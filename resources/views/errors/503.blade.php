{{--
    Standalone for the same reason as 500, but a sharper one: this page is what
    `php artisan down` serves during a deploy — exactly the window when
    migrations are running, the config cache is being rebuilt, and the compiled
    assets are being replaced. Anything this page depends on may be mid-swap, so
    it depends on nothing: no layout, no Livewire, no database, no compiled asset.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta http-equiv="refresh" content="30">
    <title>Sedang Pemeliharaan — Gegares</title>
    <style>
        :root {
            --bg: #f4f4f0;
            --card: #ffffff;
            --ink: #1a1a17;
            --muted: #6b6b63;
            --line: #eae9e1;
            --brand: #38943b;
            --brand-dark: #2a6f2c;
            --brand-soft: #edf8ed;
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
                --brand-soft: #0e250f;
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
            background: var(--brand-soft);
            color: var(--brand);
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
        .note { margin: 28px 0 0; font-size: 12px; color: var(--muted); }
        .dots { display: inline-flex; gap: 4px; margin-left: 2px; vertical-align: middle; }
        .dots i {
            width: 4px; height: 4px; border-radius: 50%;
            background: var(--muted);
            animation: blink 1.4s infinite ease-in-out both;
        }
        .dots i:nth-child(2) { animation-delay: .2s; }
        .dots i:nth-child(3) { animation-delay: .4s; }
        @keyframes blink { 0%, 80%, 100% { opacity: .25 } 40% { opacity: 1 } }
        @media (prefers-reduced-motion: reduce) { .dots i { animation: none; opacity: .6 } }
    </style>
</head>
<body>
    <main class="card">
        <div class="badge" aria-hidden="true">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
            </svg>
        </div>

        <p class="eyebrow">Error 503</p>
        <h1>Sedang dalam pemeliharaan</h1>
        <p class="lead">
            Kami sedang memperbarui sistem sebentar<span class="dots" aria-hidden="true"><i></i><i></i><i></i></span><br>
            Halaman ini akan memuat ulang sendiri, atau kamu bisa mencoba lagi beberapa menit lagi.
        </p>

        <div class="actions">
            <a class="btn primary" href="/">Coba Lagi</a>
        </div>

        <p class="note">
            <strong>Pesanan, pembayaran, dan stok tetap aman.</strong>
            Tidak ada data yang hilang selama pemeliharaan.
        </p>
    </main>
</body>
</html>
