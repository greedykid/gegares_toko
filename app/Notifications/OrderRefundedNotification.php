<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent once the shop has recorded the money going back, closing the loop the
 * cancellation opened.
 */
class OrderRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->loadMissing('items');

        return (new MailMessage)
            ->subject('Dana Dikembalikan — Pesanan #'.$order->order_number)
            ->theme('gegares')
            ->markdown('mail.orders.refunded', [
                'order' => $order,
                'user' => $notifiable,
            ]);
    }
}
