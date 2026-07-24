<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Gegares - {{ now()->format('d/m/Y') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-shadow-none { shadow: none !important; box-shadow: none !important; }
            @page { margin: 1.5cm; }
            tr { page-break-inside: avoid; }
        }
        body { font-family: 'Quicksand', ui-sans-serif, system-ui, sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-4 md:p-10 antialiased">
    <div class="max-w-5xl mx-auto bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-100 print-shadow-none print:border-none">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start gap-6 border-b border-slate-100 pb-10 mb-10">
            <div>
                <h1 class="text-3xl font-black text-primary-700 tracking-tighter uppercase italic">GEGARES<span class="text-accent-500">.</span></h1>
                <p class="text-slate-500 text-sm mt-1.5 font-bold uppercase tracking-wider">Laporan Ringkasan Penjualan</p>
                <div class="mt-4 text-xs text-slate-400 space-y-1 font-medium">
                    <p>Periode: {{ request('from_date', now()->subMonth()->format('d/m/Y')) }} - {{ request('to_date', now()->format('d/m/Y')) }}</p>
                    <p>Status: {{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}</p>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Dicetak Oleh</p>
                <p class="text-sm font-extrabold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ now()->translatedFormat('d F Y, H:i') }}</p>
                
                <div class="mt-6 no-print">
                    <button onclick="window.print()" class="px-5 py-3 bg-primary-600 text-white text-xs font-bold rounded-xl hover:bg-primary-700 transition-all flex items-center gap-2 duration-200">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.617 0-1.11-.461-1.12-1.078L6.34 18m11.32 0a1.152 1.152 0 0 0 1.059-1.086L19.5 8.25m-14 8.75a1.152 1.152 0 0 1-1.059-1.086L3.5 8.25m16 0a2.25 2.25 0 0 0-2.247-2.118H6.247A2.25 2.25 0 0 0 4 8.25m16 0V6a2.25 2.25 0 0 0-2.25-2.25h-7.5A2.25 2.25 0 0 0 8 6v2.25m4-3.037.01-.011m-.01.011-.01-.011m0 .011.011-.011" />
                        </svg>
                        Cetak Laporan / Simpan PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Summary --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="p-5 rounded-2xl bg-slate-50/50 border border-slate-100 shadow-xs">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Transaksi</p>
                <p class="text-xl font-black text-slate-900 mt-1.5">{{ number_format($orders->count()) }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-slate-50/50 border border-slate-100 shadow-xs">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pesanan Selesai</p>
                <p class="text-xl font-black text-emerald-650 mt-1.5">{{ number_format($orders->where('status', 'completed')->count()) }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-slate-50/50 border border-slate-100 shadow-xs">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Penjualan Kotor</p>
                <p class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($orders->sum('subtotal'), 0, ',', '.') }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-primary-950 border border-primary-900 shadow-xs">
                <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest">Total Pendapatan Bersih</p>
                <p class="text-xl font-black text-white mt-1.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 mb-10">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-left">
                        <th class="px-5 py-3.5 font-bold text-slate-550 uppercase text-[10px] tracking-wide">Tanggal</th>
                        <th class="px-5 py-3.5 font-bold text-slate-550 uppercase text-[10px] tracking-wide">No. Pesanan</th>
                        <th class="px-5 py-3.5 font-bold text-slate-550 uppercase text-[10px] tracking-wide">Pelanggan</th>
                        <th class="px-5 py-3.5 font-bold text-slate-550 uppercase text-[10px] tracking-wide">Status</th>
                        <th class="px-5 py-3.5 font-bold text-slate-550 uppercase text-[10px] tracking-wide text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 font-medium text-slate-700">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/30 transition-colors">
                        <td class="px-5 py-4 text-slate-500 text-xs">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-slate-900 font-bold tracking-tight">{{ $order->order_number }}</td>
                        <td class="px-5 py-4">
                            <div class="text-slate-900 font-extrabold">{{ $order->user->name ?? '-' }}</div>
                            <div class="text-[9px] text-slate-400 font-medium italic mt-0.5 leading-relaxed">
                                @foreach($order->items as $item)
                                    {{ $item->product_name }}{{ $item->variant_name ? " ($item->variant_name)" : "" }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wide border {{ match($order->status) { 'completed' => 'bg-emerald-50 text-emerald-650 border-emerald-100', 'cancelled' => 'bg-red-50 text-red-655 border-red-100', default => 'bg-amber-50 text-amber-650 border-amber-100' } }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right text-slate-900 font-extrabold font-mono text-sm">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-20 text-center text-slate-400 font-bold uppercase tracking-widest">
                            Tidak ada data dalam periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Details --}}
        <div class="flex flex-col md:flex-row justify-between items-start gap-10">
            <div class="max-w-sm">
                <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Catatan Laporan</h5>
                <p class="text-[10px] text-slate-400 leading-relaxed italic font-medium">
                    Laporan ini dihasilkan secara otomatis oleh sistem administrasi Gegares. Pendapatan Bersih dihitung berdasarkan pesanan dengan status 'Selesai'. Data tidak mencakup potongan biaya payment gateway Pakasir.
                </p>
            </div>
            
            <div class="w-full md:w-64 space-y-3 pt-6 border-t border-slate-100 md:border-t-0 text-slate-650 font-medium">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Subtotal Penjualan</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($orders->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-red-500">
                    <span class="">Potongan Diskon</span>
                    <span class="font-bold">- Rp {{ number_format($orders->sum('discount_amount'), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Total Ongkir</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($orders->sum('shipping_cost'), 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 border-t-2 border-slate-900 flex justify-between items-center">
                    <span class="text-xs font-black text-slate-900 uppercase">Grand Total</span>
                    <span class="text-lg font-black text-slate-900 tracking-tighter">Rp {{ number_format($orders->sum('total'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Signatures for Printing --}}
        <div class="hidden print:flex mt-20 justify-end gap-10">
            <div class="w-48 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-16">Penanggung Jawab</p>
                <div class="border-b border-slate-200 mb-1"></div>
                <p class="text-[10px] font-bold text-slate-900 uppercase tracking-widest">Administrasi Gegares</p>
            </div>
        </div>

    </div>

    <div class="mt-8 text-center no-print">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-slate-600 text-xs font-bold uppercase tracking-wider transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Slight delay to ensure styles and fonts render before print triggers
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
