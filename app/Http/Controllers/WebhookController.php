<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function midtrans(Request $request, MidtransService $midtransService)
    {
        Log::info('Midtrans webhook received', $request->all());

        $order = $midtransService->handleNotification($request->all());

        if ($order) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 404);
    }

    public function biteship(Request $request)
    {
        Log::info('Biteship webhook received:', $request->all());

        // Biteship unique internal order ID is the most stable lookup key
        $biteshipOrderId = $request->input('order_id');
        
        // Secondary fallback: courier_waybill_id (AWB) or tracking_id
        $rawTrackingId = $request->input('courier_waybill_id') ?? $request->input('courier_tracking_id');
        $status = $request->input('status');

        // Biteship verification or empty request
        if (!$biteshipOrderId && !$rawTrackingId && !$status) {
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

        // Secondary Lookup: By Tracking/Waybill Number
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

        // Update tracking IDs if they arrived in the webhook and are different
        $idUpdates = [];
        if ($rawTrackingId && $order->tracking_number !== $rawTrackingId) {
            $idUpdates['tracking_number'] = $rawTrackingId;
        }
        
        $payloadTrackingId = $request->input('courier_tracking_id');
        if ($payloadTrackingId && $order->courier_tracking_id !== $payloadTrackingId) {
            $idUpdates['courier_tracking_id'] = $payloadTrackingId;
        }

        if (!empty($idUpdates)) {
            $order->update($idUpdates);
            Log::debug("Biteship Webhook: Updated tracking identifiers for Order #{$order->order_number}");
        }

        // Map Biteship status to local Gegares status
        $newStatus = match($status) {
            'picking_up', 'pickingUp' => 'processing',
            'picked_up', 'picked', 'dropping_off', 'droppingOff', 'out_for_delivery', 'on_the_way', 'in_transit', 'dispatched', 'return_in_transit', 'returnInTransit' => 'shipped',
            'delivered' => 'completed',
            'cancelled', 'canceled', 'rejected', 'returned' => 'cancelled',
            default => $order->status
        };

        if ($newStatus && $order->status !== $newStatus) {
            $order->update(['status' => $newStatus]);
            Log::info("Order #{$order->order_number} status updated to {$newStatus} via Biteship Webhook.");
        }

        return response()->json(['success' => true]);
    }
}
