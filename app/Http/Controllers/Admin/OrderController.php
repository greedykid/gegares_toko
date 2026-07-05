<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 1. Set Defaults for Dates if not provided
        if (!$request->has('from_date')) {
            $request->merge(['from_date' => now()->subMonth()->format('Y-m-d')]);
        }
        if (!$request->has('to_date')) {
            $request->merge(['to_date' => now()->format('Y-m-d')]);
        }

        $query = $this->getOrderQuery($request);

        // 3. Statistics Calculation (Global Stats)
        $totalOrders = Order::count();
        $activeOrders = Order::whereIn('status', ['paid', 'processing', 'shipped'])->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'totalOrders', 'activeOrders', 'completedOrders', 'totalRevenue'));
    }

    public function exportCsv(Request $request)
    {
        $orders = $this->getOrderQuery($request)->with('items')->get();
        
        $filename = "laporan-pesanan-" . now()->format('Y-m-d-His') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No. Pesanan', 'Tanggal', 'Pelanggan', 'Email', 'Items', 'Subtotal', 'Ongkir', 'Diskon', 'Total', 'Status', 'Pembayaran']);

            foreach ($orders as $order) {
                $items = $order->items->map(function($item) {
                    return $item->product_name . ($item->variant_name ? " [{$item->variant_name}]" : "") . " (x{$item->quantity})";
                })->implode('; ');

                fputcsv($file, [
                    $order->order_number,
                    $order->created_at->format('d-m-Y H:i'),
                    $order->user->name ?? '-',
                    $order->user->email ?? '-',
                    $items,
                    (float)$order->subtotal,
                    (float)$order->shipping_cost,
                    (float)($order->discount_amount ?? 0),
                    (float)$order->total,
                    $order->status_label,
                    $order->payment_status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function report(Request $request)
    {
        $orders = $this->getOrderQuery($request)->get();
        $totalRevenue = $orders->where('status', 'completed')->sum('total');
        
        // Load relationships needed for the report
        $orders->load(['user', 'items.product', 'address']);

        return view('admin.orders.report', compact('orders', 'totalRevenue'));
    }

    private function getOrderQuery(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns
        $allowedSorts = ['order_number', 'total', 'status', 'payment_status', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Allowed direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        // Items are only needed by CSV export / printable report (added there),
        // not by the list view — so keep the base list query lean.
        $query = Order::with(['user', 'address']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function($query) use ($q) {
                $query->where('order_number', 'LIKE', "%$q%")
                      ->orWhereHas('user', function($query) use ($q) {
                          $query->where('name', 'LIKE', "%$q%");
                      });
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
            $query->where('created_at', '>=', $request->from_date . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
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

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,awaiting_payment,paid,processing,shipped,completed,cancelled',
            'tracking_number' => 'nullable|string',
        ]);

        $order->update($data);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
    public function processShipping(Order $order, BiteshipService $biteship)
    {
        if (!in_array($order->status, ['paid', 'processing'])) {
            return back()->with('error', 'Hanya pesanan dengan status "Dibayar" yang dapat diproses ke Biteship.');
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

    public function getTracking(Order $order, BiteshipService $biteship)
    {
        // Try real API first if tracking number exists
        if ($order->tracking_number) {
            $tracking = $biteship->trackShipment($order->tracking_number, strtolower($order->shipping_courier));
            
            Log::info("Biteship Tracking for #{$order->order_number}: " . json_encode($tracking));

            if ($tracking && isset($tracking['success']) && $tracking['success']) {
                $status = $tracking['status'] ?? 'allocated';
                
                // Auto-sync local order status based on Biteship status
                if (in_array($status, ['picking_up', 'picked_up', 'dropping_off', 'out_for_delivery', 'on_the_way', 'in_transit', 'dispatched'])) {
                    if ($order->status !== 'shipped') {
                        $order->update(['status' => 'shipped']);
                    }
                } elseif ($status === 'delivered') {
                    if ($order->status !== 'completed') {
                        $order->update(['status' => 'completed']);
                    }
                }

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

        // Fallback Simulation for testing
        if (in_array($order->status, ['processing', 'shipped', 'completed', 'cancelled'])) {
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
            } elseif ($order->status === 'cancelled') {
                $status = 'returned';
                $label = 'Pesanan Telah Dikembalikan';
                $history[] = ['status' => 'return_in_transit', 'note' => 'Paket sedang dalam perjalanan kembali ke penjual', 'time' => $order->updated_at->subMinutes(30)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'returned', 'note' => 'Selesai: Pesanan telah diterima kembali oleh penjual', 'time' => $order->updated_at->translatedFormat('d M, H:i')];
            } else {
                // processing
                $history[] = ['status' => 'allocated', 'note' => 'Mencari kurir terdekat...', 'time' => now()->subMinutes(5)->translatedFormat('d M, H:i')];
                $history[] = ['status' => 'allocated', 'note' => 'Kurir telah ditemukan dan sedang menuju lokasi Anda', 'time' => now()->subMinutes(2)->translatedFormat('d M, H:i')];
            }

            // Reverse history for chronological order (latest first)
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
                    'type' => 'Gojek Instant'
                ],
                'link' => $order->tracking_url,
                'history' => $history
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Belum ada informasi pelacakan.']);
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
