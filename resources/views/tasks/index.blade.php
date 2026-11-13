<x-app-layout>
    <div class="max-w-[960px] mx-auto px-6 py-8">
        {{-- Flash message --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Judul halaman dan keterangan --}}
        <div class="mb-6">
            <h1 class="text-[28px] font-bold text-gray-900 leading-tight">Tugas</h1>
            <p class="text-[15px] text-gray-600 mt-1">
                @if (Auth::user()->isAdmin())
                    Menampilkan semua tugas tim.
                @else
                    Menampilkan tugas yang kamu buat atau ditugaskan kepadamu.
                @endif
            </p>
        </div>

        {{-- Tab filter status --}}
        <div class="flex items-center gap-2 mb-6 overflow-x-auto">
            <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
               class="inline-flex items-center justify-center px-4 h-11 text-[15px] font-medium rounded-lg transition-colors {{ empty($status) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                Semua
            </a>
            <a href="{{ Route::has('tasks.index') ? route('tasks.index', ['status' => 'pending']) : url('/tasks?status=pending') }}"
               class="inline-flex items-center justify-center px-4 h-11 text-[15px] font-medium rounded-lg transition-colors {{ $status === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                Belum selesai
            </a>
            <a href="{{ Route::has('tasks.index') ? route('tasks.index', ['status' => 'done']) : url('/tasks?status=done') }}"
               class="inline-flex items-center justify-center px-4 h-11 text-[15px] font-medium rounded-lg transition-colors {{ $status === 'done' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                Selesai
            </a>
        </div>

        {{-- Daftar kartu tugas --}}
        <div class="space-y-3">
            @forelse ($tasks as $task)
                <div class="rounded-[12px] p-5 border transition-colors {{ $task->is_done ? 'bg-gray-50 border-gray-200' : 'bg-white border-gray-200' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        {{-- Checkbox status --}}
                        <div class="shrink-0 pt-0.5">
                            <input type="checkbox"
                                   @checked($task->is_done)
                                   class="w-[22px] h-[22px] rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer"
                                   aria-label="Tandai {{ $task->title }} sebagai selesai"
                                   disabled>
                        </div>

                        {{-- Konten tugas --}}
                        <div class="flex-1 min-w-0">
                            {{-- Judul dan Pill Selesai --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[17px] font-semibold {{ $task->is_done ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                    {{ $task->title }}
                                </h3>
                                @if ($task->is_done)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[12px] font-bold bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                @endif
                            </div>

                            {{-- Deskripsi (opsional) --}}
                            @if ($task->description)
                                <p class="text-[15px] text-gray-600 mt-1 {{ $task->is_done ? 'text-gray-400 line-through' : '' }}">
                                    {{ $task->description }}
                                </p>
                            @endif

                            {{-- Baris meta pembuat dan tanggal --}}
                            @if (Auth::user()->isAdmin() || Auth::id() !== $task->created_by)
                                <div class="text-[13px] text-gray-500 mt-1">
                                    Dibuat oleh {{ $task->creator->name ?? 'Pengguna Tidak Dikenal' }} &middot; {{ $task->created_at ? $task->created_at->format('d M Y') : '' }}
                                </div>
                            @endif

                            {{-- Baris penanggung jawab --}}
                            <div class="flex items-center flex-wrap gap-2 mt-3 text-[13px]">
                                <span class="text-gray-600 font-medium">Penanggung jawab:</span>
                                @forelse ($task->assignees as $assignee)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[13px] font-medium bg-indigo-50 text-indigo-800">
                                        <span>{{ $assignee->name }}</span>
                                        @can('manageAssignees', $task)
                                            <button type="button"
                                                    class="text-indigo-600 hover:text-indigo-900 focus:outline-none"
                                                    aria-label="Hapus {{ $assignee->name }} dari penanggung jawab">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endcan
                                    </span>
                                @empty
                                    <span class="text-gray-500">belum ada</span>
                                @endforelse

                                @can('manageAssignees', $task)
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-[13px] font-medium border border-dashed border-gray-300 text-gray-600 hover:text-gray-900 hover:border-gray-400">
                                        + Tambah
                                    </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                            @can('update', $task)
                                <x-secondary-button type="button" class="h-9 px-3 text-xs">
                                    Edit
                                </x-secondary-button>
                            @endcan

                            @can('delete', $task)
                                <button type="button"
                                        class="inline-flex items-center px-3 h-9 border border-red-200 rounded-lg text-xs font-semibold text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Hapus
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-[12px] p-8 text-center text-gray-500">
                    @if ($status)
                        Tidak ada tugas pada filter ini.
                    @else
                        Belum ada tugas. Buat yang pertama di atas.
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
