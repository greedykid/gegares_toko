@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<x-mail::message>
# Pembayaran Berhasil ✅

Halo {{ $user->name }}, pembayaran untuk pesanan **#{{ $order->order_number }}** sudah kami terima.
Pesanan Kakak akan segera kami proses dan kirimkan.

<x-mail::table>
| Produk | Qty | Subtotal |
|:------- |:---:| --------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_name ? ' (' . $item->variant_name . ')' : '' }} | {{ $item->quantity }} | {{ $rupiah($item->subtotal) }} |
@endforeach
</x-mail::table>

**Total Dibayar:** {{ $rupiah($order->total) }}
**Metode Pembayaran:** {{ strtoupper($order->payment_method ?? '-') }}
@if ($order->paid_at)
**Waktu Pembayaran:** {{ $order->paid_at->timezone(config('app.timezone'))->format('d M Y H:i') }}
@endif

<x-mail::button :url="route('orders.show', $order)" color="success">
Lihat Detail Pesanan
</x-mail::button>

Terima kasih sudah berbelanja di {{ config('app.name') }}. 🙏

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>
