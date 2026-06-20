<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

use App\Services\PakasirService;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->orders()->with('items')->latest();

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'pending') {
                $query->whereIn('status', ['pending', 'awaiting_payment']);
            } elseif ($status === 'processing') {
                $query->whereIn('status', ['paid', 'processing', 'shipped']);
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        // Calculate stats
        $stats = [
            'total' => $user->orders()->count(),
            'pending' => $user->orders()->whereIn('status', ['pending', 'awaiting_payment'])->count(),
            'processing' => $user->orders()->whereIn('status', ['paid', 'processing', 'shipped'])->count(),
            'completed' => $user->orders()->where('status', 'completed')->count(),
            'monthly_spent' => $user->orders()
                ->whereNotIn('status', ['pending', 'awaiting_payment', 'cancelled'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        abort_if($order->user_id !== \Illuminate\Support\Facades\Auth::id(), 403);

        $order->load(['items.product', 'address']);

        return view('orders.show', compact('order'));
    }

    public function payment(Order $order, PakasirService $pakasirService)
    {
        abort_if($order->user_id !== \Illuminate\Support\Facades\Auth::id(), 403);

        if (in_array($order->payment_status, ['failed', 'expired'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Pesanan ini sudah tidak bisa dibayar.');
        }

        // Sync status with Pakasir if not already paid
        if ($order->payment_status !== 'paid') {
            // Force a fresh check by clearing any existing throttle cache
            \Illuminate\Support\Facades\Cache::forget('pakasir_sync_limit_' . $order->id);

            $order = $pakasirService->syncOrderWithPakasir($order);

            // Set the cache throttle so polling doesn't immediately check again
            \Illuminate\Support\Facades\Cache::put('pakasir_sync_limit_' . $order->id, true, 15);
        }

        return response()
            ->view('orders.payment', compact('order'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function checkStatus(Order $order, PakasirService $pakasirService)
    {
        abort_if($order->user_id !== \Illuminate\Support\Facades\Auth::id(), 403);

        if ($order->payment_status !== 'paid') {
            $cacheKey = 'pakasir_sync_limit_' . $order->id;
            
            // Only query Pakasir if the cache lock has expired (at most once per 15s)
            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $order = $pakasirService->syncOrderWithPakasir($order);
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, 15);
            }
        }

        return response()->json([
            'payment_status' => $order->payment_status,
            'status' => $order->status,
        ]);
    }

    public function getTracking(Order $order, \App\Services\BiteshipService $biteshipService)
    {
        abort_if($order->user_id !== \Illuminate\Support\Facades\Auth::id(), 403);

        // Try real API first if tracking number exists
        if ($order->tracking_number) {
            $tracking = $biteshipService->trackShipment($order->tracking_number, strtolower($order->shipping_courier));
            
            if ($tracking && isset($tracking['success']) && $tracking['success']) {
                $status = $tracking['status'] ?? 'allocated';
                
                $history = collect($tracking['history'] ?? [])->map(function($h) {
                    return [
                        'status' => $h['status'],
                        'note' => $this->translateBiteshipNote($h['note'] ?? 'Status diperbarui'),
                        'time' => \Illuminate\Support\Carbon::parse($h['updated_at'] ?? $h['time'] ?? now())->translatedFormat('d M, H:i')
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
                        'photo' => $tracking['courier']['photo'] ?? 'https://i.pravatar.cc/150?u=biteship',
                        'type' => $tracking['courier']['type'] ?? ($order->shipping_courier . ' ' . $order->shipping_service)
                    ],
                    'link' => $order->tracking_url,
                    'history' => array_reverse($history)
                ]);
            }
        }

        // Fallback Simulation for testing (matches Admin Dashboard level of detail)
        if (in_array($order->status, ['processing', 'shipped', 'completed'])) {
            $status = 'allocated';
            $label = 'Kurir Menuju Lokasi';
            $history = [
                ['status' => 'confirmed', 'note' => 'Pesanan dikonfirmasi', 'time' => $order->updated_at->subMinutes(30)->translatedFormat('d M, H:i')],
            ];

            if ($order->status === 'shipped') {
                $status = 'picked_up';
                $label = 'Pesanan Berhasil Dijemput';
                $history[] = ['status' => 'allocated', 'note' => 'Kurir telah ditemukan', 'time' => $order->updated_at->subMinutes(20)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'picking_up', 'note' => 'Kurir sedang menuju lokasi Anda', 'time' => $order->updated_at->subMinutes(15)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'picked_up', 'note' => 'Pesanan berhasil dijemput oleh kurir', 'time' => $order->updated_at->subMinutes(10)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'on_the_way', 'note' => 'Paket sedang dikirim ke tujuan', 'time' => $order->updated_at->translatedFormat('d M, H:i')];
            } elseif ($order->status === 'completed') {
                $status = 'delivered';
                $label = 'Pesanan Diterima';
                $history[] = ['status' => 'allocated', 'note' => 'Kurir telah ditemukan', 'time' => $order->updated_at->subHours(1)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'picked_up', 'note' => 'Pesanan telah dijemput oleh kurir', 'time' => $order->updated_at->subMinutes(45)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'delivered', 'note' => 'Selesai: Pesanan telah diterima oleh pelanggan', 'time' => $order->updated_at->translatedFormat('d M, H:i')];
            } else {
                // processing
                $history[] = ['status' => 'allocated', 'note' => 'Mencari kurir terdekat...', 'time' => \Illuminate\Support\Carbon::now()->subMinutes(5)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'allocated', 'note' => 'Kurir telah ditemukan dan sedang menuju lokasi Anda', 'time' => \Illuminate\Support\Carbon::now()->subMinutes(2)->translatedFormat('d M, H:i')];
            }

            $history = array_reverse($history);

            return response()->json([
                'success' => true,
                'status' => $status,
                'status_label' => $label,
                'courier' => [
                    'name' => 'Budi Santoso',
                    'phone' => '081234567890',
                    'plate_number' => 'B 3546 UIL',
                    'photo' => 'https://i.pravatar.cc/150?u=budi',
                    'type' => strtoupper($order->shipping_courier . ' ' . $order->shipping_service)
                ],
                'link' => $order->tracking_url,
                'history' => $history
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Belum ada informasi pelacakan.']);
    }

    public function complete(Order $order)
    {
        abort_if($order->user_id !== \Illuminate\Support\Facades\Auth::id(), 403);

        if ($order->status !== 'shipped') {
            return redirect()->back()->with('error', 'Hanya pesanan dengan status dikirim yang dapat diselesaikan.');
        }

        $order->update([
            'status' => 'completed'
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan telah selesai. Terima kasih telah berbelanja!');
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
        if (!$note) return '';

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
