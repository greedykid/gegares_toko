@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

    // Show the breakdown, not just the total: without the shipping line a
    // "Total dibayar Rp 46.000" next to a single Rp 13.000 item looks wrong.
    $rows = [
        ['label' => 'Subtotal', 'value' => $rupiah($order->subtotal)],
        ['label' => 'Ongkos kirim', 'value' => $rupiah($order->shipping_cost)],
    ];

    if ((float) $order->discount_amount > 0) {
        $rows[] = ['label' => 'Diskon', 'value' => '− ' . $rupiah($order->discount_amount)];
    }

    $rows[] = ['label' => 'Total dibayar', 'value' => $rupiah($order->total), 'total' => true];

    $method = strtoupper($order->payment_method ?? '');
    $paidAt = $order->paid_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
@endphp

<x-mail::message>
@include('mail.orders.partials.orderhead', ['order' => $order, 'tone' => 'success', 'label' => 'Lunas'])

# Pembayaran berhasil, {{ $user->name }}! 🎉

Pembayaran Kakak sudah kami terima. Pesanan akan segera kami siapkan dan kirimkan — kami kabari lagi begitu paketnya jalan.

<x-mail::table>
| Produk | Qty | Subtotal |
|:-------|:---:|---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_name ? ' (' . $item->variant_name . ')' : '' }} | {{ $item->quantity }} | {{ $rupiah($item->subtotal) }} |
@endforeach
</x-mail::table>

@include('mail.orders.partials.summary', ['rows' => $rows])

{{-- One sentence rather than stacked "**Label:** value" lines, which Markdown
     would collapse into a single run-on paragraph. --}}
<p style="margin: 14px 0 24px; font-size: 13px; color: #93a294;">
    Dibayar{{ $method ? ' dengan ' . $method : '' }}{{ $paidAt ? ' pada ' . $paidAt . ' WIB' : '' }}
</p>

<x-mail::button :url="route('orders.show', $order)" color="success">
Lihat Detail Pesanan
</x-mail::button>

Terima kasih sudah berbelanja di {{ config('app.name') }} 🙏

Salam hangat,<br>
Tim {{ config('app.name') }}
</x-mail::message>
