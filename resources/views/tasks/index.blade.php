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

        <div x-data="{
                runningCount: {{ $runningCount }},
                doneCount: {{ $doneCount }},
                totalCount: {{ $totalCount }}
             }"
             @task-status-changed.window="
                if ($event.detail.is_done) {
                    runningCount--;
                    doneCount++;
                } else {
                    runningCount++;
                    doneCount--;
                }
             "
             @task-deleted.window="
                totalCount--;
             "
             class="flex items-start justify-between mb-5">
            <div>
                <h1 class="text-[28px] font-bold text-gray-900 leading-tight">Tugas</h1>
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
                    <span x-text="runningCount + ' Berjalan'">{{ $runningCount }} Berjalan</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                    <span x-text="doneCount + ' Selesai'">{{ $doneCount }} Selesai</span>
                </span>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- Tab filter status                                        --}}
        {{-- ======================================================= --}}
        <div class="mb-6 flex items-center gap-4">
            <div class="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto pb-0.5">
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

            <button
                type="button"
                x-data=""
                x-on:click="$dispatch('open-modal', 'create-task-modal')"
                aria-haspopup="dialog"
                class="inline-flex min-h-11 shrink-0 items-center gap-2 whitespace-nowrap rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah tugas
            </button>
        </div>

        {{-- ======================================================= --}}
        {{-- Modal tambah tugas (Alpine.js)                           --}}
        {{-- ======================================================= --}}
        <x-modal name="create-task-modal" :show="$errors->has('title') || $errors->has('description')" focusable>
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
              class="p-6 sm:p-8"
              role="dialog"
              aria-modal="true"
              aria-labelledby="create-task-heading"
              aria-describedby="create-task-description">
            @csrf

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="create-task-heading" class="text-lg font-semibold text-gray-900">Tambah tugas</h2>
                    <p id="create-task-description" class="mt-1 text-sm text-gray-600">
                        Isi judul tugas dan detail jika diperlukan.
                    </p>
                </div>
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    x-bind:disabled="submitting"
                    aria-label="Tutup form tambah tugas"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Judul + counter --}}
            <div class="mt-6 flex items-center justify-between mb-2">
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
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button
                    type="button"
                    x-on:click="$dispatch('close')"
                    x-bind:disabled="submitting"
                    class="min-h-11 rounded-lg normal-case"
                >
                    Batal
                </x-secondary-button>
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
        </x-modal>

        {{-- ======================================================= --}}
        {{-- Daftar kartu tugas                                       --}}
        {{-- ======================================================= --}}
        <div class="space-y-3">
            @forelse ($tasks as $task)
                <div x-data="{
                        id: {{ $task->id }},
                        title: @js($task->title),
                        description: @js($task->description ?? ''),
                        isDone: {{ $task->is_done ? 'true' : 'false' }},
                        deleted: false,
                        updating: false,
                        assigneeStoreUrl: @js(route('tasks.assignees.store', $task)),
                        assigneeDestroyUrlTemplate: @js(route('tasks.assignees.destroy', [$task, '__ASSIGNEE_ID__'])),
                        assignees: @js($task->assignees->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()),
                        allUsers: @js($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()),
                        openAddAssignee: false,
                        addingAssignee: false,
                        removingAssigneeId: null,
                        get availableUsers() {
                            const currentIds = this.assignees.map(a => a.id);
                            return this.allUsers.filter(u => !currentIds.includes(u.id));
                        },
                        async addAssignee(userId) {
                            if (!userId || this.addingAssignee) return;
                            this.addingAssignee = true;
                            try {
                                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                                const response = await fetch(this.assigneeStoreUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({ user_id: userId })
                                });

                                if (response.ok) {
                                    const data = await response.json();
                                    this.assignees = data.assignees;
                                    this.openAddAssignee = false;
                                    window.dispatchEvent(new CustomEvent('toast', { detail: 'Penanggung jawab berhasil ditambahkan.' }));
                                } else {
                                    const data = await response.json().catch(() => ({}));
                                    const errMsg = (data.errors && data.errors.user_id && data.errors.user_id[0]) || data.message || 'Gagal menambahkan penanggung jawab.';
                                    alert(errMsg);
                                }
                            } catch (e) {
                                alert('Gagal menghubungi server.');
                            } finally {
                                this.addingAssignee = false;
                            }
                        },
                        async removeAssignee(userId, userName) {
                            if (this.removingAssigneeId) return;
                            this.removingAssigneeId = userId;
                            try {
                                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                                const destroyUrl = this.assigneeDestroyUrlTemplate.replace('__ASSIGNEE_ID__', encodeURIComponent(userId));
                                const response = await fetch(destroyUrl, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    }
                                });

                                if (response.ok) {
                                    const data = await response.json();
                                    this.assignees = data.assignees;
                                    window.dispatchEvent(new CustomEvent('toast', { detail: 'Penanggung jawab berhasil dihapus.' }));
                                } else {
                                    const data = await response.json().catch(() => ({}));
                                    alert(data.message || 'Gagal menghapus penanggung jawab.');
                                }
                            } catch (e) {
                                alert('Gagal menghubungi server.');
                            } finally {
                                this.removingAssigneeId = null;
                            }
                        },
                        async toggleDone() {
                            if (this.updating) return;
                            this.updating = true;
                            const newStatus = !this.isDone;
                            try {
                                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                                const response = await fetch(`/tasks/${this.id}`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({ is_done: newStatus })
                                });

                                if (response.ok) {
                                    const data = await response.json();
                                    this.isDone = data.is_done;
                                    window.dispatchEvent(new CustomEvent('toast', { detail: 'Tugas berhasil diperbarui.' }));
                                    window.dispatchEvent(new CustomEvent('task-status-changed', { detail: { id: this.id, is_done: this.isDone } }));
                                } else {
                                    const data = await response.json().catch(() => ({}));
                                    alert(data.message || 'Gagal memperbarui status tugas.');
                                }
                            } catch (e) {
                                alert('Gagal menghubungi server.');
                            } finally {
                                this.updating = false;
                            }
                        }
                     }"
                     x-show="!deleted"
                     x-cloak
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @task-updated.window="if ($event.detail.id === id) { title = $event.detail.title; description = $event.detail.description; }"
                     @task-deleted.window="if ($event.detail.id === id) { deleted = true; }"
                     class="border border-gray-200 rounded-xl p-5 shadow-sm transition-colors"
                     :class="isDone ? 'bg-gray-50' : 'bg-white'">
                    <div class="flex items-start gap-4">

                        {{-- Checkbox --}}
                        <div class="shrink-0 mt-0.5">
                            @can('update', $task)
                                <input type="checkbox"
                                       :checked="isDone"
                                       @change="toggleDone()"
                                       :disabled="updating"
                                       class="w-[22px] h-[22px] rounded border-gray-300 text-indigo-600 accent-indigo-600 focus:ring-indigo-500 cursor-pointer disabled:opacity-50"
                                       aria-label="Status tugas: {{ $task->title }}">
                            @else
                                <input type="checkbox"
                                       :checked="isDone"
                                       class="w-[22px] h-[22px] rounded border-gray-300 text-indigo-600 accent-indigo-600 cursor-not-allowed opacity-60"
                                       aria-label="Status tugas: {{ $task->title }}"
                                       disabled>
                            @endcan
                        </div>

                        {{-- Konten utama --}}
                        <div class="flex-1 min-w-0">

                            {{-- Judul + pill Selesai --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[17px] font-semibold leading-snug transition-colors"
                                    :class="isDone ? 'text-gray-400 line-through' : 'text-gray-900'"
                                    x-text="title">
                                    {{ $task->title }}
                                </h3>
                                <span x-show="isDone"
                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    Selesai
                                </span>
                            </div>

                            {{-- Deskripsi (jika ada) --}}
                            <template x-if="description">
                                <p class="text-sm mt-1 transition-colors"
                                   :class="isDone ? 'text-gray-400 line-through' : 'text-gray-600'"
                                   x-text="description">
                                    {{ $task->description }}
                                </p>
                            </template>

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

                                <template x-if="assignees.length === 0">
                                    <span class="text-xs text-gray-400 italic">belum ada</span>
                                </template>

                                <template x-for="assignee in assignees" :key="assignee.id">
                                    <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                        <span x-text="assignee.name"></span>
                                        @can('manageAssignees', $task)
                                            <button type="button"
                                                    @click="removeAssignee(assignee.id, assignee.name)"
                                                    :disabled="removingAssigneeId === assignee.id"
                                                    class="ml-0.5 text-indigo-400 hover:text-indigo-700 focus:outline-none leading-none disabled:opacity-50"
                                                    :aria-label="'Hapus ' + assignee.name + ' dari penanggung jawab'">
                                                <template x-if="removingAssigneeId !== assignee.id">
                                                    <span>&times;</span>
                                                </template>
                                                <template x-if="removingAssigneeId === assignee.id">
                                                    <svg class="animate-spin w-2.5 h-2.5 text-indigo-600 inline" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </template>
                                            </button>
                                        @endcan
                                    </span>
                                </template>

                                @can('manageAssignees', $task)
                                    <div class="relative inline-block" @click.outside="openAddAssignee = false">
                                        <button type="button"
                                                @click="openAddAssignee = !openAddAssignee"
                                                :disabled="addingAssignee"
                                                :aria-expanded="openAddAssignee"
                                                :aria-controls="'assignee-options-' + id"
                                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full border border-dashed border-indigo-300 text-indigo-600 hover:text-indigo-800 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-colors disabled:opacity-50">
                                            <template x-if="!addingAssignee">
                                                <span>+ Tambah</span>
                                            </template>
                                            <template x-if="addingAssignee">
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="animate-spin w-3 h-3 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Menyimpan...
                                                </span>
                                            </template>
                                        </button>

                                        {{-- Dropdown pilihan anggota --}}
                                        <div x-show="openAddAssignee"
                                             x-cloak
                                             id="assignee-options-{{ $task->id }}"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             @keydown.escape.window="openAddAssignee = false"
                                             class="absolute left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-30 max-h-48 overflow-y-auto">
                                            <template x-if="availableUsers.length > 0">
                                                <div>
                                                    <template x-for="user in availableUsers" :key="user.id">
                                                        <button type="button"
                                                                @click="addAssignee(user.id)"
                                                                class="w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-900 transition-colors flex items-center justify-between">
                                                            <span x-text="user.name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="availableUsers.length === 0">
                                                <div class="px-3 py-2 text-xs text-gray-400 italic">
                                                    Semua anggota sudah ditugaskan
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                @endcan
                            </div>
                        </div>

                        {{-- Tombol aksi (kanan) --}}
                        <div class="flex items-center gap-2 shrink-0 self-start mt-0.5">
                            @can('update', $task)
                                <button type="button"
                                        @click="$dispatch('open-edit-task', { id: id, title: title, description: description })"
                                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                                    Edit
                                </button>
                            @endcan

                            @can('delete', $task)
                                <button type="button"
                                        @click="$dispatch('open-delete-task', { id: id })"
                                        class="px-3 py-1.5 text-xs font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300 transition-colors">
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

    {{-- ======================================================= --}}
    {{-- Modal Edit Tugas (x-modal)                              --}}
    {{-- ======================================================= --}}
    <x-modal name="edit-task-modal" focusable>
        <form x-data="{
                taskId: null,
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
                async submitEdit() {
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
                        const response = await fetch(`/tasks/${this.taskId}`, {
                            method: 'PUT',
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

                        if (response.ok) {
                            const data = await response.json();
                            window.dispatchEvent(new CustomEvent('task-updated', {
                                detail: {
                                    id: this.taskId,
                                    title: trimmedTitle,
                                    description: this.description ? this.description.trim() : null
                                }
                            }));
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-task-modal' }));
                            window.dispatchEvent(new CustomEvent('toast', { detail: 'Tugas berhasil diperbarui.' }));
                        } else if (response.status === 422) {
                            const data = await response.json();
                            this.errors = data.errors || {};
                        } else {
                            const data = await response.json().catch(() => ({}));
                            alert(data.message || 'Gagal memperbarui tugas.');
                        }
                    } catch (e) {
                        alert('Gagal menghubungi server.');
                    } finally {
                        this.submitting = false;
                    }
                }
             }"
             @open-edit-task.window="
                taskId = $event.detail.id;
                title = $event.detail.title;
                description = $event.detail.description || '';
                errors = {};
                $dispatch('open-modal', 'edit-task-modal');
             "
             @submit.prevent="submitEdit"
             class="p-6">
            @csrf

            <h2 class="text-lg font-bold text-gray-900 mb-4">Edit Tugas</h2>

            {{-- Judul + counter --}}
            <div class="flex items-center justify-between mb-2">
                <label for="edit-title-input" class="text-sm font-medium text-gray-700">Judul tugas</label>
                <span class="text-xs transition-colors"
                      :class="counterClass"
                      x-text="charCount + ' / 100'">0 / 100</span>
            </div>
            <input id="edit-title-input"
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

            {{-- Deskripsi --}}
            <label for="edit-desc-input" class="block text-sm font-medium text-gray-700 mt-4 mb-2">
                Deskripsi (opsional)
            </label>
            <textarea id="edit-desc-input"
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

            {{-- Tombol Batal & Simpan --}}
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')" x-bind:disabled="submitting">
                    Batal
                </x-secondary-button>

                <button type="submit"
                        :disabled="submitting || isInvalid"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </form>
    </x-modal>

    {{-- ======================================================= --}}
    {{-- Modal Konfirmasi Hapus Tugas (x-modal)                  --}}
    {{-- ======================================================= --}}
    <x-modal name="confirm-task-deletion" focusable>
        <div x-data="{
                taskId: null,
                submitting: false,
                async confirmDelete() {
                    this.submitting = true;
                    try {
                        const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                        const response = await fetch(`/tasks/${this.taskId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        if (response.ok) {
                            window.dispatchEvent(new CustomEvent('task-deleted', { detail: { id: this.taskId } }));
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-task-deletion' }));
                            window.dispatchEvent(new CustomEvent('toast', { detail: 'Tugas berhasil dihapus.' }));
                        } else {
                            const data = await response.json().catch(() => ({}));
                            alert(data.message || 'Gagal menghapus tugas.');
                        }
                    } catch (e) {
                        alert('Gagal menghubungi server.');
                    } finally {
                        this.submitting = false;
                    }
                }
             }"
             @open-delete-task.window="
                taskId = $event.detail.id;
                $dispatch('open-modal', 'confirm-task-deletion');
             "
             class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Hapus tugas ini? Tindakan ini tidak bisa dibatalkan.
            </h2>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')" x-bind:disabled="submitting">
                    Batal
                </x-secondary-button>

                <x-danger-button type="button" @click="confirmDelete()" x-bind:disabled="submitting">
                    <span x-text="submitting ? 'Menghapus...' : 'Hapus'"></span>
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</x-app-layout>
