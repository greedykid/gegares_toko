{{--
    Deliberately standalone: no layout, no Livewire, no database, no compiled
    asset. layouts/app mounts six Livewire components and reads StoreSetting for
    the footer, so extending it here would re-throw on exactly the failures that
    produce a 500 in the first place — a dead database, a broken cache — and the
    customer would land on Laravel's bare "Server Error" instead.

    Everything below is inline for the same reason: a 500 caused by a missing or
    stale build manifest must still render.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Ada Gangguan — Gegares</title>
    <style>
        :root {
            --bg: #f4f4f0;
            --card: #ffffff;
            --ink: #1a1a17;
            --muted: #6b6b63;
            --line: #eae9e1;
            --brand: #38943b;
            --brand-dark: #2a6f2c;
            --accent: #b91c1c;
            --accent-soft: #fef2f2;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0b0b0a;
                --card: #161614;
                --ink: #f4f4f0;
                --muted: #9c9c92;
                --line: #262622;
                --brand: #4faf52;
                --brand-dark: #38943b;
                --accent: #f87171;
                --accent-soft: #1f1211;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--bg);
            color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .06);
        }
        .badge {
            width: 72px; height: 72px;
            margin: 0 auto 24px;
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            background: var(--accent-soft);
            color: var(--accent);
        }
        .badge svg { width: 34px; height: 34px; }
        .eyebrow {
            margin: 0 0 8px;
            font-size: 11px; font-weight: 800;
            letter-spacing: .2em; text-transform: uppercase;
            color: var(--muted);
        }
        h1 { margin: 0 0 12px; font-size: 24px; font-weight: 800; line-height: 1.25; }
        p.lead { margin: 0 0 28px; font-size: 15px; line-height: 1.65; color: var(--muted); }
        .actions { display: flex; flex-direction: column; gap: 10px; }
        @media (min-width: 480px) { .actions { flex-direction: row; justify-content: center; } }
        a.btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 24px;
            border-radius: 14px;
            font-size: 14px; font-weight: 700;
            text-decoration: none;
            transition: background .15s ease, border-color .15s ease;
        }
        a.primary { background: var(--brand); color: #fff; }
        a.primary:hover { background: var(--brand-dark); }
        a.ghost { background: transparent; color: var(--ink); border: 1px solid var(--line); }
        a.ghost:hover { border-color: var(--brand); }
        .note { margin: 28px 0 0; font-size: 12px; color: var(--muted); }
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 2px 6px;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="badge" aria-hidden="true">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>

        <p class="eyebrow">Error 500</p>
        <h1>Ada gangguan di sisi kami</h1>
        <p class="lead">
            Ini bukan kesalahanmu. Sistem kami sedang bermasalah dan tim sudah menerima
            laporannya. Coba muat ulang beberapa saat lagi.
        </p>

        <div class="actions">
            <a class="btn primary" href="/">Kembali ke Beranda</a>
            <a class="btn ghost" href="javascript:location.reload()">Muat Ulang</a>
        </div>

        <p class="note">
            Kalau pesananmu sedang diproses, <strong>pembayaran dan stok tetap aman</strong> —
            tidak ada yang hilang karena halaman ini.
        </p>
    </main>
</body>
</html>
