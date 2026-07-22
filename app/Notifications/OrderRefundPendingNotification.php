<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an order the customer already paid for is cancelled, so they hear it
 * from us rather than discovering a cancelled order and wondering about the
 * money. Queued so a mail failure never blocks the cancellation itself.
 */
class OrderRefundPendingNotification extends Notification implements ShouldQueue
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
            ->subject('Pengembalian Dana Pesanan #'.$order->order_number)
            ->theme('gegares')
            ->markdown('mail.orders.refund-pending', [
                'order' => $order,
                'user' => $notifiable,
            ]);
    }
}
