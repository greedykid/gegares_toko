<?php

namespace App\Support;

use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Support\Carbon;

/**
 * Stand-in courier data for demos, so the "Informasi Kurir" panel is populated
 * when Biteship has nothing to say (sandbox keys, or a shipment that was never
 * really booked).
 *
 * OFF BY DEFAULT, and deliberately so. The controllers used to answer with a
 * hardcoded driver whenever tracking was empty, and that was removed because it
 * showed a customer a delivery that was not happening — see the comment in
 * OrderController::getTracking(). Turning DEMO_COURIER on brings that behaviour
 * back on purpose; it belongs on a demo deployment, not one taking real orders.
 *
 * Two rules keep the made-up data from doing harm even while it is on:
 *
 *  - the phone number is the shop's own, never an invented mobile number that
 *    would belong to some real stranger the customer then calls;
 *  - the photo is the shop owner's own likeness, supplied for this purpose. It
 *    is not a face taken from an avatar service or a stock library, which would
 *    put a stranger's picture on this order's delivery.
 *
 * The identity is fixed rather than generated, so every order shows the same
 * driver instead of one face wearing a different name each time.
 */
class DemoCourier
{
    /**
     * One driver, not a pool. The photo is a specific person, so the name and
     * plate have to stay pinned to it — a face that came with a different name
     * on the next order would give the whole panel away.
     */
    private const NAME = 'Farhan Adhi';

    private const PLATE = 'B 6127 KRA';

    /**
     * Relative to public/, first match wins. Absent file → initials, so a
     * missing asset is not fatal; the extension is not worth a round trip.
     */
    private const PHOTO_CANDIDATES = [
        'images/demo-courier.jpg',
        'images/demo-courier.jpeg',
        'images/demo-courier.png',
        'images/demo-courier.webp',
    ];

    public static function enabled(): bool
    {
        return (bool) config('app.demo_courier', false);
    }

    /**
     * A tracking payload shaped like the real endpoint's, or null when demo mode
     * is off or the order has not reached a stage where a courier would exist.
     */
    public static function payload(Order $order): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        // A pending or cancelled order has no courier in real life either, and
        // inventing one there contradicts the status shown right next to it.
        if (! in_array($order->status, ['processing', 'shipped', 'completed'], true)) {
            return null;
        }

        $status = match ($order->status) {
            'completed' => 'delivered',
            'shipped' => 'on_the_way',
            default => 'allocated',
        };

        return [
            'success' => true,
            'demo' => true,
            'status' => $status,
            'status_label' => match ($status) {
                'delivered' => 'Terkirim',
                'on_the_way' => 'Dalam Perjalanan',
                default => 'Kurir Ditugaskan',
            },
            'courier' => [
                'name' => self::NAME,
                'phone' => self::shopPhone(),
                'plate_number' => self::PLATE,
                'photo' => self::photoUrl(),
                'type' => trim($order->shipping_courier.' '.$order->shipping_service),
            ],
            'link' => $order->tracking_url,
            'history' => self::history($order, $status),
        ];
    }

    /** Whether this order would get a stand-in, without building the payload. */
    public static function appliesTo(Order $order): bool
    {
        return self::enabled()
            && in_array($order->status, ['processing', 'shipped', 'completed'], true);
    }

    /**
     * The shop's own number. A customer who taps "hubungi kurir" reaches the
     * shop rather than a stranger whose number happened to be plausible.
     */
    private static function shopPhone(): string
    {
        $phone = StoreSetting::first()?->contact_whatsapp;

        return $phone ? '+'.ltrim((string) $phone, '+') : '-';
    }

    /** Null when the file was never added, so the views fall back to initials. */
    private static function photoUrl(): ?string
    {
        foreach (self::PHOTO_CANDIDATES as $path) {
            if (file_exists(public_path($path))) {
                // Cache-bust on mtime: replacing the photo would otherwise keep
                // showing the old one from the browser cache.
                return asset($path).'?v='.filemtime(public_path($path));
            }
        }

        return null;
    }

    /** Plausible progress, anchored to timestamps the order actually has. */
    private static function history(Order $order, string $status): array
    {
        $start = $order->paid_at ? Carbon::parse($order->paid_at) : $order->created_at->copy();

        $events = [
            ['status' => 'allocated', 'note' => 'Kurir ditugaskan untuk pesanan ini.', 'at' => $start->copy()->addMinutes(20)],
            ['status' => 'picking_up', 'note' => 'Kurir menuju lokasi penjemputan.', 'at' => $start->copy()->addMinutes(45)],
        ];

        if (in_array($status, ['on_the_way', 'delivered'], true)) {
            $events[] = ['status' => 'picked_up', 'note' => 'Paket telah dijemput dari toko.', 'at' => $start->copy()->addMinutes(70)];
            $events[] = ['status' => 'on_the_way', 'note' => 'Paket sedang dalam perjalanan ke alamat tujuan.', 'at' => $start->copy()->addMinutes(95)];
        }

        if ($status === 'delivered') {
            $delivered = $order->delivered_at ? Carbon::parse($order->delivered_at) : $start->copy()->addMinutes(150);
            $events[] = ['status' => 'delivered', 'note' => 'Paket telah diterima di alamat tujuan.', 'at' => $delivered];
        }

        // Newest first, matching what the real endpoint returns.
        return array_map(fn ($e) => [
            'status' => $e['status'],
            'note' => $e['note'],
            'time' => $e['at']->translatedFormat('d M, H:i'),
        ], array_reverse($events));
    }

}
