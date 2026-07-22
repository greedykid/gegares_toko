<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderRefundedNotification;
use App\Services\BiteshipService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 1. Set Defaults for Dates if not provided
        if (! $request->has('from_date')) {
            $request->merge(['from_date' => now()->subMonth()->format('Y-m-d')]);
        }
        if (! $request->has('to_date')) {
            $request->merge(['to_date' => now()->format('Y-m-d')]);
        }

        $query = $this->getOrderQuery($request);

        // 3. Statistics Calculation (Global Stats)
        $stats = Order::selectRaw("
            count(*) as total,
            sum(case when status in ('processing', 'shipped') then 1 else 0 end) as active,
            sum(case when status = 'completed' then 1 else 0 end) as completed,
            sum(case when status = 'completed' then total else 0 end) as revenue
        ")->first();

        $totalOrders = $stats->total ?? 0;
        $activeOrders = $stats->active ?? 0;
        $completedOrders = $stats->completed ?? 0;
        $totalRevenue = $stats->revenue ?? 0;

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'totalOrders', 'activeOrders', 'completedOrders', 'totalRevenue'));
    }

    public function exportCsv(Request $request)
    {
        $filename = 'laporan-pesanan-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $query = $this->getOrderQuery($request)->with(['user', 'items']);

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No. Pesanan', 'Tanggal', 'Pelanggan', 'Email', 'Items', 'Subtotal', 'Ongkir', 'Diskon', 'Total', 'Status', 'Pembayaran']);

            $query->chunk(100, function ($orders) use ($file) {
                foreach ($orders as $order) {
                    $items = $order->items->map(function ($item) {
                        return $item->product_name.($item->variant_name ? " [{$item->variant_name}]" : '')." (x{$item->quantity})";
                    })->implode('; ');

                    fputcsv($file, [
                        $order->order_number,
                        $order->created_at->format('d-m-Y H:i'),
                        $order->user->name ?? '-',
                        $order->user->email ?? '-',
                        $items,
                        (float) $order->subtotal,
                        (float) $order->shipping_cost,
                        (float) ($order->discount_amount ?? 0),
                        (float) $order->total,
                        $order->status_label,
                        $order->payment_status,
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function report(Request $request)
    {
        $orders = $this->getOrderQuery($request)
            ->with(['user', 'items.product', 'address'])
            ->get();

        $totalRevenue = $orders->where('status', 'completed')->sum('total');

        return view('admin.orders.report', compact('orders', 'totalRevenue'));
    }

    private function getOrderQuery(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns
        $allowedSorts = ['order_number', 'total', 'status', 'payment_status', 'created_at'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Allowed direction
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        // The list view embeds each order as JSON for the detail modal, and that
        // modal lists the purchased items — so items must be eager loaded here.
        // It is one extra query for the whole page, not one per order.
        $query = Order::with(['user', 'address', 'items']);

        if ($request->filled('search')) {
            $q = $request->search;
            $userIds = User::where('name', 'LIKE', "%$q%")->pluck('id');
            $query->where(function ($query) use ($q, $userIds) {
                $query->where('order_number', 'LIKE', "%$q%")
                    ->orWhereIn('user_id', $userIds);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Use a range on the raw timestamp instead of whereDate() so the
        // created_at index can be used (whereDate wraps the column in DATE()).
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date.' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date.' 23:59:59');
        }

        if ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        return $query;
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user', 'address']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order, BiteshipService $biteship, OrderService $orders)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'tracking_number' => 'nullable|string',
        ]);

        $target = $data['status'];

        // The state machine lives on the model so the Biteship webhook obeys the
        // same rules this dropdown does.
        if (! $order->canTransitionTo($target)) {
            return back()->with(
                'error',
                "Status tidak dapat diubah dari \"{$order->status_label}\" ke status tersebut."
            );
        }

        // Cancelling from the dropdown must do everything the Batalkan Pesanan
        // button does — otherwise the courier still collects a cancelled order
        // and its stock is never returned.
        if ($target === 'cancelled') {
            [$ok, $message] = $this->applyCancellation($order, $biteship, $orders, 'Dibatalkan oleh admin');

            if (! $ok) {
                return back()->with('error', $message);
            }

            if ($request->filled('tracking_number')) {
                $order->update(['tracking_number' => $data['tracking_number']]);
            }

            return back()->with('success', $message);
        }

        $order->update($data);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function processShipping(Order $order, BiteshipService $biteship)
    {
        if ($order->status !== 'processing') {
            return back()->with('error', 'Hanya pesanan yang sudah dibayar dan sedang diproses yang dapat dibooking ke Biteship.');
        }

        $result = $biteship->createOrder($order);

        if (isset($result['success']) && $result['success']) {
            $order->update([
                'status' => 'processing',
                'tracking_number' => $result['courier']['waybill_id'] ?? $order->tracking_number,
            ]);

            return back()->with('success', 'Berhasil! Pesanan telah diteruskan ke Biteship. Status kini: Diproses.');
        }

        $errorMessage = $result['error'] ?? 'Gagal memproses pesanan ke Biteship. Silakan coba lagi.';

        return back()->with('error', $errorMessage);
    }

    public function cancelShipping(Request $request, Order $order, BiteshipService $biteship, OrderService $orders)
    {
        $reason = trim((string) $request->input('cancellation_reason')) ?: 'Dibatalkan oleh admin';

        [$ok, $message] = $this->applyCancellation($order, $biteship, $orders, $reason);

        return back()->with($ok ? 'success' : 'error', $message);
    }

    /**
     * The one way an order gets cancelled from the admin panel, whichever button
     * was pressed: cancel the shipment at Biteship first, then let
     * OrderService::cancelAndRelease return the stock and the coupon slot and
     * notify the customer if a refund is owed.
     *
     * @return array{0: bool, 1: string} success flag and the message to show
     */
    private function applyCancellation(Order $order, BiteshipService $biteship, OrderService $orders, string $reason): array
    {
        if ($order->status === 'cancelled') {
            return [false, 'Pesanan ini sudah dibatalkan.'];
        }

        if ($order->status === 'completed') {
            return [false, 'Pesanan yang sudah selesai tidak dapat dibatalkan.'];
        }

        // If the shipment was booked with Biteship, cancel it there first and
        // only flip the local status when the courier side confirms.
        if (! empty($order->biteship_order_id)) {
            $result = $biteship->cancelOrder($order, $reason);

            if (! ($result['success'] ?? false)) {
                return [false, $result['error'] ?? 'Gagal membatalkan pengiriman di Biteship. Silakan coba lagi.'];
            }
        }

        // Captured before the flip: stock is reserved from the moment the order
        // is created, so it comes back for anything that has not yet been handed
        // to the courier.
        $shouldRestock = $order->status !== 'shipped';
        $owesRefund = $order->payment_status === 'paid';

        if (! $orders->cancelAndRelease($order)) {
            return [false, 'Pesanan ini sudah dibatalkan.'];
        }

        $message = 'Pesanan berhasil dibatalkan';
        $message .= $shouldRestock ? ' dan stok dikembalikan' : '';
        $message .= $owesRefund ? '. Pesanan ini sudah dibayar — tandai sudah direfund setelah dana dikembalikan.' : '.';

        return [true, $message];
    }

    /** Record that the customer has had their money back. */
    public function markRefunded(Order $order)
    {
        if (! $order->needsRefund()) {
            return back()->with('error', 'Pesanan ini tidak menunggu pengembalian dana.');
        }

        $order->update(['refunded_at' => now()]);

        if ($order->user) {
            try {
                $order->user->notify(new OrderRefundedNotification($order->fresh()));
            } catch (\Throwable $e) {
                Log::error('OrderRefunded notification failed: '.$e->getMessage());
            }
        }

        return back()->with('success', 'Pesanan ditandai sudah direfund.');
    }

    public function getTracking(Order $order, BiteshipService $biteship, OrderService $orders)
    {
        // Try real API first if tracking number exists
        if ($order->tracking_number) {
            $tracking = $biteship->trackShipment($order->tracking_number, strtolower($order->shipping_courier));

            Log::info("Biteship Tracking for #{$order->order_number}: ".json_encode($tracking));

            if ($tracking && isset($tracking['success']) && $tracking['success']) {
                $status = $tracking['status'] ?? 'allocated';

                // Opening this modal syncs the order with what the courier says,
                // through the same door the webhook and biteship:sync use. It
                // used to write 'shipped'/'completed' itself, with no transition
                // rules — so viewing a cancelled order's tracking revived it.
                $orders->applyCourierStatus($order, $tracking['status'] ?? null, 'Biteship Tracking');

                $history = collect($tracking['history'] ?? [])->map(function ($h) {
                    return [
                        'status' => $h['status'],
                        'note' => $this->translateBiteshipNote($h['note'] ?? 'Status diperbarui'),
                        'time' => Carbon::parse($h['updated_at'] ?? $h['time'] ?? now())->translatedFormat('d M, H:i'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'status' => $status,
                    'status_label' => $this->mapBiteshipStatusToLabel($status),
                    'courier' => [
                        'name' => $tracking['courier']['name'] ?? 'Kurir Biteship',
                        'phone' => $tracking['courier']['phone'] ?? '-',
                        'plate_number' => $tracking['courier']['plate_number'] ?? 'GGR-TRK',
                        // Null, not a stock portrait: a random face from an avatar
                        // service would be presented as this courier's photo.
                        'photo' => $tracking['courier']['photo'] ?? null,
                        'type' => $tracking['courier']['type'] ?? ($order->shipping_courier.' '.$order->shipping_service),
                    ],
                    'link' => $order->tracking_url,
                    'history' => array_reverse($history),
                ]);
            }
        }

        // Nothing is invented when Biteship has no data. The admin needs to see
        // that a booking has not produced a waybill yet — a simulated courier
        // here hid exactly the failures this panel exists to catch.
        return response()->json([
            'success' => false,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'message' => $order->biteship_order_id
                ? 'Pesanan sudah dibooking ke Biteship, tetapi kurir belum mengirimkan data pelacakan.'
                : 'Pesanan belum dibooking ke Biteship, jadi belum ada data pelacakan.',
        ]);
    }

    private function mapBiteshipStatusToLabel($status)
    {
        return match (strtolower($status)) {
            'placed' => 'Pesanan Dibuat',
            'confirmed' => 'Pesanan Dikonfirmasi',
            'allocated' => 'Kurir Dialokasikan',
            'picking_up' => 'Penjemputan',
            'picked' => 'Pesanan Diambil',
            'dropping_off' => 'Sedang Diantar',
            'delivered' => 'Tiba di Tujuan',
            'completed' => 'Selesai',
            'courier_not_found' => 'Kurir Tidak Ditemukan',
            'on_hold' => 'Ditangguhkan',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function translateBiteshipNote($note)
    {
        if (! $note) {
            return '';
        }

        $mappings = [
            // Confirmed / Allocated
            'Order has been confirmed. Locating nearest driver to pick up.' => 'Pesanan kurir telah dikonfirmasi. Mencari kurir terdekat untuk penjemputan.',
            'Courier has been allocated. Waiting to pick up.' => 'Kurir telah ditemukan dan siap menjemput pesanan.',
            'Courier order is confirmed' => 'Pesanan kurir telah dikonfirmasi',
            'Courier is allocated and ready to pick up' => 'Kurir telah ditemukan dan siap menjemput',
            'has been notified to pick up' => 'telah diberitahu untuk menjemput',
            'is notified to pick up' => 'telah diberitahu untuk menjemput',
            'Pickup number' => 'Nomor penjemputan',
            'is trying to find courier' => 'sedang mencoba mencarikan kurir',
            'Failed to find courier' => 'Gagal menemukan kurir untuk penjemputan',
            'no courier available' => 'tidak ada kurir tersedia',

            // Picking up
            'Courier is on the way to pick up item.' => 'Kurir sedang dalam perjalanan menuju lokasi penjemputan.',
            'Courier is on the way to pick up location' => 'Kurir sedang menuju lokasi penjemputan',

            // Picked up
            'Item has been picked and ready to be shipped.' => 'Pesanan telah dijemput dan siap untuk dikirim.',
            'Item has been picked by courier' => 'Pesanan telah diambil oleh kurir',

            // In Transit / Dropping off
            'Item is on the way to customer.' => 'Paket sedang dalam perjalanan menuju alamat tujuan.',
            'Courier is dropping off item to destination' => 'Kurir sedang mengantar pesanan ke tujuan',

            // Hold / Issue
            'Your shipment is on hold at the moment.' => 'Pengiriman Anda sedang ditangguhkan sementara.',
            'Order is on hold for a moment due to shipment issue' => 'Pesanan ditangguhkan sementara karena kendala pengiriman',

            // Delivered
            'Item has been delivered.' => 'Pesanan telah sampai di tujuan.',
            'Order has been delivered' => 'Pesanan telah sampai di tujuan',
            'Order is delivered' => 'Pesanan telah sampai di tujuan',
            'Order is completed' => 'Pesanan selesai',

            // Rejected / Cancelled / Returned
            'Your shipment has been rejected.' => 'Pengiriman Anda telah ditolak oleh kurir.',
            'Your shipment is canceled because there\'s no courier available.' => 'Pengiriman dibatalkan karena tidak ada kurir yang tersedia saat ini.',
            'rejected because no courier picking up order' => 'ditolak karena tidak ada kurir yang menjemput pesanan',
            'Order is on the way back to the origin.' => 'Pesanan sedang dalam proses pengembalian ke penjual.',
            'Order successfully returned.' => 'Pesanan telah berhasil dikembalikan ke penjual.',
            'Order successfully disposed.' => 'Pesanan telah berhasil dihancurkan/dibuang.',
        ];

        $translated = $note;
        foreach ($mappings as $en => $id) {
            $translated = str_ireplace($en, $id, $translated);
        }

        return $translated;
    }
}
