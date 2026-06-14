<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Gegares - {{ now()->format('d/m/Y') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-shadow-none { shadow: none !important; box-shadow: none !important; }
            @page { margin: 1.5cm; }
        }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-4 md:p-10">
    <div class="max-w-5xl mx-auto bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-100 print-shadow-none print:border-none">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start gap-6 border-b border-slate-100 pb-10 mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">GEGARES<span class="text-orange-500">.</span></h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Laporan Ringkasan Penjualan</p>
                <div class="mt-4 text-xs text-slate-400 space-y-1">
                    <p>Periode: {{ request('from_date', now()->subMonth()->format('d/m/Y')) }} - {{ request('to_date', now()->format('d/m/Y')) }}</p>
                    <p>Status: {{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}</p>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Dicetak Oleh</p>
                <p class="text-sm font-extrabold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ now()->translatedFormat('d F Y, H:i') }}</p>
                
                <div class="mt-6 no-print">
                    <button onclick="window.print()" class="px-5 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM14.25 15h.008v.008H14.25V15zm0 2.25h.008v.008H14.25v-.008z" />
                        </svg>
                        Cetak Laporan / Simpan PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Summary --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Transaksi</p>
                <p class="text-xl font-black text-slate-900 mt-1">{{ $orders->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pesanan Selesai</p>
                <p class="text-xl font-black text-emerald-600 mt-1">{{ $orders->where('status', 'completed')->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Penjualan Kotor</p>
                <p class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($orders->sum('subtotal'), 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Pendapatan Bersih</p>
                <p class="text-xl font-black text-white mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 mb-10">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-left">
                        <th class="px-5 py-3 font-bold text-slate-600 uppercase text-[10px]">Tanggal</th>
                        <th class="px-5 py-3 font-bold text-slate-600 uppercase text-[10px]">No. Pesanan</th>
                        <th class="px-5 py-3 font-bold text-slate-600 uppercase text-[10px]">Pelanggan</th>
                        <th class="px-5 py-3 font-bold text-slate-600 uppercase text-[10px]">Status</th>
                        <th class="px-5 py-3 font-bold text-slate-600 uppercase text-[10px] text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 font-medium">
                    @forelse($orders as $order)
                    <tr>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-slate-900 font-bold tracking-tight">{{ $order->order_number }}</td>
                        <td class="px-5 py-3 text-slate-600">
                            <div>{{ $order->user->name ?? '-' }}</div>
                            <div class="text-[9px] text-slate-400 italic">
                                @foreach($order->items as $item)
                                    {{ $item->product_name }}{{ $item->variant_name ? " ($item->variant_name)" : "" }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-[10px] font-bold uppercase tracking-widest {{ $order->status === 'completed' ? 'text-emerald-600' : ($order->status === 'cancelled' ? 'text-red-500' : 'text-amber-500') }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right text-slate-900 font-extrabold font-mono">
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
                <p class="text-[10px] text-slate-400 leading-relaxed italic">
                    Laporan ini dihasilkan secara otomatis oleh sistem administrasi Gegares. Pendapatan Bersih dihitung berdasarkan pesanan dengan status 'Selesai'. Data tidak mencakup potongan biaya payment gateway Midtrans.
                </p>
            </div>
            
            <div class="w-full md:w-64 space-y-3 pt-6 border-t border-slate-100 md:border-t-0">
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
        <a href="{{ route('admin.orders.index') }}" class="text-slate-400 hover:text-slate-600 text-[10px] font-bold uppercase tracking-widest transition-all">
            &larr; Kembali ke Dashboard
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
