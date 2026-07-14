@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

    $rows = [
        ['label' => 'Subtotal', 'value' => $rupiah($order->subtotal)],
        ['label' => 'Ongkos kirim', 'value' => $rupiah($order->shipping_cost)],
    ];

    if ((float) $order->discount_amount > 0) {
        $rows[] = ['label' => 'Diskon', 'value' => '− ' . $rupiah($order->discount_amount)];
    }

    $rows[] = ['label' => 'Total', 'value' => $rupiah($order->total), 'total' => true];

    $courier = trim(strtoupper($order->shipping_courier . ' ' . $order->shipping_service));
@endphp

<x-mail::message>
@include('mail.orders.partials.orderhead', ['order' => $order, 'tone' => 'pending', 'label' => 'Menunggu pembayaran'])

# Terima kasih, {{ $user->name }}! 🎉

Pesanan Kakak sudah kami terima. Tinggal selesaikan pembayaran, lalu jajanannya langsung kami siapkan.

<x-mail::table>
| Produk | Qty | Subtotal |
|:-------|:---:|---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_name ? ' (' . $item->variant_name . ')' : '' }} | {{ $item->quantity }} | {{ $rupiah($item->subtotal) }} |
@endforeach
</x-mail::table>

@include('mail.orders.partials.summary', ['rows' => $rows])

@if ($courier)
<p style="margin: 14px 0 24px; font-size: 13px; color: #93a294;">Dikirim via {{ $courier }}</p>
@endif

@if ($order->pakasir_link)
<x-mail::button :url="$order->pakasir_link" color="success">
Bayar Sekarang
</x-mail::button>

<p style="margin: -12px 0 26px; text-align: center; font-size: 13px;">
    <a href="{{ route('orders.show', $order) }}" style="color: #7d8c7e; text-decoration: underline;">Lihat detail pesanan</a>
</p>
@else
<x-mail::button :url="route('orders.show', $order)" color="success">
Lihat Detail Pesanan
</x-mail::button>
@endif

Terima kasih sudah berbelanja di {{ config('app.name') }} 🙏

Salam hangat,<br>
Tim {{ config('app.name') }}
</x-mail::message>
