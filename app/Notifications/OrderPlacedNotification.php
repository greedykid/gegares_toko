<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer right after an order is created (checkout or AI chatbot),
 * confirming the order was received and prompting payment. Queued so the email
 * never blocks the checkout HTTP request.
 */
class OrderPlacedNotification extends Notification implements ShouldQueue
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
            ->subject('Pesanan Diterima #' . $order->order_number)
            ->markdown('mail.orders.placed', [
                'order' => $order,
                'user' => $notifiable,
            ]);
    }
}
