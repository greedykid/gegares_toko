@php
    $sort = $sort ?? request('sort', 'created_at');
    $dir = $dir ?? request('direction', 'desc');
    $pageIds = $pageIds ?? $users->pluck('id')->values();
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
                        <th class="admin-select-cell px-4 py-4 w-10 rounded-tl-2xl">
                            <input type="checkbox" @change="toggleAll(@json($pageIds))" :checked="allSelected(@json($pageIds))"
                                   class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('name', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Nama
                                @if($sort === 'name')
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
                            <a href="{{ sortUrl('email', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Email
                                @if($sort === 'email')
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
                            <a href="{{ sortUrl('role', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                                Role
                                @if($sort === 'role')
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
                                Bergabung
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
                <tbody class="divide-y divide-slate-100/65 dark:divide-slate-800/60">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors"
                        :class="isSelected({{ $user->id }}) && 'bg-primary-50/50 dark:bg-primary-950/20'">
                        <td class="admin-select-cell px-4 py-4 w-10">
                            <input type="checkbox" @change="toggle({{ $user->id }})" :checked="isSelected({{ $user->id }})"
                                   class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                        </td>
                        <td data-label="Nama" class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-linear-to-br {{ $user->isAdmin() ? 'from-purple-400 to-purple-600' : 'from-primary-400 to-primary-600' }} flex items-center justify-center text-white text-xs font-bold shadow-xs shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td data-label="Email" class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</td>
                        <td data-label="Role" class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $user->isAdmin() ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200/50 dark:border-purple-950/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td data-label="Bergabung" class="px-6 py-4 text-sm text-right text-slate-400 dark:text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td data-label="Aksi" class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <div class="relative group/tooltip inline-flex">
                                <button @click="showModal=true; editMode=true; form={id:{{ $user->id }},name:'{{ addslashes($user->name) }}',email:'{{ $user->email }}',role:'{{ $user->role }}',password:''}"
                                        class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-750 hover:bg-primary-50 dark:hover:bg-primary-950/60 rounded-xl transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Edit Pengguna
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            @if($user->id !== auth()->id())
                                <div class="relative group/tooltip inline-flex">
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-55 dark:hover:bg-red-950/60 rounded-xl transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                        Hapus Pengguna
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 transition-colors">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Pengguna Tidak Ditemukan</h3>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 transition-colors">{{ $users->links() }}</div>
