<x-mail::message>
# Peringatan Keamanan Terdeteksi

Sistem keamanan Gegares telah mendeteksi aktivitas yang mencurigakan dengan tingkat keparahan **{{ strtoupper($event->severity) }}**.

**Detail Kejadian:**
- **Tipe:** {{ $event->event_type }}
- **Alamat IP:** {{ $event->ip_address }}
- **Waktu:** {{ $event->created_at->format('d M Y H:i:s') }}
- **User ID:** {{ $event->user_id ?? 'Guest' }}
- **Session:** `{{ $event->session_id }}`

**Payload/Input:**
```text
{{ $event->payload }}
```

@if($event->metadata)
**Metadata Tambahan:**
```json
{{ json_encode($event->metadata, JSON_PRETTY_PRINT) }}
```
@endif

Harap segera periksa log keamanan di Admin Panel untuk detail lebih lanjut.

<x-mail::button :url="config('app.url') . '/admin/security'">
Buka Dashboard Keamanan
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }} Security System
</x-mail::message>
