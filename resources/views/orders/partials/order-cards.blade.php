{{--
    One order card per row. Extracted so "Muat Lebih Banyak" can request just
    the next batch and append it, instead of reloading the whole page.

    @param \Illuminate\Contracts\Pagination\Paginator $orders
--}}
            @foreach($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="block bg-white dark:bg-slate-900/60 rounded-4xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 transition-all hover:bg-slate-50/50 dark:hover:bg-slate-900 hover:shadow-lg overflow-hidden group">
                    <div class="px-6 py-5 sm:px-8 border-b border-slate-100 dark:border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 group-hover:text-primary-600 dark:group-hover:text-primary-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $order->order_number }}</h3>
                                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-wider">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 self-start sm:self-auto">
                            @php
                                $statusColors = [
                                    'completed' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50',
                                    'paid' => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-800/50',
                                    'cancelled' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/50',
                                    'expired' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/50',
                                ];
                                $defaultColor = 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50';
                                $statusClass = $statusColors[$order->status] ?? $defaultColor;
                            @endphp
                            <span class="inline-flex px-3 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-xl {{ $statusClass }} shadow-sm">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-6 py-6 sm:px-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="flex items-center gap-4 flex-wrap">
                            @foreach($order->items->take(4) as $item)
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 shadow-sm relative border border-slate-200/50 dark:border-slate-700/50">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600 bg-slate-100 dark:bg-slate-800">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                        </div>
                                    @endif
                                    <div class="absolute bottom-1 right-1 px-1.5 py-0.5 bg-slate-900/80 backdrop-blur-sm text-white text-[9px] font-bold rounded-md border border-white/10">
                                        {{ $item->quantity }}x
                                    </div>
                                </div>
                            @endforeach
                            @if($order->items->count() > 4)
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <span class="text-sm font-black">+{{ $order->items->count() - 4 }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider">Lainnya</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between lg:justify-end gap-6 border-t border-slate-100 dark:border-slate-800 lg:border-t-0 pt-4 lg:pt-0">
                            <div class="lg:text-right">
                                <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Belanja</p>
                                <p class="text-xl sm:text-2xl font-black text-primary-600 dark:text-primary-400">{{ $order->formatted_total }}</p>
                            </div>
                            <div class="shrink-0 lg:hidden">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 transition-transform group-hover:translate-x-1 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 group-hover:text-primary-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
