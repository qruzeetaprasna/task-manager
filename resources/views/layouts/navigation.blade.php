<nav class="bg-white border-b border-gray-200">
    <div class="max-w-[960px] mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            {{-- Kiri: Logo + Tautan navigasi --}}
            <div class="flex items-center gap-8">
                <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
                   class="text-indigo-600 font-bold text-xl tracking-tight shrink-0">
                    TugasKu
                </a>

                <div class="flex items-center h-16">
                    <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
                       class="inline-flex items-center h-full px-1 text-sm font-medium text-gray-900 border-b-2 border-indigo-600 mr-6">
                        Tugas
                    </a>
                    @if (Auth::user()?->isAdmin())
                        <a href="#"
                           class="inline-flex items-center h-full px-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors">
                            Kelola Pengguna
                        </a>
                    @endif
                </div>
            </div>

            {{-- Kanan: Badge, Nama, Avatar, Keluar --}}
            <div class="flex items-center gap-3">
                @if (Auth::user()?->isAdmin())
                    <span class="px-2.5 py-0.5 bg-violet-100 text-violet-800 text-xs font-bold rounded-full tracking-wider uppercase">
                        Admin
                    </span>
                @endif

                <span class="text-sm text-gray-700 font-medium">{{ Auth::user()?->name }}</span>

                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-sm text-gray-500 hover:text-gray-900 font-medium transition-colors focus:outline-none">
                        Keluar
                    </button>
                </form>
            </div>

        </div>
    </div>
</nav>
