<?php

namespace App\Http\Controllers;

use App\Services\PakasirService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    // Midtrans integration removed. Use Pakasir webhook instead.

    public function pakasir(Request $request, PakasirService $pakasirService)
    {
        Log::info('Pakasir webhook received', $request->all());

        $order = $pakasirService->handleNotification($request->all());

        if ($order) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 404);
    }

    public function biteship(Request $request)
    {
        Log::info('Biteship webhook received:', $request->all());

        // Biteship webhook payload could contain fields directly, or nested inside a 'data' block.
        // Let's support both for maximum resilience.
        $biteshipOrderId = $request->input('order_id') ?? $request->input('data.order_id');
        $referenceId = $request->input('reference_id') ?? $request->input('data.reference_id');
        
        $rawTrackingId = $request->input('courier_waybill_id') 
            ?? $request->input('courier_tracking_id')
            ?? $request->input('data.courier.waybill_id')
            ?? $request->input('data.courier_waybill_id')
            ?? $request->input('data.courier_tracking_id');
            
        $status = $request->input('status') ?? $request->input('data.status');

        // Biteship verification or empty request
        if (!$biteshipOrderId && !$rawTrackingId && !$status && !$referenceId) {
            return response()->json([
                'success' => true,
                'message' => 'Biteship Webhook reached successfully'
            ]);
        }

        // Primary Lookup: By Biteship Order ID
        $order = null;
        if ($biteshipOrderId) {
            $order = Order::where('biteship_order_id', $biteshipOrderId)->first();
            if ($order) {
                Log::debug("Biteship Webhook Match: Found by Biteship Order ID [{$biteshipOrderId}]");
            }
        }

        // Secondary Lookup: By Reference ID (our local order_number)
        if (!$order && $referenceId) {
            $order = Order::where('order_number', $referenceId)->first();
            if ($order) {
                Log::debug("Biteship Webhook Match: Found by Reference ID [{$referenceId}]");
            }
        }

        // Tertiary Lookup: By Tracking/Waybill Number
        if (!$order && $rawTrackingId) {
            $trackingId = trim($rawTrackingId);
            $order = Order::where('tracking_number', $trackingId)->first();
            
            if (!$order) {
                // Fallback: Case-insensitive and trimmed search
                $order = Order::whereRaw('TRIM(tracking_number) = ?', [$trackingId])->first();
            }
            
            if ($order) {
                Log::debug("Biteship Webhook Match: Found by Tracking Number [{$trackingId}]");
            }
        }

        if (!$order) {
            Log::warning("Biteship Webhook: Order not found for order_id [{$biteshipOrderId}], reference_id [{$referenceId}], tracking_id [{$rawTrackingId}]");
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Update tracking IDs if they arrived in the webhook and are different
        $idUpdates = [];
        if ($rawTrackingId && $order->tracking_number !== $rawTrackingId) {
            $idUpdates['tracking_number'] = $rawTrackingId;
        }
        
        $payloadTrackingId = $request->input('courier_tracking_id') ?? $request->input('data.courier_tracking_id');
        if ($payloadTrackingId && $order->courier_tracking_id !== $payloadTrackingId) {
            $idUpdates['courier_tracking_id'] = $payloadTrackingId;
        }

        if (!empty($idUpdates)) {
            $order->update($idUpdates);
            Log::debug("Biteship Webhook: Updated tracking identifiers for Order #{$order->order_number}");
        }

        // Handle Automated Courier Re-allocation
        if ($status === 'rejected' || $status === 'courier_not_found') {
            $retryKey = 'biteship_reallocation_retries_' . $order->id;
            $retries = \Illuminate\Support\Facades\Cache::get($retryKey, 0);

            if ($retries < 2) {
                $newRetries = $retries + 1;
                \Illuminate\Support\Facades\Cache::put($retryKey, $newRetries, now()->addDay());
                Log::warning("Biteship Webhook: Courier status for Order #{$order->order_number} is '{$status}'. Re-allocation attempt #{$newRetries} initiated.");

                // Reset tracking details and set status back to paid (which triggers the booted Eloquent listener to request a new driver)
                $order->update([
                    'biteship_order_id' => null,
                    'courier_tracking_id' => null,
                    'tracking_number' => null,
                    'status' => 'paid',
                ]);
            } else {
                Log::warning("Biteship Webhook: Courier status for Order #{$order->order_number} is '{$status}'. Exceeded max reallocation attempts (2). Leaving status as paid for manual administrator action.");
                
                // Set status to paid but do NOT clear biteship_order_id so it doesn't trigger re-allocation again
                if ($order->status !== 'paid') {
                    $order->update(['status' => 'paid']);
                }
            }

            return response()->json(['success' => true]);
        }

        // Map Biteship status to local Gegares status
        $newStatus = match($status) {
            'allocated', 'picking_up', 'pickingUp' => 'processing',
            'picked_up', 'picked', 'dropping_off', 'droppingOff', 'out_for_delivery', 'on_the_way', 'in_transit', 'dispatched', 'return_in_transit', 'returnInTransit' => 'shipped',
            'delivered' => 'completed',
            'cancelled', 'canceled', 'returned' => 'cancelled',
            default => $order->status
        };

        if ($newStatus && $order->status !== $newStatus) {
            $order->update(['status' => $newStatus]);
            Log::info("Order #{$order->order_number} status updated to {$newStatus} via Biteship Webhook.");
        }

        return response()->json(['success' => true]);
    }
}
