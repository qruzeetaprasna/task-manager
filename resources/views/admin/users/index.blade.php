<x-app-layout>
    @php
        $pageAdminCount = $users->getCollection()->where('role', 'admin')->count();
        $pageMemberCount = $users->getCollection()->where('role', 'member')->count();
        $showCreateUserModal = $errors->has('name')
            || $errors->has('email')
            || $errors->has('password')
            || ($errors->has('role') && old('name') !== null && old('email') !== null);
    @endphp

    <div class="mx-auto max-w-[960px] overflow-x-hidden px-6 py-8">
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-[28px] font-bold leading-tight text-gray-900">Kelola Pengguna</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Manajemen akun anggota tim dan penetapan peran pengguna.
                </p>
            </div>

            <x-primary-button
                type="button"
                x-data=""
                x-on:click="$dispatch('open-modal', 'create-user-modal')"
                class="min-h-11 rounded-lg bg-indigo-600 px-5 text-sm normal-case hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800"
            >
                <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" />
                </svg>
                Tambah User
            </x-primary-button>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Ringkasan pengguna">
            <article class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total pengguna</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $users->total() }} Pengguna</p>
                <p class="mt-1 text-xs text-gray-600">Terdaftar dalam organisasi</p>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Administrator</p>
                <p class="mt-1 text-2xl font-bold text-violet-800">{{ $pageAdminCount }} Admin</p>
                <p class="mt-1 text-xs text-gray-600">Pada halaman ini</p>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Member</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $pageMemberCount }} Member</p>
                <p class="mt-1 text-xs text-gray-600">Pada halaman ini</p>
            </article>
        </section>

        <section
            x-data="{
                search: '',
                role: 'all',
                get visibleCount() {
                    return [...this.$root.querySelectorAll('[data-user-row]')]
                        .filter(row => this.matchesUser(row.dataset.name, row.dataset.email, row.dataset.role))
                        .length;
                },
                matchesUser(name, email, userRole) {
                    const query = this.search.trim().toLocaleLowerCase();
                    const matchesSearch = name.toLocaleLowerCase().includes(query)
                        || email.toLocaleLowerCase().includes(query);

                    return matchesSearch && (this.role === 'all' || this.role === userRole);
                }
            }"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white"
            aria-label="Daftar pengguna"
        >
            <div class="flex flex-col gap-3 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="relative block w-full sm:max-w-sm">
                    <span class="sr-only">Cari nama atau email pada halaman ini</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-width="2" d="m20 20-4-4"></path>
                    </svg>
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Cari nama atau email..."
                        class="h-11 w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </label>

                <div class="flex gap-2 overflow-x-auto" role="group" aria-label="Filter peran pengguna">
                    <button
                        type="button"
                        @click="role = 'all'"
                        :aria-pressed="role === 'all'"
                        :class="role === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Semua ({{ $users->total() }})
                    </button>
                    <button
                        type="button"
                        @click="role = 'admin'"
                        :aria-pressed="role === 'admin'"
                        :class="role === 'admin' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Admin ({{ $pageAdminCount }})
                    </button>
                    <button
                        type="button"
                        @click="role = 'member'"
                        :aria-pressed="role === 'member'"
                        :class="role === 'member' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Member ({{ $pageMemberCount }})
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-left sm:min-w-[640px]">
                    <thead class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th scope="col" class="px-4 py-3">Pengguna</th>
                            <th scope="col" class="px-4 py-3">Role</th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr
                                data-user-row
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                x-show="matchesUser($el.dataset.name, $el.dataset.email, $el.dataset.role)"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <th scope="row" class="px-4 py-3 font-normal">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-700" aria-hidden="true">
                                            {{ collect(explode(' ', $user->name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->join('') }}
                                        </span>
                                        <span class="min-w-0">
                                            <span class="flex flex-wrap items-center gap-2 text-sm font-semibold text-gray-900">
                                                {{ $user->name }}
                                                @if (Auth::id() === $user->id)
                                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">Akun kamu</span>
                                                @endif
                                            </span>
                                            <span class="mt-0.5 block break-all text-xs text-gray-600">{{ $user->email }}</span>
                                        </span>
                                    </div>
                                </th>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->role === 'admin' ? 'bg-violet-100 text-violet-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $user->role === 'admin' ? 'Admin' : 'Member' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="flex items-center justify-end gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="sr-only" for="user-role-{{ $user->id }}">Role {{ $user->name }}</label>
                                        <select
                                            id="user-role-{{ $user->id }}"
                                            name="role"
                                            class="h-10 rounded-lg border border-gray-300 bg-white px-2 text-xs text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        >
                                            <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                            <option value="member" @selected($user->role === 'member')>Member</option>
                                        </select>
                                        <button
                                            type="submit"
                                            class="min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            Simpan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center text-sm text-gray-600">
                                    Belum ada pengguna.
                                </td>
                            </tr>
                        @endforelse
                        <tr x-show="visibleCount === 0 && {{ $users->count() > 0 ? 'true' : 'false' }}">
                            <td colspan="3" class="px-4 py-12 text-center text-sm text-gray-600">
                                Tidak ada pengguna yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-600">
                    Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
                </p>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </section>
    </div>

    <x-modal name="create-user-modal" :show="$showCreateUserModal" focusable>
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6">
            @csrf

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Tambah User Baru</h2>
                    <p class="mt-1 text-sm text-gray-600">Buat akun baru dan tentukan perannya dalam tim.</p>
                </div>
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    aria-label="Tutup form tambah user"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="new-user-name" value="Nama" />
                    <input
                        id="new-user-name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        maxlength="255"
                        autocomplete="name"
                        required
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @class([
                            'mt-1 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has('name'),
                            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('name'),
                        ])
                    >
                    @error('name')
                        <p class="mt-1 text-[13px] text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="new-user-email" value="Email" />
                    <input
                        id="new-user-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @class([
                            'mt-1 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has('email'),
                            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('email'),
                        ])
                    >
                    @error('email')
                        <p class="mt-1 text-[13px] text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="new-user-password" value="Password" />
                    <input
                        id="new-user-password"
                        name="password"
                        type="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        @class([
                            'mt-1 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has('password'),
                            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('password'),
                        ])
                    >
                    @error('password')
                        <p class="mt-1 text-[13px] text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="new-user-role" value="Role" />
                    <select
                        id="new-user-role"
                        name="role"
                        required
                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                        @class([
                            'mt-1 h-11 w-full rounded-lg border bg-white px-3 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has('role'),
                            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' => ! $errors->has('role'),
                        ])
                    >
                        <option value="member" @selected(old('role', 'member') === 'member')>Member</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-[13px] text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>
                <x-primary-button class="min-h-11 rounded-lg bg-indigo-600 px-5 text-sm normal-case hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800">
                    Tambah User
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
