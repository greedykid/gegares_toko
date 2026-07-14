@props(['url'])
<tr>
<td class="header">
{{-- Logo beside the wordmark. Most clients block remote images by default, so
     the store name is real text and stays readable on its own. --}}
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin: 0 auto; border-collapse: collapse;">
<tr>
<td style="padding-right: 10px; vertical-align: middle;">
<img src="{{ asset('images/logo.png') }}" class="logo" alt="" width="44" height="44" style="display: block; height: 44px; width: 44px; border: 0;">
</td>
<td style="vertical-align: middle;">
<span style="color: #14300f; font-size: 23px; font-weight: 700; letter-spacing: -0.4px; line-height: 1;">{!! $slot !!}</span>
<span style="display: block; color: #93a294; font-size: 11px; font-weight: 600; letter-spacing: 1.4px; text-transform: uppercase; margin-top: 3px;">Jajanan pasar</span>
</td>
</tr>
</table>
</a>
</td>
</tr>
