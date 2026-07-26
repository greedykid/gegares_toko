<?php

namespace App\Services\Chatbot;

use App\Models\Order;

/**
 * Decides which of a customer's orders the bot still owes them a word about, and
 * writes that message. Acknowledgement lives in the session so an order is
 * announced exactly once, however many times the component re-mounts.
 *
 * Returns plain reply payloads (content / buttons / suggestions) — appending them
 * to the transcript and telling Livewire about it stays with the component.
 */
class ChatbotOrderAnnouncer
{
    /** How far back a settled payment still counts as "just now". */
    private const RECENT_WINDOW_HOURS = 2;

    private const PAID_KEY = 'gegares_acknowledged_paid_orders';

    private const UNPAID_KEY = 'gegares_acknowledged_unpaid_orders';

    /**
     * Announcements for chatbot orders paid in the last couple of hours that the
     * customer has not been told about yet.
     *
     * @return array<int, array>
     */
    public function recentlyPaid(int $userId): array
    {
        $orders = Order::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', now()->subHours(self::RECENT_WINDOW_HOURS))
            ->where('source', 'chatbot')
            ->get();

        $messages = [];

        foreach ($orders as $order) {
            if ($this->isAcknowledged(self::PAID_KEY, $order->id)) {
                continue;
            }

            $this->acknowledge(self::PAID_KEY, $order->id);
            $messages[] = $this->paidMessage($order);
        }

        return $messages;
    }

    /**
     * The order the customer was just redirected to, when it still needs a word.
     * Null when there is nothing to say — no order on the route, someone else's
     * order, or one already acknowledged.
     */
    public function forRedirectedOrder(int $userId): ?array
    {
        $order = $this->resolveRouteOrder($userId);

        if (! $order) {
            return null;
        }

        // Only speak up for orders this chatbot created, unless the caller asked
        // for it explicitly with ?chatbot_open=1.
        $isChatbotOrder = $order->isFromChatbot();

        if (! $isChatbotOrder && request()->query('chatbot_open') !== '1') {
            return null;
        }

        if ($order->payment_status === 'paid') {
            if (! $isChatbotOrder || $this->isAcknowledged(self::PAID_KEY, $order->id)) {
                return null;
            }

            $this->acknowledge(self::PAID_KEY, $order->id);

            return $this->paidMessage($order);
        }

        if ($this->isAcknowledged(self::UNPAID_KEY, $order->id)) {
            return null;
        }

        $this->acknowledge(self::UNPAID_KEY, $order->id);

        return $this->unpaidMessage($order);
    }

    private function paidMessage(Order $order): array
    {
        return [
            'content' => "Yey! Pembayaran untuk pesanan dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** telah berhasil kami terima. Terima kasih banyak ya Kak! Pesanan Kakak akan segera kami proses dan kirim.",
            'buttons' => [
                [
                    'label' => 'Lihat Detail Pesanan',
                    'url' => route('orders.show', $order->id),
                    'style' => 'primary',
                ],
                [
                    'label' => 'Lihat Riwayat Pesanan',
                    'url' => route('orders.index'),
                    'style' => 'secondary',
                ],
            ],
            'suggestions' => [
                'Lacak pengiriman',
                'Jam operasional & lokasi toko',
                'Hubungi CS via WhatsApp',
            ],
        ];
    }

    private function unpaidMessage(Order $order): array
    {
        $buttons = [];

        if ($order->pakasir_link) {
            $buttons[] = [
                'label' => 'Bayar Sekarang (Pakasir)',
                'url' => $order->pakasir_link,
                'style' => 'primary',
            ];
        }

        $buttons[] = [
            'label' => 'Lihat Detail Pesanan',
            'url' => route('orders.show', $order->id),
            'style' => 'secondary',
        ];

        return [
            'content' => "Halo Kak! Pembayaran untuk pesanan dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** belum selesai atau belum kami terima.\n\nSilakan selesaikan pembayaran Kakak dengan mengeklik tombol **Bayar Sekarang** di bawah ini agar pesanan Kakak dapat segera kami proses.",
            'buttons' => $buttons,
            'suggestions' => [
                'Cek status pesanan saya',
                'Cara bayar pesanan',
                'Hubungi CS via WhatsApp',
            ],
        ];
    }

    /** Whether the order bound to the current route was placed through the chatbot. */
    public function routeOrderIsFromChatbot(): bool
    {
        return (bool) $this->routeOrder()?->isFromChatbot();
    }

    /** The order bound to the current route, but only if it belongs to this user. */
    private function resolveRouteOrder(int $userId): ?Order
    {
        // fresh() so an announcement never reads a payment status that was
        // already stale when the route model was bound.
        $order = $this->routeOrder()?->fresh();

        if (! $order || (int) $order->user_id !== $userId) {
            return null;
        }

        return $order;
    }

    private function routeOrder(): ?Order
    {
        $routeOrder = request()->route('order');

        return match (true) {
            $routeOrder instanceof Order => $routeOrder,
            is_numeric($routeOrder), is_string($routeOrder) => Order::find($routeOrder),
            default => null,
        };
    }

    private function isAcknowledged(string $key, int $orderId): bool
    {
        return in_array($orderId, session($key, []));
    }

    private function acknowledge(string $key, int $orderId): void
    {
        $acknowledged = session($key, []);
        $acknowledged[] = $orderId;
        session([$key => $acknowledged]);
    }
}
