@php
    $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

    $rows = [
        ['label' => 'Dana dikembalikan', 'value' => $rupiah($order->total), 'total' => true],
    ];

    $refundedAt = $order->refunded_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
@endphp

<x-mail::message>
@include('mail.orders.partials.orderhead', ['order' => $order, 'tone' => 'success', 'label' => 'Dana Dikembalikan'])

# Dana Kakak sudah kami kembalikan ✅

Halo {{ $user->name }}, pengembalian dana untuk pesanan yang dibatalkan ini sudah kami proses.

@include('mail.orders.partials.summary', ['rows' => $rows])

<p style="margin: 14px 0 24px; font-size: 13px; color: #93a294;">
    Diproses{{ $refundedAt ? ' pada '.$refundedAt.' WIB' : '' }}. Dana biasanya masuk dalam 1–3 hari kerja tergantung bank atau e-wallet Kakak. Jika sampai lewat dari itu belum masuk, kabari kami ya.
</p>

<x-mail::button :url="route('orders.show', $order)" color="success">
Lihat Detail Pesanan
</x-mail::button>

Terima kasih atas pengertiannya, semoga bisa melayani Kakak lagi lain waktu 🙏

Salam hangat,<br>
Tim {{ config('app.name') }}
</x-mail::message>
