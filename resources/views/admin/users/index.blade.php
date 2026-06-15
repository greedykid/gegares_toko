@extends('layouts.admin')
@section('page_title', 'Kelola Pengguna')
@section('content')
@php
    $sort = request('sort', 'created_at');
    $dir = request('direction', 'desc');
    
    if (!function_exists('sortUrl')) {
        function sortUrl($column, $currentSort, $currentDir) {
            $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
        }
    }
@endphp

<div x-data="{ showModal: false, editMode: false, form: { id:null, name:'', email:'', role:'user', password:'' } }">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Pengguna</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($totalUsers) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-purple-500 dark:text-purple-400 uppercase tracking-widest transition-colors">Admin</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($totalAdmin) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.968-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest transition-colors">Pelanggan</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($totalCustomer) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest transition-colors">Baru Bulan Ini</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($newUsersThisMonth) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end mb-6">
        <button @click="showModal=true; editMode=false; form={id:null,name:'',email:'',role:'user',password:''}"
                class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 shadow-sm transition-all duration-200">+ Tambah Pengguna</button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-all duration-300">
        <div class="overflow-x-auto lg:overflow-x-visible custom-scrollbar">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tl-2xl">
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
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-linear-to-br {{ $user->isAdmin() ? 'from-purple-400 to-purple-600' : 'from-primary-400 to-primary-600' }} flex items-center justify-center text-white text-xs font-bold shadow-xs shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $user->isAdmin() ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200/50 dark:border-purple-950/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-slate-400 dark:text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
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
                        <td colspan="5" class="px-6 py-20 text-center">
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
        <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800/60 bg-white dark:bg-slate-900 transition-colors">{{ $users->links() }}</div>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 shadow-2xl shadow-black/50" style="display:none;">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showModal=false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 border border-slate-200 dark:border-slate-800 transition-all duration-300" x-transition>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 transition-colors" x-text="editMode ? 'Edit Pengguna' : 'Tambah Pengguna'"></h3>
            <form :action="editMode ? '{{ url('admin/users') }}/'+form.id : '{{ route('admin.users.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Nama</label><input type="text" name="name" x-model="form.name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all"></div>
                <div><label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Email</label><input type="email" name="email" x-model="form.email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all"></div>
                <div><label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Password <span x-show="editMode" class="text-slate-400 dark:text-slate-500 transition-colors">(kosongkan jika tidak diubah)</span></label><input type="password" name="password" x-model="form.password" :required="!editMode" minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all"></div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Role</label>
                    <div x-data="{ 
                            open: false,
                            get selectedLabel() {
                                return String(form.role) === 'admin' ? 'Admin' : 'User';
                            }
                         }" 
                         class="relative">
                        <input type="hidden" name="role" x-model="form.role">
                        <button @click="open = !open" type="button" 
                                class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
                            <span x-text="selectedLabel"></span>
                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open" 
                             @click.outside="open = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                             style="display: none;">
                            <button type="button" @click="form.role = 'user'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="String(form.role) === 'user' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">User</button>
                            <button type="button" @click="form.role = 'admin'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="String(form.role) === 'admin' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Admin</button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal=false" class="px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 shadow-sm transition-all duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
