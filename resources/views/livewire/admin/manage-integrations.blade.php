@php
    $meta = [
        'google'   => ['Login Google', 'Kredensial OAuth dari Google Cloud Console untuk tombol "Masuk dengan Google".'],
        'pakasir'  => ['Pembayaran — Pakasir', 'Dipakai untuk membuat link QRIS dan memverifikasi pembayaran.'],
        'biteship' => ['Pengiriman — Biteship', 'Dipakai untuk menghitung ongkir dan memesan kurir.'],
        'ai'       => ['AI Chatbot', 'Endpoint dan kunci model bahasa untuk chatbot toko.'],
        'mail'     => ['Email (SMTP)', 'Server pengirim untuk email pesanan, pembayaran, dan reset kata sandi.'],
    ];
@endphp

<div class="space-y-6 sm:space-y-8">
    {{-- The admin is putting live payment and mail credentials into a database;
         say so plainly rather than letting it look like an ordinary settings form. --}}
    <div class="rounded-2xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/20 p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="text-sm text-amber-800 dark:text-amber-300 leading-relaxed">
                <p class="font-bold">Kunci di sini menggantikan file <code class="font-mono text-xs">.env</code></p>
                <p class="mt-1 text-amber-700/90 dark:text-amber-400/90">
                    Nilai disimpan terenkripsi di database. Kolom rahasia tidak pernah ditampilkan kembali —
                    biarkan kosong untuk mempertahankan yang tersimpan. Kosongkan lewat tombol hapus agar nilai
                    dari <code class="font-mono text-xs">.env</code> dipakai lagi.
                </p>
                <p class="mt-2 font-semibold text-amber-800 dark:text-amber-300">
                    Siapa pun yang bisa masuk sebagai admin dapat mengubah kunci ini. Jaga akun admin Anda.
                </p>
            </div>
        </div>
    </div>

    @foreach ($groups as $group => $fields)
        <div class="bg-white dark:bg-slate-900/60 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 sm:p-6">
            <div class="mb-5">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ $meta[$group][0] }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $meta[$group][1] }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($fields as $key => [$path, $label, $secret])
                    <div class="{{ $key === 'google_redirect' || $key === 'ai_base_url' ? 'md:col-span-2' : '' }}">
                        <label for="int-{{ $key }}" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            {{ $label }}
                            @if ($secret)
                                <span class="ml-1 normal-case tracking-normal font-semibold text-amber-600 dark:text-amber-500">· rahasia</span>
                            @endif
                        </label>

                        @if ($key === 'mail_mailer')
                            <select id="int-{{ $key }}" wire:model="form.{{ $key }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                <option value="smtp">smtp — kirim sungguhan</option>
                                <option value="log">log — tulis ke file log (uji coba)</option>
                                <option value="array">array — tidak dikirim</option>
                                <option value="sendmail">sendmail</option>
                            </select>
                        @elseif ($key === 'mail_scheme')
                            <select id="int-{{ $key }}" wire:model="form.{{ $key }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                <option value="">STARTTLS (umumnya port 587)</option>
                                <option value="smtps">SSL/TLS (umumnya port 465)</option>
                            </select>
                        @else
                            <input id="int-{{ $key }}"
                                type="{{ $secret ? 'password' : 'text' }}"
                                wire:model="form.{{ $key }}"
                                autocomplete="{{ $secret ? 'new-password' : 'off' }}"
                                placeholder="{{ $secret ? ($stored[$key] ?? false ? 'Tersimpan — isi untuk mengganti' : 'Belum diatur') : '' }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                        @endif

                        @error('form.'.$key)
                            <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                        @enderror

                        @if ($secret && ($stored[$key] ?? false))
                            <div class="mt-1.5 flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersimpan
                                </span>
                                <button type="button" wire:click="clearCredential('{{ $key }}')"
                                    wire:confirm="Hapus kredensial ini? Nilai dari file .env akan dipakai kembali."
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-red-500 transition-colors">
                                    Hapus
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="flex justify-end">
        <button type="button" wire:click="save" wire:loading.attr="disabled"
            class="px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white text-sm font-bold transition-colors">
            <span wire:loading.remove wire:target="save">Simpan Pengaturan Integrasi</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>
    </div>
</div>
