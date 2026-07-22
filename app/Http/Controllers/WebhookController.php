<?php

namespace App\Http\Controllers;

use App\Jobs\BookBiteshipOrder;
use App\Models\Order;
use App\Services\BiteshipService;
use App\Services\OrderService;
use App\Services\PakasirService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(protected OrderService $orders) {}

    // Midtrans integration removed. Use Pakasir webhook instead.

    public function pakasir(Request $request, PakasirService $pakasirService)
    {
        // Log only the non-sensitive fields we actually act on, never the raw
        // payload — it is a payment notification and may carry customer details.
        Log::info('Pakasir webhook received', Arr::only(
            $request->all(),
            ['order_id', 'status', 'amount', 'payment_method', 'completed_at']
        ));

        $order = $pakasirService->handleNotification($request->all());

        if ($order) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 404);
    }

    public function biteship(Request $request)
    {
        // SECURITY: authenticate the caller with a shared secret when configured.
        // Without this, anyone who knows the URL could forge order status updates.
        $expectedToken = config('biteship.webhook_token');
        if ($expectedToken) {
            $providedToken = $request->header('X-Webhook-Token') ?? $request->query('token');
            if (! is_string($providedToken) || ! hash_equals($expectedToken, $providedToken)) {
                Log::warning('Biteship webhook rejected: invalid or missing webhook token.');

                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
        }

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
        if (! $biteshipOrderId && ! $rawTrackingId && ! $status && ! $referenceId) {
            return response()->json([
                'success' => true,
                'message' => 'Biteship Webhook reached successfully',
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
        if (! $order && $referenceId) {
            $order = Order::where('order_number', $referenceId)->first();
            if ($order) {
                Log::debug("Biteship Webhook Match: Found by Reference ID [{$referenceId}]");
            }
        }

        // Tertiary Lookup: By Tracking/Waybill Number
        if (! $order && $rawTrackingId) {
            $trackingId = trim($rawTrackingId);
            $order = Order::where('tracking_number', $trackingId)->first();

            if (! $order) {
                // Fallback: Case-insensitive and trimmed search
                $order = Order::whereRaw('TRIM(tracking_number) = ?', [$trackingId])->first();
            }

            if ($order) {
                Log::debug("Biteship Webhook Match: Found by Tracking Number [{$trackingId}]");
            }
        }

        if (! $order) {
            Log::warning("Biteship Webhook: Order not found for order_id [{$biteshipOrderId}], reference_id [{$referenceId}], tracking_id [{$rawTrackingId}]");

            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Update tracking IDs if they arrived in the webhook and are different
        $idUpdates = [];
        if ($rawTrackingId && $order->tracking_number !== $rawTrackingId) {
            $idUpdates['tracking_number'] = $rawTrackingId;
        }

        $payloadTrackingId = $request->input('courier_tracking_id')
            ?? $request->input('data.courier_tracking_id')
            ?? $request->input('courier.tracking_id')
            ?? $request->input('data.courier.tracking_id');
        if ($payloadTrackingId && $order->courier_tracking_id !== $payloadTrackingId) {
            $idUpdates['courier_tracking_id'] = $payloadTrackingId;
        }

        if (! empty($idUpdates)) {
            $order->update($idUpdates);
            Log::debug("Biteship Webhook: Updated tracking identifiers for Order #{$order->order_number}");
        }

        // Handle Automated Courier Re-allocation
        if ($status === 'rejected' || $status === 'courier_not_found') {
            $retryKey = 'biteship_reallocation_retries_'.$order->id;
            $retries = Cache::get($retryKey, 0);

            if ($retries < 2) {
                $newRetries = $retries + 1;
                Cache::put($retryKey, $newRetries, now()->addDay());
                Log::warning("Biteship Webhook: Courier status for Order #{$order->order_number} is '{$status}'. Re-allocation attempt #{$newRetries} initiated.");

                // Clear the failed booking and keep the order in "processing", then
                // re-dispatch the courier booking job to request a fresh driver.
                $order->update([
                    'biteship_order_id' => null,
                    'courier_tracking_id' => null,
                    'tracking_number' => null,
                    'status' => 'processing',
                ]);

                // Test guard: don't hit the real Biteship API unless a mock is bound.
                if (! (app()->runningUnitTests() && ! app()->bound(BiteshipService::class))) {
                    BookBiteshipOrder::dispatch($order->id);
                }
            } else {
                Log::warning("Biteship Webhook: Courier status for Order #{$order->order_number} is '{$status}'. Exceeded max reallocation attempts (2). Leaving status as processing for manual administrator action.");

                // Keep the order in processing but do NOT clear biteship_order_id so
                // it does not trigger re-allocation again.
                if ($order->status !== 'processing') {
                    $order->update(['status' => 'processing']);
                }
            }

            return response()->json(['success' => true]);
        }

        // Map Biteship status to local Gegares status
        $newStatus = match ($status) {
            'allocated', 'picking_up', 'pickingUp' => 'processing',
            'picked_up', 'picked', 'dropping_off', 'droppingOff', 'out_for_delivery', 'on_the_way', 'in_transit', 'dispatched', 'return_in_transit', 'returnInTransit' => 'shipped',
            'delivered' => 'completed',
            'cancelled', 'canceled', 'returned' => 'cancelled',
            default => $order->status
        };

        if ($newStatus === $order->status) {
            return response()->json(['success' => true]);
        }

        // The courier's view of an order is not allowed to rewrite history: a
        // delayed "in transit" notification arriving after delivery must not
        // drag a completed order back to "shipped". Same state machine the admin
        // panel obeys.
        if (! $order->canTransitionTo($newStatus)) {
            Log::warning("Biteship Webhook: refused status '{$status}' for Order #{$order->order_number} — cannot move from '{$order->status}' to '{$newStatus}'.");

            return response()->json(['success' => true]);
        }

        if ($newStatus === 'cancelled') {
            // Never a bare status write: a cancellation has to return the stock
            // and the coupon slot and tell a paying customer a refund is coming,
            // exactly as the admin's cancel button does. A parcel Biteship
            // reports as 'returned' is physically back on the shelf, so that one
            // restocks even though the order had already shipped.
            $restock = in_array($status, ['returned', 'return_in_transit', 'returnInTransit'], true) ? true : null;

            $this->orders->cancelAndRelease($order, [
                'admin_note' => ($order->admin_note ? $order->admin_note.' | ' : '')
                    ."Dibatalkan otomatis dari Biteship (status: {$status}).",
            ], $restock);

            Log::info("Order #{$order->order_number} cancelled via Biteship Webhook (status: {$status}).");

            return response()->json(['success' => true]);
        }

        $order->update(['status' => $newStatus]);
        Log::info("Order #{$order->order_number} status updated to {$newStatus} via Biteship Webhook.");

        return response()->json(['success' => true]);
    }
}
