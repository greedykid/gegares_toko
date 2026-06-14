<?php

namespace App\Livewire;

use App\Models\Coupon;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;

class NotificationDropdown extends Component
{
    public array $notificationsList = [];
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->updateNotifications();
    }

    #[On('notification-updated')]
    #[On('order-updated')]
    #[On('order-created')]
    public function updateNotifications(): void
    {
        if (!auth()->check()) {
            $this->notificationsList = [];
            $this->unreadCount = 0;
            return;
        }

        $user = auth()->user();
        $settings = $user->notification_settings ?? [];
        $lastReadAtStr = $settings['last_read_at'] ?? null;
        $lastReadAt = $lastReadAtStr ? Carbon::parse($lastReadAtStr) : null;

        $showOrderUpdates = $settings['order_updates'] ?? true;
        $showPromos = $settings['promos'] ?? true;

        $list = collect();

        // 1. Fetch user's active/recent orders (last 3)
        if ($showOrderUpdates) {
            $userOrders = $user->orders()
                ->latest()
                ->take(3)
                ->get();

            foreach($userOrders as $order) {
                $icon = 'primary';
                $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />';
                $title = 'Status Pesanan';
                $description = '';

                switch($order->status) {
                    case 'pending':
                    case 'awaiting_payment':
                        $icon = 'amber';
                        $title = 'Menunggu Pembayaran';
                        $description = "Pesanan #{$order->order_number} menanti pembayaran Anda. Silakan selesaikan transaksi.";
                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />';
                        break;
                    case 'paid':
                        $icon = 'emerald';
                        $title = 'Pembayaran Diterima';
                        $description = "Pembayaran untuk pesanan #{$order->order_number} berhasil diverifikasi. Pesanan akan segera diproses.";
                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />';
                        break;
                    case 'processing':
                        $icon = 'primary';
                        $title = 'Pesanan Diproses';
                        $description = "Pesanan #{$order->order_number} sedang disiapkan oleh penjual.";
                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.99l1.005.831a1.125 1.125 0 0 1 .26 1.43l-1.297 2.247a1.125 1.125 0 0 1-1.37.491l-1.216-.456c-.356-.133-.751-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.831a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.645-.869L9.594 3.94ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />';
                        break;
                    case 'shipped':
                        $icon = 'indigo';
                        $title = 'Pesanan Dikirim';
                        $description = "Pesanan #{$order->order_number} sedang dalam perjalanan dengan kurir {$order->shipping_courier}.";
                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.375-3h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-.75m-6-6h3.375m-9.75-3h9.75" />';
                        break;
                    case 'completed':
                        $icon = 'emerald';
                        $title = 'Pesanan Selesai';
                        $description = "Pesanan #{$order->order_number} telah selesai dan diterima dengan baik. Terima kasih!";
                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />';
                        break;
                    case 'cancelled':
                        $icon = 'red';
                        $title = 'Pesanan Dibatalkan';
                        $description = "Pesanan #{$order->order_number} telah dibatalkan.";
                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />';
                        break;
                    default:
                        $description = "Pesanan #{$order->order_number} berstatus {$order->status}.";
                }

                $orderTime = $order->updated_at;
                $isUnread = $lastReadAt === null || $orderTime->gt($lastReadAt);

                $list->push([
                    'type' => 'order',
                    'icon' => $icon,
                    'svg' => $svgPath,
                    'title' => $title,
                    'description' => $description,
                    'time' => $orderTime->diffForHumans(),
                    'url' => route('orders.show', $order->id),
                    'raw_time' => $orderTime->toIso8601String(),
                    'is_unread' => $isUnread,
                ]);
            }
        }

        // 2. Fetch active valid coupons (last 2)
        if ($showPromos) {
            $couponsList = Coupon::where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->latest()
                ->take(2)
                ->get();

            foreach($couponsList as $coupon) {
                $discountText = $coupon->type === 'percent' ? (int)$coupon->value . '%' : 'Rp ' . number_format($coupon->value, 0, ',', '.');
                
                $couponTime = $coupon->created_at ?? now();
                $isUnread = $lastReadAt === null || $couponTime->gt($lastReadAt);

                $list->push([
                    'type' => 'coupon',
                    'icon' => 'amber',
                    'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 0 0 3.181 0l5.103-5.103a2.25 2.25 0 0 0 0-3.181l-9.58-9.581A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />',
                    'title' => 'Voucher Spesial Aktif',
                    'description' => "Diskon spesial {$discountText} dengan kupon <span class=\"font-extrabold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-1 py-0.5 rounded\">{$coupon->code}</span> untuk Anda.",
                    'time' => $coupon->created_at ? $coupon->created_at->diffForHumans() : 'Baru',
                    'url' => route('home'),
                    'raw_time' => $couponTime->toIso8601String(),
                    'is_unread' => $isUnread,
                ]);
            }
        }

        // 3. Fallback welcome notification if no notifications exist
        if ($list->isEmpty()) {
            $systemTime = now()->subDay();
            $isUnread = $lastReadAt === null || $systemTime->gt($lastReadAt);

            $list->push([
                'type' => 'system',
                'icon' => 'emerald',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />',
                'title' => 'Selamat Datang!',
                'description' => 'Terima kasih telah bergabung dengan Gegares. Temukan kuliner dan jajanan lokal terbaik.',
                'time' => '1 hari lalu',
                'url' => route('home'),
                'raw_time' => $systemTime->toIso8601String(),
                'is_unread' => $isUnread,
            ]);
        }

        $this->notificationsList = $list->sortByDesc('raw_time')->values()->all();
        $this->unreadCount = $list->where('is_unread', true)->count();
    }

    public function markAsRead(): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        $settings = $user->notification_settings ?? [];
        $settings['last_read_at'] = Carbon::now()->toIso8601String();
        
        $user->update([
            'notification_settings' => $settings
        ]);

        $this->updateNotifications();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
