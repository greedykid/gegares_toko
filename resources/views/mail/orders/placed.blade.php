@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<x-mail::message>
# Terima kasih, {{ $user->name }}! 🎉

Pesanan Kakak sudah kami terima dengan nomor **#{{ $order->order_number }}**.
Silakan selesaikan pembayaran agar pesanan segera kami proses.

<x-mail::table>
| Produk | Qty | Subtotal |
|:------- |:---:| --------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_name ? ' (' . $item->variant_name . ')' : '' }} | {{ $item->quantity }} | {{ $rupiah($item->subtotal) }} |
@endforeach
</x-mail::table>

**Subtotal:** {{ $rupiah($order->subtotal) }}
**Ongkir:** {{ $rupiah($order->shipping_cost) }}
@if ((float) $order->discount_amount > 0)
**Diskon:** -{{ $rupiah($order->discount_amount) }}
@endif
**Total:** {{ $rupiah($order->total) }}

@if ($order->pakasir_link)
<x-mail::button :url="$order->pakasir_link" color="success">
Bayar Sekarang
</x-mail::button>
@endif

<x-mail::button :url="route('orders.show', $order)">
Lihat Detail Pesanan
</x-mail::button>

Terima kasih sudah berbelanja di {{ config('app.name') }}.

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>
