<x-app-layout>
    <div class="max-w-[960px] mx-auto px-6 py-8">

        {{-- ======================================================= --}}
        {{-- Toast sukses (session flash / custom event)              --}}
        {{-- ======================================================= --}}
        <div x-data="{
                show: {{ session('success') ? 'true' : 'false' }},
                message: '{{ session('success') ?? '' }}',
                timeout: null,
                init() {
                    const storedMsg = sessionStorage.getItem('toast_success');
                    if (storedMsg) {
                        this.message = storedMsg;
                        this.show = true;
                        sessionStorage.removeItem('toast_success');
                    }
                    if (this.show) {
                        this.startTimer();
                    }
                },
                startTimer() {
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => this.show = false, 5000);
                },
                trigger(msg) {
                    this.message = msg;
                    this.show = true;
                    this.startTimer();
                }
             }"
             @toast.window="trigger($event.detail)"
             x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             class="fixed top-5 right-5 z-50 flex items-center gap-3 bg-white border border-gray-200 rounded-xl shadow-lg px-4 py-3 text-sm text-gray-700 max-w-sm">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="flex-1" x-text="message"></span>
            <button @click="show = false"
                    class="text-gray-400 hover:text-gray-600 ml-1 shrink-0"
                    aria-label="Tutup notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

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
        {{-- Form tambah tugas (Alpine.js)                           --}}
        {{-- ======================================================= --}}
        <form x-data="{
                title: '',
                description: '',
                errors: {},
                submitting: false,
                get charCount() {
                    return this.title.trim().length;
                },
                get counterClass() {
                    if (this.charCount > 100) return 'text-red-600 font-semibold';
                    if (this.charCount >= 90) return 'text-amber-700 font-medium';
                    return 'text-gray-400';
                },
                get isInvalid() {
                    return this.charCount === 0 || this.charCount > 100;
                },
                clearError(field) {
                    if (this.errors[field]) {
                        delete this.errors[field];
                    }
                },
                async submitForm() {
                    this.errors = {};
                    const trimmedTitle = this.title.trim();
                    if (!trimmedTitle) {
                        this.errors.title = ['Judul tugas wajib diisi dan tidak boleh hanya spasi.'];
                        return;
                    }
                    if (trimmedTitle.length > 100) {
                        this.errors.title = ['Judul tugas maksimal 100 karakter.'];
                        return;
                    }

                    this.submitting = true;

                    try {
                        const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                        const response = await fetch('{{ route('tasks.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                title: trimmedTitle,
                                description: this.description ? this.description.trim() : null
                            })
                        });

                        if (response.status === 201) {
                            sessionStorage.setItem('toast_success', 'Tugas berhasil ditambahkan.');
                            window.location.reload();
                            return;
                        }

                        if (response.status === 422) {
                            const data = await response.json();
                            this.errors = data.errors || {};
                        } else {
                            const data = await response.json().catch(() => ({}));
                            alert(data.message || 'Terjadi kesalahan saat menambahkan tugas.');
                        }
                    } catch (e) {
                        alert('Gagal menghubungi server.');
                    } finally {
                        this.submitting = false;
                    }
                }
              }"
              @submit.prevent="submitForm"
              method="POST"
              action="{{ route('tasks.store') }}"
              class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm">
            @csrf

            {{-- Judul + counter --}}
            <div class="flex items-center justify-between mb-2">
                <label for="title-input" class="text-sm font-medium text-gray-700">Judul tugas</label>
                <span class="text-xs transition-colors"
                      :class="counterClass"
                      x-text="charCount + ' / 100'">0 / 100</span>
            </div>
            <input id="title-input"
                   name="title"
                   type="text"
                   x-model="title"
                   @input="clearError('title')"
                   placeholder="Tuliskan judul tugas yang jelas..."
                   class="w-full rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                   :class="errors.title ? 'border-red-500 ring-1 ring-red-500' : 'border border-gray-300'"
                   :disabled="submitting">
            <template x-if="errors.title">
                <p class="text-[13px] text-red-600 mt-1" x-text="errors.title[0]"></p>
            </template>
            @error('title')
                <p class="text-[13px] text-red-600 mt-1">{{ $message }}</p>
            @enderror

            {{-- Deskripsi --}}
            <label for="desc-input" class="block text-sm font-medium text-gray-700 mt-4 mb-2">
                Deskripsi (opsional)
            </label>
            <textarea id="desc-input"
                      name="description"
                      x-model="description"
                      @input="clearError('description')"
                      rows="3"
                      placeholder="Tambahkan detail atau instruksi tugas jika diperlukan..."
                      class="w-full rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none transition-colors"
                      :class="errors.description ? 'border-red-500 ring-1 ring-red-500' : 'border border-gray-300'"
                      :disabled="submitting"></textarea>
            <template x-if="errors.description">
                <p class="text-[13px] text-red-600 mt-1" x-text="errors.description[0]"></p>
            </template>
            @error('description')
                <p class="text-[13px] text-red-600 mt-1">{{ $message }}</p>
            @enderror

            {{-- Tombol submit (kanan) --}}
            <div class="flex justify-end mt-4">
                <button type="submit"
                        :disabled="submitting || isInvalid"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <template x-if="!submitting">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah tugas
                        </span>
                    </template>
                    <template x-if="submitting">
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </template>
                </button>
            </div>
        </form>

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

                            {{-- Deskripsi (jika ada) --}}
                            @if ($task->description)
                                <p class="text-sm text-gray-600 mt-1 {{ $task->is_done ? 'text-gray-400 line-through' : '' }}">
                                    {{ $task->description }}
                                </p>
                            @endif

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
