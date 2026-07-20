<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                {{ __('Lobby Synapse Forum') }}
            </h2>
            <div class="flex gap-2">
                <!-- TOMBOL PANDUAN -->
                <a href="{{ route('panduan') }}" class="btn btn-outline btn-info btn-sm shadow-lg font-bold tracking-wider hover:scale-105 transition-transform">📖 Panduan</a>
                <button onclick="my_modal_create.showModal()" class="btn btn-primary btn-sm shadow-lg text-white font-bold tracking-wider hover:scale-105 transition-transform">+ Buat Ruangan Baru</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-base-300 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- ============================================== -->
            <!-- BANNER STATISTIK (Biar Kelihatan Keren saat UAS)-->
            <!-- ============================================== -->
            <div class="stats shadow w-full bg-[#1d232a] border border-gray-700 mb-8">
                <div class="stat place-items-center">
                    <div class="stat-title text-gray-400">Total Pengguna Aktif</div>
                    <div class="stat-value text-primary">{{ $stats['total_users'] }}</div>
                    <div class="stat-desc">Akun Terdaftar</div>
                </div>
                
                <div class="stat place-items-center">
                    <div class="stat-title text-gray-400">Forum Tercipta</div>
                    <div class="stat-value text-secondary">{{ $stats['total_rooms'] }}</div>
                    <div class="stat-desc">Debat & Diskusi</div>
                </div>
                
                <div class="stat place-items-center">
                    <div class="stat-title text-gray-400">Total Argumen AI</div>
                    <div class="stat-value text-accent">{{ $stats['total_args'] }}</div>
                    <div class="stat-desc">Pemikiran Dihasilkan</div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SECTION 1: RUANGAN BUATAN SAYA                 -->
            <!-- ============================================== -->
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2 border-b border-gray-700 pb-2">
                <span class="text-warning text-xl">👑</span>
                Ruangan Buatan Saya (Sbg Prompter)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($myRooms as $room)
                    <div class="card bg-base-100 shadow-xl border-t-4 {{ $room->mode === 'discussion' ? 'border-accent' : 'border-primary' }} hover:shadow-2xl hover:-translate-y-1 transition-all">
                        <div class="card-body">
                            <h2 class="card-title text-lg text-gray-200 font-extrabold leading-snug">{{ $room->topic }}</h2>
                            <p class="text-sm mt-2">
                                Mode: <strong class="{{ $room->mode === 'discussion' ? 'text-accent' : 'text-primary' }}">{{ $room->mode === 'discussion' ? '🤝 Diskusi' : '⚔️ Debat' }}</strong>
                            </p>
                            <p class="text-sm text-gray-500">Batas AI: <span class="font-bold">{{ $room->max_rounds }} Ronde</span></p>
                            
                            <div class="card-actions flex flex-row items-center justify-between mt-4 border-t border-gray-800 pt-4 gap-2">
                                <form action="{{ route('arena.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ruangan ini permanen?');" class="w-1/4">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-error btn-outline btn-sm w-full" title="Hapus Ruangan">🗑️</button>
                                </form>
                                <a href="{{ route('arena.show', $room->id) }}" class="btn btn-primary btn-sm text-white flex-1 shadow-lg">Kelola Ruangan</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500 py-6 bg-base-100 rounded-xl shadow-sm border border-gray-700 border-dashed">
                        <p class="text-sm">Anda belum membuat ruangan apa pun.</p>
                    </div>
                @endforelse
            </div>

            <!-- ============================================== -->
            <!-- SECTION 2: RUANGAN PERAN LAIN                  -->
            <!-- ============================================== -->
            <h3 class="text-lg font-bold text-gray-400 mt-12 mb-4 flex items-center gap-2 border-b border-gray-700 pb-2">
                <span class="text-gray-400 text-xl">🌐</span>
                Forum Diskusi & Debat Lainnya
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($otherRooms as $room)
                    <div class="card bg-[#1d232a] shadow-md border-t-4 border-gray-600 opacity-90 hover:opacity-100 transition-all">
                        <div class="card-body">
                            <h2 class="card-title text-base text-gray-300 font-bold leading-snug line-clamp-2">{{ $room->topic }}</h2>
                            <p class="text-xs mt-1 text-gray-400">
                                Mode: {{ $room->mode === 'discussion' ? '🤝 Diskusi' : '⚔️ Debat' }}
                            </p>
                            
                            <div class="card-actions justify-end mt-4 border-t border-gray-700 pt-4">
                                <a href="{{ route('arena.show', $room->id) }}" class="btn btn-outline btn-secondary btn-sm text-white w-full">Kunjungi Forum</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-600 py-6 text-sm italic">
                        Belum ada forum buatan pengguna lain.
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <!-- MODAL POP-UP -->
    <dialog id="my_modal_create" class="modal">
        <div class="modal-box bg-base-100 border border-gray-700 shadow-2xl">
            <h3 class="font-extrabold text-xl mb-6 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">Mulai Topik Baru</h3>
            <form action="{{ route('arena.store') }}" method="POST">
                @csrf
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text text-gray-400 font-bold uppercase tracking-wider text-xs">Topik Pembahasan</span></label>
                    <input type="text" name="topic" placeholder="Contoh: Apakah AI menggantikan programmer?" class="input input-bordered input-primary w-full text-gray-900 bg-white placeholder-gray-400" required />
                </div>
                
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text text-gray-400 font-bold uppercase tracking-wider text-xs">Mode AI</span></label>
                    <select name="mode" class="select select-bordered select-primary w-full text-gray-900 bg-white" required>
                        <option value="debate">⚔️ Mode Debat (Pro vs Kontra)</option>
                        <option value="discussion">🤝 Mode Diskusi (Saling Melengkapi Ide)</option>
                    </select>
                </div>

                <div class="form-control w-full mb-6">
                    <label class="label"><span class="label-text text-gray-400 font-bold uppercase tracking-wider text-xs">Jumlah Ronde</span></label>
                    <select name="max_rounds" class="select select-bordered select-primary w-full text-gray-900 bg-white" required>
                        <option value="1">1 Ronde (Sangat Singkat: 2 Argumen)</option>
                        <option value="2">2 Ronde (Singkat: 4 Argumen)</option>
                        <option value="3" selected>3 Ronde (Normal: 6 Argumen)</option>
                        <option value="5">5 Ronde (Sengit: 10 Argumen)</option>
                    </select>
                </div>

                <div class="modal-action border-t border-gray-800 pt-4">
                    <button type="button" class="btn btn-ghost" onclick="my_modal_create.close()">Batal</button>
                    <button type="submit" class="btn btn-primary text-white shadow-lg">Simpan Ruangan</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
</x-app-layout>