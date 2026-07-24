@php
    $sort = $sort ?? request('sort', 'created_at');
    $dir = $dir ?? request('direction', 'desc');
    if (!function_exists('sortUrl')) {
        function sortUrl($column, $currentSort, $currentDir) {
            $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
        }
    }
@endphp
    <div class="overflow-x-auto lg:overflow-x-visible custom-scrollbar">
        <table class="w-full admin-table">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tl-2xl">
                        <a href="{{ sortUrl('order_number', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                            No. Pesanan
                            @if($sort === 'order_number')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        Pelanggan
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('total', $sort, $dir) }}" class="inline-flex items-center justify-end gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors w-full">
                            Total
                            @if($sort === 'total')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('status', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                            Status
                            @if($sort === 'status')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('payment_status', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                            Pembayaran
                            @if($sort === 'payment_status')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('created_at', $sort, $dir) }}" class="inline-flex items-center justify-end gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors w-full">
                            Tanggal
                            @if($sort === 'created_at')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tr-2xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-800/60">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                    <td data-label="No. Pesanan" class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-slate-100">
                        {{ $order->order_number }}
                    </td>
                    <td data-label="Pelanggan" class="px-6 py-4 text-sm text-slate-650 dark:text-slate-350">
                        {{ $order->user->name ?? '-' }}
                    </td>
                    <td data-label="Total" class="px-6 py-4 text-sm text-right font-bold text-slate-900 dark:text-slate-100">
                        {{ $order->formatted_total }}
                    </td>
                    <td data-label="Status" class="px-6 py-4">
                        <div class="flex justify-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold {{ match($order->status_color) { 'green','emerald' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-950/30', 'red' => 'bg-red-50 dark:bg-red-950/40 text-red-655 dark:text-red-400 border border-red-200/50 dark:border-red-950/30', 'orange' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-655 dark:text-orange-400 border border-orange-200/50 dark:border-orange-950/30', 'yellow' => 'bg-yellow-50 dark:bg-yellow-950/40 text-yellow-655 dark:text-yellow-400 border border-yellow-200/50 dark:border-yellow-950/30', default => 'bg-blue-50 dark:bg-blue-950/40 text-blue-655 dark:text-blue-400 border border-blue-200/50 dark:border-blue-950/30' } }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </td>
                    <td data-label="Pembayaran" class="px-6 py-4">
                        <div class="flex justify-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $order->payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                @if($order->payment_status === 'paid')
                                    {{ $order->payment_method ? strtoupper($order->payment_method) : 'PAID' }}
                                @else
                                    {{ $order->payment_status === 'unpaid' ? 'BELUM BAYAR' : strtoupper($order->payment_status) }}
                                @endif
                            </span>
                        </div>
                    </td>
                    <td data-label="Tanggal" class="px-6 py-4 text-sm text-right text-slate-400 dark:text-slate-500">
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>
                    <td data-label="Aksi" class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($order->tracking_number || $order->courier_tracking_id)
                            <div class="relative group/tooltip inline-flex">
                                <a href="{{ $order->tracking_url }}" 
                                   target="_blank"
                                   class="p-2 rounded-xl text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 transition-all hover:shadow-sm"
                                   title="Lacak Pengiriman">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                </a>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Lacak Pengiriman
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            @endif
                            <div class="relative group/tooltip inline-flex">
                                <button @click="selectedOrder = {{ $order->toJson() }}; showDetail = true"
                                        class="p-2 rounded-xl text-indigo-650 dark:text-indigo-400 hover:text-indigo-750 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 transition-all hover:shadow-sm"
                                        title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                </button>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Lihat Detail
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 transition-colors">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tidak ada pesanan ditemukan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Coba sesuaikan filter atau hapus pencarian Anda.</p>
                            @if(request()->anyFilled(['search', 'status', 'payment_status']))
                                <a href="{{ route('admin.orders.index') }}" class="mt-4 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200">Reset Filter</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">{{ $orders->links() }}</div>
