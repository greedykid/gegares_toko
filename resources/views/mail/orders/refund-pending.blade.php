@php
    $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

    $rows = [
        ['label' => 'Subtotal', 'value' => $rupiah($order->subtotal)],
        ['label' => 'Ongkos kirim', 'value' => $rupiah($order->shipping_cost)],
    ];

    if ((float) $order->discount_amount > 0) {
        $rows[] = ['label' => 'Diskon', 'value' => '− '.$rupiah($order->discount_amount)];
    }

    $rows[] = ['label' => 'Dana dikembalikan', 'value' => $rupiah($order->total), 'total' => true];

    // Same WhatsApp number the order page offers, so both routes lead to one inbox.
    $store = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn () => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting)->toArray()));
    $wa = preg_replace('/[^0-9]/', '', $store->contact_whatsapp ?? $store->contact_phone ?? '6281234567890');
    if (str_starts_with($wa, '0')) {
        $wa = '62'.substr($wa, 1);
    }
    $waUrl = 'https://wa.me/'.$wa.'?text='.rawurlencode("Halo Gegares, saya ingin menanyakan pengembalian dana untuk pesanan *{$order->order_number}* senilai *{$order->formatted_total}*. Terima kasih.");
@endphp

<x-mail::message>
@include('mail.orders.partials.orderhead', ['order' => $order, 'tone' => 'pending', 'label' => 'Menunggu Refund'])

# Pesanan dibatalkan, dana Kakak kami kembalikan

Halo {{ $user->name }}, mohon maaf pesanan ini tidak dapat kami lanjutkan. Pembayaran Kakak sudah kami terima, jadi dananya akan kami kembalikan sepenuhnya.

<x-mail::table>
| Produk | Qty | Subtotal |
|:-------|:---:|---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_name ? ' ('.$item->variant_name.')' : '' }} | {{ $item->quantity }} | {{ $rupiah($item->subtotal) }} |
@endforeach
</x-mail::table>

@include('mail.orders.partials.summary', ['rows' => $rows])

<p style="margin: 14px 0 24px; font-size: 13px; color: #93a294;">
    Silakan hubungi kami lewat WhatsApp untuk memproses pengembalian dana. Sertakan nomor rekening atau e-wallet tujuan ya Kak.
</p>

<x-mail::button :url="$waUrl" color="success">
Hubungi Admin untuk Refund
</x-mail::button>

Mohon maaf atas ketidaknyamanannya 🙏

Salam hangat,<br>
Tim {{ config('app.name') }}
</x-mail::message>
