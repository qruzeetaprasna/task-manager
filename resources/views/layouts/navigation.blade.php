<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[960px] mx-auto px-6">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo TugasKu -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}" class="text-[20px] font-bold text-indigo-600 tracking-tight">
                        TugasKu
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-8 sm:flex h-full">
                    <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
                       class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-600 text-[15px] font-medium text-gray-900">
                        Tugas
                    </a>
                </div>
            </div>

            <!-- Right Side: Badge Admin, User Name, Keluar -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                @if (Auth::user()?->isAdmin())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[12px] font-bold bg-violet-100 text-violet-800">
                        Admin
                    </span>
                @endif

                <span class="text-[15px] font-medium text-gray-700">
                    {{ Auth::user()?->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-[15px] font-medium text-gray-500 hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                        Keluar
                    </button>
                </form>
            </div>

            <!-- Hamburger for Mobile -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
               class="block py-2 text-base font-medium text-indigo-600">
                Tugas
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200 px-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="font-medium text-base text-gray-800">{{ Auth::user()?->name }}</span>
                @if (Auth::user()?->isAdmin())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-violet-100 text-violet-800">
                        Admin
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left py-2 text-base font-medium text-gray-600 hover:text-gray-900">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>
