<?php

namespace App\Support;

use App\Models\Order;

/**
 * Turns the staff-facing system trail on `orders.admin_note` into something the
 * customer can be shown.
 *
 * `admin_note` is written for whoever is working the order — it carries courier
 * status codes, internal to-dos ("PERLU REFUND"), and instructions aimed at the
 * shop ("Perbaiki jam buka toko atau booking manual"). Rendering it verbatim on
 * the customer's page would leak an operational misconfiguration and read as an
 * unactioned ticket, so each known event is restated here in the customer's
 * terms instead. Anything unrecognised is dropped rather than guessed at, which
 * makes silence the failure mode.
 *
 * The markers matched below are the ones written by BookBiteshipOrder,
 * AutoCancelOrders, OrderService and PakasirService — keep them in step.
 */
class CustomerOrderNote
{
    /**
     * Customer-safe lines for this order, oldest event first.
     *
     * @return array<int, string>
     */
    public static function for(Order $order): array
    {
        $trail = trim((string) $order->admin_note);

        if ($trail === '') {
            return [];
        }

        $lines = [];

        // Split on the separator the writers use so one entry cannot swallow the
        // next, then restate each entry we recognise.
        foreach (array_map('trim', explode('|', $trail)) as $entry) {
            $line = self::translate($entry, $order);

            if ($line !== null && ! in_array($line, $lines, true)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    private static function translate(string $entry, Order $order): ?string
    {
        // Money arrived after the order was already cancelled. The customer is
        // owed a refund and should not have to discover that by comparing dates.
        if (str_contains($entry, 'PERLU REFUND')) {
            return 'Pembayaran kamu kami terima setelah pesanan ini dibatalkan. Dana akan dikembalikan sepenuhnya — tim kami akan menghubungi kamu untuk prosesnya.';
        }

        // The shop's opening hours and the courier's pickup window do not
        // overlap. That is ours to fix, so the customer is told the outcome
        // (a human is arranging it) and not the cause.
        if (str_contains($entry, 'TIDAK BISA DIJEMPUT')) {
            return 'Penjemputan kurir belum bisa dijadwalkan otomatis untuk pesanan ini. Tim kami sedang mengatur pengirimannya secara manual.';
        }

        // Booking is deferred to the courier's next operating window. The
        // scheduled time is genuinely useful, so it is carried over when the
        // sentence still has it in the expected shape.
        if (str_contains($entry, 'MENUNGGU JAM KURIR')) {
            if (preg_match('/dijadwalkan\s+(.+?)\s+WIB/u', $entry, $m)) {
                return "Pesanan kamu sudah kami siapkan dan sedang menunggu jam operasional kurir. Penjemputan dijadwalkan {$m[1]} WIB.";
            }

            return 'Pesanan kamu sudah kami siapkan dan sedang menunggu jam operasional kurir berikutnya.';
        }

        if (str_contains($entry, 'Otomatis dibatalkan setelah')) {
            $hours = preg_match('/setelah\s+(\d+)\s+jam/u', $entry, $m) ? $m[1] : null;

            return $hours
                ? "Pesanan dibatalkan otomatis karena pembayaran belum selesai dalam {$hours} jam."
                : 'Pesanan dibatalkan otomatis karena pembayaran belum selesai tepat waktu.';
        }

        // Drop the raw courier status code — it means nothing outside Biteship.
        if (str_contains($entry, 'Dibatalkan otomatis dari Biteship')) {
            return $order->payment_status === 'paid'
                ? 'Pesanan dibatalkan karena pengiriman tidak dapat dilanjutkan oleh kurir. Dana kamu akan dikembalikan.'
                : 'Pesanan dibatalkan karena pengiriman tidak dapat dilanjutkan oleh kurir.';
        }

        return null;
    }
}
