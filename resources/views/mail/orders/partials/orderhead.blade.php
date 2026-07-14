{{--
    Status pill + order reference block used at the top of the order emails.

    @param \App\Models\Order $order
    @param string $tone   'success' (lunas) or 'pending' (menunggu pembayaran)
    @param string $label  text inside the pill
--}}
@php
    $palette = $tone === 'success'
        ? ['bg' => '#edf8ed', 'fg' => '#1c4a1e', 'dot' => '#38943b']
        : ['bg' => '#fdf6e3', 'fg' => '#5c4a08', 'dot' => '#f0b80f'];
@endphp

{{-- The pill is a table, not an inline-block span: the dot and the label sit in
     sibling cells, which is the only vertical centring email clients agree on.
     An inline span with a &nbsp; inside it inherited the line-height and drew the
     dot as an oval floating above the text. --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; border-collapse: collapse; margin: 0 0 18px;">
<tr>
<td style="padding: 0;">
    <table cellpadding="0" cellspacing="0" role="presentation" align="left" style="border-collapse: collapse; background-color: {{ $palette['bg'] }}; border-radius: 999px;">
    <tr>
    <td style="padding: 7px 13px;">
        <table cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: collapse;">
        <tr>
        <td width="7" style="width: 7px; line-height: 0; font-size: 0;">
            <div style="width: 7px; height: 7px; background-color: {{ $palette['dot'] }}; border-radius: 50%; line-height: 7px; font-size: 0;">&nbsp;</div>
        </td>
        <td style="padding-left: 7px; color: {{ $palette['fg'] }}; font-size: 11px; font-weight: 700; letter-spacing: 1px; line-height: 1; text-transform: uppercase; white-space: nowrap;">
            {{ $label }}
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>
</td>
</tr>
</table>

{{-- Only the date is pinned to one line — it is short and always fits, and it was
     the one breaking into "14 Jul / 2026". The order number is left free to wrap:
     forcing it to nowrap too pushed the box past the card and gave the whole mail
     a horizontal scrollbar on a 320px phone. --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f7faf7; border: 1px solid #e8ede8; border-radius: 12px; margin: 0 0 8px;">
<tr>
<td style="padding: 14px; text-align: left; vertical-align: top;">
    <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; color: #93a294;">Nomor pesanan</div>
    <div style="font-size: 14px; font-weight: 700; color: #14300f; margin-top: 3px;">#{{ $order->order_number }}</div>
</td>
<td style="padding: 14px; text-align: right; vertical-align: top;">
    <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; color: #93a294;">Tanggal</div>
    <div style="font-size: 14px; font-weight: 700; color: #14300f; margin-top: 3px; white-space: nowrap;">{{ $order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</div>
</td>
</tr>
</table>
