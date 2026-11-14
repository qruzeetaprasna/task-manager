<nav class="bg-white border-b border-gray-200">
    <div class="max-w-[960px] mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            {{-- Kiri: Logo + Tautan navigasi --}}
            <div class="flex items-center gap-4 sm:gap-8">
                <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
                   class="text-indigo-600 font-bold text-xl tracking-tight shrink-0">
                    TugasKu
                </a>

                <div class="flex items-center h-16">
                    <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
                       @class([
                           'inline-flex items-center h-full px-1 text-xs font-medium mr-3 sm:mr-6 sm:text-sm',
                           'text-gray-900 border-b-2 border-indigo-600' => request()->routeIs('tasks.*'),
                           'text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors' => ! request()->routeIs('tasks.*'),
                       ])>
                        Tugas
                    </a>
                    @if (Auth::user()?->isAdmin())
                        <a href="{{ route('admin.users.index') }}"
                           @class([
                               'inline-flex items-center h-full px-1 text-xs font-medium sm:text-sm',
                               'text-gray-900 border-b-2 border-indigo-600' => request()->routeIs('admin.users.*'),
                               'text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors' => ! request()->routeIs('admin.users.*'),
                           ])>
                            Kelola Pengguna
                        </a>
                    @endif
                </div>
            </div>

            {{-- Kanan: Badge, Nama, dan menu akun --}}
            <div class="flex items-center gap-2 sm:gap-3">
                @if (Auth::user()?->isAdmin())
                    <span class="px-2.5 py-0.5 bg-violet-100 text-violet-800 text-xs font-bold rounded-full tracking-wider uppercase">
                        Admin
                    </span>
                @endif

                <span class="hidden text-sm font-medium text-gray-700 sm:inline">{{ Auth::user()?->name }}</span>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            type="button"
                            aria-label="Buka menu akun"
                            aria-haspopup="true"
                            x-bind:aria-expanded="open"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-gray-100 px-4 py-2">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()?->name }}</p>
                            <p class="break-all text-xs text-gray-500">{{ Auth::user()?->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                            >
                                Keluar
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

        </div>
    </div>
</nav>
