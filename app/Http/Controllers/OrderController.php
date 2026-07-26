<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BiteshipService;
use App\Services\PakasirService;
use App\Support\DemoCourier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->orders()->with('items')->latest();

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'processing') {
                $query->whereIn('status', ['processing', 'shipped']);
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        // "Muat Lebih Banyak": hand back just the next batch of cards so the
        // browser appends them instead of reloading the page (and its stats).
        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('orders.partials.order-cards', compact('orders'))->render(),
                'next_page_url' => $orders->nextPageUrl(),
            ]);
        }

        // Calculate stats
        $counts = $user->orders()
            ->select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total' => $counts->sum(),
            'pending' => $counts->get('pending', 0),
            'processing' => ($counts->get('processing', 0) + $counts->get('shipped', 0)),
            'completed' => $counts->get('completed', 0),
            // Range bounds (not whereMonth/whereYear) so the created_at index can
            // be used — wrapping the column in a function would disable it.
            'monthly_spent' => $user->orders()
                ->whereNotIn('status', ['pending', 'cancelled'])
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total'),
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        $order->load(['items.product', 'address']);

        return view('orders.show', compact('order'));
    }

    public function payment(Order $order, PakasirService $pakasirService)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        // The status matters as much as the payment status here. Auto-cancel
        // marks the payment 'expired', but an order cancelled by an admin keeps
        // payment_status 'unpaid' — so without the first check its QRIS page
        // stayed open and the customer could pay an order that no longer exists,
        // handing the shop a refund it never needed to make.
        if ($order->status === 'cancelled') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Pesanan ini sudah dibatalkan, jadi tidak bisa dibayar.');
        }

        if (in_array($order->payment_status, ['failed', 'expired'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Pesanan ini sudah tidak bisa dibayar.');
        }

        // Sync status with Pakasir if not already paid. Honour the same 2s throttle
        // the polling path uses (checkStatus) so refreshing this page repeatedly
        // cannot hammer Pakasir with back-to-back 8s API calls while holding the
        // session lock.
        if ($order->payment_status !== 'paid') {
            $cacheKey = 'pakasir_sync_limit_'.$order->id;

            if (! Cache::has($cacheKey)) {
                $order = $pakasirService->syncOrderWithPakasir($order);
                Cache::put($cacheKey, true, 2);
            }
        }

        return response()
            ->view('orders.payment', compact('order'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function checkStatus(Order $order, PakasirService $pakasirService)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        if ($order->payment_status !== 'paid') {
            $cacheKey = 'pakasir_sync_limit_'.$order->id;

            // Only query Pakasir if the cache lock has expired (at most once per 2s)
            if (! Cache::has($cacheKey)) {
                $order = $pakasirService->syncOrderWithPakasir($order);
                Cache::put($cacheKey, true, 2);
            }
        }

        return response()->json([
            'payment_status' => $order->payment_status,
            'status' => $order->status,
        ]);
    }

    public function getTracking(Order $order, BiteshipService $biteshipService)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        // Try real API first if tracking number exists
        if ($order->tracking_number) {
            $tracking = $biteshipService->trackShipment($order->tracking_number, strtolower($order->shipping_courier));

            if ($tracking && isset($tracking['success']) && $tracking['success']) {
                $status = $tracking['status'] ?? 'allocated';

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

        // No invented courier here. This used to answer with a hardcoded driver
        // ("Budi Santoso", plate B 3546 UIL) whenever Biteship had nothing yet,
        // which showed the customer a delivery that was not happening. What the
        // order itself knows is real; anything else waits for the courier.
        //
        // The one exception is an explicitly enabled demo deployment, where the
        // panel needs something to show — off unless DEMO_COURIER says otherwise.
        if ($demo = DemoCourier::payload($order)) {
            return response()->json($demo);
        }

        return response()->json([
            'success' => false,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'message' => $this->trackingPendingMessage($order),
        ]);
    }

    public function complete(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        if ($order->status !== 'shipped') {
            return redirect()->back()->with('error', 'Hanya pesanan dengan status dikirim yang dapat diselesaikan.');
        }

        $order->update([
            'status' => 'completed',
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan telah selesai. Terima kasih telah berbelanja!');
    }

    /** Why there is nothing to show yet, in the customer's own terms. */
    private function trackingPendingMessage(Order $order): string
    {
        return match ($order->status) {
            'pending' => 'Pesanan belum dibayar, jadi belum ada pengiriman yang dijadwalkan.',
            'processing' => 'Pesanan sedang disiapkan. Nomor resi akan muncul di sini setelah kurir menjemput paket.',
            'cancelled' => 'Pesanan dibatalkan, tidak ada pengiriman untuk dilacak.',
            default => 'Belum ada informasi pelacakan dari kurir.',
        };
    }

    private function mapBiteshipStatusToLabel($status)
    {
        return match (strtolower(str_replace('_', '', $status))) {
            'placed' => 'Pesanan Dibuat',
            'confirmed' => 'Pesanan Dikonfirmasi',
            'allocated' => 'Kurir Dialokasikan',
            'pickingup' => 'Kurir Menuju Penjemputan',
            'picked' => 'Pesanan Telah Dijemput',
            'droppingoff' => 'Kurir Sedang Mengantar',
            'returnintransit' => 'Pesanan Sedang Dikembalikan',
            'onhold' => 'Pengiriman Ditangguhkan',
            'delivered' => 'Tiba di Tujuan',
            'rejected' => 'Pengiriman Ditolak',
            'couriernotfound' => 'Kurir Tidak Ditemukan',
            'returned' => 'Pesanan Telah Dikembalikan',
            'cancelled' => 'Dibatalkan',
            'disposed' => 'Pesanan Dibuang/Dihancurkan',
            default => ucfirst(str_replace(['_', 'Up', 'Off', 'In'], [' ', ' Up', ' Off', ' In'], $status)),
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
