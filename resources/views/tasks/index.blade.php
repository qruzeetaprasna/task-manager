<x-app-layout>
    <div class="max-w-[960px] mx-auto px-6 py-8">

        {{-- ======================================================= --}}
        {{-- Toast sukses (session flash)                             --}}
        {{-- ======================================================= --}}
        @if (session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="fixed top-5 right-5 z-50 flex items-center gap-3 bg-white border border-gray-200 rounded-xl shadow-lg px-4 py-3 text-sm text-gray-700 max-w-xs">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="flex-1">{{ session('success') }}</span>
                <button @click="show = false"
                        class="text-gray-400 hover:text-gray-600 ml-1 shrink-0"
                        aria-label="Tutup notifikasi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- ======================================================= --}}
        {{-- Header: Judul + Subtitle (kiri) | Statistik (kanan)     --}}
        {{-- ======================================================= --}}
        @php
            $runningCount = $tasks->where('is_done', false)->count();
            $doneCount    = $tasks->where('is_done', true)->count();
            $totalCount   = $tasks->count();
        @endphp

        <div class="flex items-start justify-between mb-5">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 leading-tight">Tugas</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if (Auth::user()->isAdmin())
                        Menampilkan semua tugas tim.
                    @else
                        Menampilkan tugas yang kamu buat atau ditugaskan kepadamu.
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-500 pt-1">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-indigo-500"></span>
                    {{ $runningCount }} Berjalan
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                    {{ $doneCount }} Selesai
                </span>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- Tab filter status                                        --}}
        {{-- ======================================================= --}}
        <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-0.5">
            {{-- Semua --}}
            <a href="{{ Route::has('tasks.index') ? route('tasks.index') : url('/tasks') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ empty($status)
                          ? 'bg-indigo-600 text-white shadow-sm'
                          : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                Semua
                <span class="text-xs px-1.5 py-0.5 rounded font-semibold leading-none
                             {{ empty($status) ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                    {{ $totalCount }}
                </span>
            </a>

            {{-- Belum selesai --}}
            <a href="{{ Route::has('tasks.index') ? route('tasks.index', ['status' => 'pending']) : url('/tasks?status=pending') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ $status === 'pending'
                          ? 'bg-indigo-600 text-white shadow-sm'
                          : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                Belum selesai
                <span class="text-xs px-1.5 py-0.5 rounded font-semibold leading-none
                             {{ $status === 'pending' ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                    {{ $runningCount }}
                </span>
            </a>

            {{-- Selesai --}}
            <a href="{{ Route::has('tasks.index') ? route('tasks.index', ['status' => 'done']) : url('/tasks?status=done') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ $status === 'done'
                          ? 'bg-indigo-600 text-white shadow-sm'
                          : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                Selesai
                <span class="text-xs px-1.5 py-0.5 rounded font-semibold leading-none
                             {{ $status === 'done' ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                    {{ $doneCount }}
                </span>
            </a>
        </div>

        {{-- ======================================================= --}}
        {{-- Form tambah tugas (visual statis — fungsionalitas di F4) --}}
        {{-- ======================================================= --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm">
            {{-- Judul + counter --}}
            <div class="flex items-center justify-between mb-2">
                <label for="title-input" class="text-sm font-medium text-gray-700">Judul tugas</label>
                <span class="text-xs text-gray-400">0 / 100</span>
            </div>
            <input id="title-input"
                   type="text"
                   placeholder="Tuliskan judul tugas yang jelas..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mb-4"
                   disabled>

            {{-- Deskripsi --}}
            <label for="desc-input" class="block text-sm font-medium text-gray-700 mb-2">
                Deskripsi (opsional)
            </label>
            <textarea id="desc-input"
                      rows="3"
                      placeholder="Tambahkan detail atau instruksi tugas jika diperlukan..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400
                             focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none mb-4"
                      disabled></textarea>

            {{-- Tombol submit (kanan) --}}
            <div class="flex justify-end">
                <button type="button" disabled
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold
                               rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                               focus:ring-offset-2 disabled:opacity-60 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah tugas
                </button>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- Daftar kartu tugas                                       --}}
        {{-- ======================================================= --}}
        <div class="space-y-3">
            @forelse ($tasks as $task)
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start gap-4">

                        {{-- Checkbox --}}
                        <div class="shrink-0 mt-0.5">
                            <input type="checkbox"
                                   @checked($task->is_done)
                                   class="w-5 h-5 rounded border-gray-300 text-indigo-600 accent-indigo-600 cursor-not-allowed"
                                   aria-label="Status tugas: {{ $task->title }}"
                                   disabled>
                        </div>

                        {{-- Konten utama --}}
                        <div class="flex-1 min-w-0">

                            {{-- Judul + pill Selesai --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[15px] font-semibold leading-snug
                                           {{ $task->is_done ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                    {{ $task->title }}
                                </h3>
                                @if ($task->is_done)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Selesai
                                    </span>
                                @endif
                            </div>

                            {{-- Meta: pembuat & tanggal --}}
                            @if (Auth::user()->isAdmin() || Auth::id() !== $task->created_by)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Dibuat oleh {{ $task->creator->name ?? 'Pengguna Tidak Dikenal' }}
                                    &middot;
                                    {{ $task->created_at ? $task->created_at->translatedFormat('d M Y') : '' }}
                                </p>
                            @endif

                            {{-- Penanggung jawab --}}
                            <div class="flex items-center gap-2 mt-2 flex-wrap text-sm">
                                <span class="text-xs text-gray-500 font-medium shrink-0">Penanggung jawab:</span>

                                @if ($task->assignees->isEmpty())
                                    <span class="text-xs text-gray-400 italic">belum ada</span>
                                @else
                                    @foreach ($task->assignees as $assignee)
                                        <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full">
                                            {{ $assignee->name }}
                                            @can('manageAssignees', $task)
                                                <button type="button"
                                                        class="ml-0.5 text-indigo-400 hover:text-indigo-700 focus:outline-none leading-none"
                                                        aria-label="Hapus {{ $assignee->name }} dari penanggung jawab">
                                                    &times;
                                                </button>
                                            @endcan
                                        </span>
                                    @endforeach
                                @endif

                                @can('manageAssignees', $task)
                                    <button type="button"
                                            class="inline-flex items-center gap-0.5 text-xs text-indigo-500 hover:text-indigo-700 font-medium focus:outline-none transition-colors">
                                        + Tambah
                                    </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Tombol aksi (kanan) --}}
                        <div class="flex items-center gap-2 shrink-0 self-start mt-0.5">
                            @can('update', $task)
                                <button type="button"
                                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200
                                               rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300
                                               transition-colors">
                                    Edit
                                </button>
                            @endcan

                            @can('delete', $task)
                                <button type="button"
                                        class="px-3 py-1.5 text-xs font-medium text-red-600 bg-white border border-red-200
                                               rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300
                                               transition-colors">
                                    Hapus
                                </button>
                            @endcan
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-xl p-10 text-center text-sm text-gray-400 shadow-sm">
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
