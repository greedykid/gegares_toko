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
    {{-- Page header --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Kelola Pengguna</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola akun admin dan pelanggan toko Anda.</p>
        </div>
        <button @click="showModal=true; editMode=false; form={id:null,name:'',email:'',role:'user',password:''}"
                class="inline-flex items-center gap-2 h-10 px-5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Pengguna
        </button>
    </div>

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

    @php
        $roleTab = request('role', '');
        $roleTabs = ['' => 'Semua', 'admin' => 'Admin', 'user' => 'Pelanggan'];
    @endphp

    <div x-data="adminListView('users')" :class="grid ? 'admin-grid-view' : ''" class="admin-list-card transition-all duration-300">
        <div class="flex flex-col-reverse gap-3 pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <form method="GET" action="{{ route('admin.users.index') }}" class="relative flex flex-1 items-center gap-2 sm:flex-none" x-data="{ loading: false }">
                    <input type="hidden" name="role" value="{{ request('role') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                    <div class="relative flex-1 min-w-0 sm:flex-none">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <input type="text" name="search" data-live-search data-target="#usersTable" value="{{ request('search') }}" placeholder="Cari nama / email..." autocomplete="off"
                               class="w-full sm:w-72 h-10 pl-10 pr-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                    </div>
                </form>
                <div class="shrink-0">
                    @include('admin.partials.view-toggle')
                </div>
            </div>
            <div class="inline-flex items-center h-10 p-1 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 max-w-full overflow-x-auto scrollbar-none shrink-0" role="group" aria-label="Filter peran">
                @foreach($roleTabs as $val => $label)
                    <a href="{{ request()->fullUrlWithQuery(['role' => $val, 'page' => null]) }}"
                       class="inline-flex items-center justify-center h-full px-3.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ (string) $roleTab === (string) $val ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div id="usersTable">
            @include('admin.users._table')
        </div>
        @include('admin.partials.bulk-bar', ['route' => route('admin.users.bulk-destroy'), 'noun' => 'pengguna'])
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 shadow-2xl shadow-black/50" style="display:none;">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showModal=false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 border border-slate-200 dark:border-slate-800 transition-all duration-300" x-transition>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 transition-colors" x-text="editMode ? 'Edit Pengguna' : 'Tambah Pengguna'"></h3>
            {{-- The update URL is derived from the named route so it uses the
                 Indonesian resource path (/admin/pengguna/{id}); hardcoding
                 /admin/users/ here previously 404'd on save. --}}
            <form :action="editMode ? '{{ route('admin.users.update', ['user' => '__ID__']) }}'.replace('__ID__', form.id) : '{{ route('admin.users.store') }}'" method="POST" class="space-y-4">
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
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
