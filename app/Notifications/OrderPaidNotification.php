<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer once payment for an order is confirmed as paid.
 * Queued so the email never blocks the webhook / payment-confirmation path.
 */
class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

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
            ->subject('Pembayaran Berhasil #' . $order->order_number)
            ->theme('gegares')
            ->markdown('mail.orders.paid', [
                'order' => $order,
                'user' => $notifiable,
            ]);
    }
}
