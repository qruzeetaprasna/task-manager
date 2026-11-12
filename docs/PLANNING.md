# PLANNING: Aplikasi Manajemen Tugas

Dokumen ini adalah **sumber kebenaran** untuk semua AI agent dan kontributor di repo ini.
Letakkan di `docs/PLANNING.md`. Jika ada yang perlu diubah (terutama bagian Kontrak),
tanyakan dulu ke pemilik proyek, jangan mengubah sepihak.

## 1. Ringkasan

Aplikasi internal untuk mencatat dan membagi tugas di dalam tim. Pengguna dapat
registrasi, login, membuat tugas, dan menugaskannya ke satu atau lebih anggota tim.
Satu pengguna bisa memegang banyak tugas, dan satu tugas bisa dipegang banyak pengguna.

Dua jenis pengguna:

- **Admin**: dapat mengelola seluruh tugas.
- **Member**: bekerja pada tugas yang ia buat sendiri atau yang ditugaskan kepadanya.

## 2. Stack

| Bagian | Pilihan |
|---|---|
| Backend | Laravel (versi stabil terbaru) |
| Frontend | Blade + Tailwind (via Vite) + Alpine.js, satu proyek |
| Auth | Laravel Breeze (Blade), berbasis session |
| Database | SQLite, migrasi Laravel biasa (portabel ke MySQL/PostgreSQL lewat `.env`) |
| Hak akses | Laravel Policy (`TaskPolicy`) |

Jangan menambah library di luar stack ini tanpa izin.

Spesifikasi tampilan (warna, tipografi, komponen, perilaku Alpine, perbedaan per role) ada di
`docs/DESIGN.md`. Track B2 wajib mengikutinya.

## 3. Desain Database

**users**: id, name, email (unique), password, `role` (`admin` / `member`, default `member`), timestamps

**tasks**: id, `title` (varchar 100, wajib), description (nullable), `is_done` (boolean, default false), `created_by` (FK ke users), timestamps

**task_user** (pivot assignee): task_id, user_id, unique(task_id, user_id), `onDelete cascade` pada kedua FK

## 4. Hak Akses dan Asumsi

| Aksi | Admin | Member |
|---|---|---|
| Lihat daftar tugas | Semua | Yang ia buat atau ditugaskan kepadanya |
| Buat tugas | Ya | Ya |
| Ubah tugas (judul, status) | Semua | Yang ia buat atau ditugaskan kepadanya |
| Hapus tugas | Semua | Hanya yang ia buat |
| Tambah/hapus assignee | Semua | Hanya pada tugas yang ia buat |

Asumsi:

- Registrasi selalu menghasilkan `member`. Admin dibuat lewat seeder.
- Pembuat tugas tidak otomatis menjadi assignee, tetapi tetap bisa melihat tugasnya.
- Tugas tanpa assignee diperbolehkan.
- Akses yang tidak berhak ditolak dengan 403.
- Password minimal 8 karakter.
- Judul tugas: wajib, tidak boleh hanya spasi, maksimal 100 karakter.
- Hak akses **selalu dicek di backend** lewat Policy. Menyembunyikan tombol di frontend saja tidak cukup.

## 5. Kontrak

Kontrak ini membuat backend dan frontend bisa dikerjakan paralel. **Jangan diubah sepihak.**

### 5.1 Model dan relasi

- `User`: `tasks()` (tugas yang ditugaskan, belongsToMany), `createdTasks()` (hasMany via `created_by`), `isAdmin(): bool`
- `Task`: `creator()` (belongsTo User via `created_by`), `assignees()` (belongsToMany User), fillable `title`, `description`, `is_done`
- Cast: `is_done` menjadi boolean

### 5.2 Route

Semua route berada di dalam middleware `auth`.

| Method | URL | Nama route | Keterangan |
|---|---|---|---|
| GET | `/tasks?status=done` atau `pending` | `tasks.index` | Halaman daftar tugas (sesuai hak akses) |
| POST | `/tasks` | `tasks.store` | Tambah tugas |
| PUT | `/tasks/{task}` | `tasks.update` | Ubah judul, deskripsi, status |
| DELETE | `/tasks/{task}` | `tasks.destroy` | Hapus tugas |
| POST | `/tasks/{task}/assignees` | `tasks.assignees.store` | Body: `user_id` |
| DELETE | `/tasks/{task}/assignees/{user}` | `tasks.assignees.destroy` | Hapus assignee |

### 5.3 Format respons

`store`, `update`, dan `destroy`: jika request meminta JSON (`expectsJson()`), kembalikan JSON;
jika tidak, redirect ke `tasks.index` dengan flash message.

- `PUT /tasks/{task}` (JSON): `200 { "id": 1, "title": "...", "is_done": true }`
- `POST` dan `DELETE` assignee (JSON): `200 { "assignees": [ { "id": 2, "name": "Budi" } ] }`
- Error validasi: `422` format standar Laravel (`message`, `errors`)
- Tidak berhak: `403`
- Assignee ganda atau `user_id` tidak ada: `422`

### 5.4 Data untuk view `tasks.index`

- `$tasks`: koleksi tugas dengan eager load `creator` dan `assignees`, urut terbaru
- `$users`: daftar pengguna (`id`, `name`) untuk dropdown assignee
- `$status`: nilai filter aktif (`done`, `pending`, atau `null`)
- Tombol di Blade memakai `@can('update', $task)`, `@can('delete', $task)`, dan `@can('manageAssignees', $task)`

### 5.5 Method TaskPolicy

`view`, `update`, `delete`, `manageAssignees`, masing-masing menerima `User $user, Task $task`.
Aturan sesuai tabel pada bagian 4. Admin lolos semua lewat `before()`.

## 6. Kepemilikan File

Setiap file hanya punya **satu pemilik**. Jangan mengedit file milik track lain; tulis permintaan
perubahannya sebagai catatan.

| Track | Pemilik | File |
|---|---|---|
| A | Pemilik proyek | `database/migrations/*`, `app/Models/*`, `app/Policies/TaskPolicy.php`, `app/Providers/AppServiceProvider.php`, `README.md` |
| B1 | AI agent (backend) | `app/Http/Controllers/Task*Controller.php`, `app/Http/Requests/*`, `database/factories/*`, `database/seeders/*`, `routes/web.php` |
| B2 | AI agent (frontend) | `resources/views/tasks/*`, `resources/views/layouts/*`, navigasi, `resources/js/*`, `resources/css/*` |

Git: satu branch per track (`track-a/...`, `track-b1/...`, `track-b2/...`), commit kecil dengan pesan jelas,
merge ke `main` hanya di titik sinkronisasi dengan urutan A, lalu B1, lalu B2.

## 7. Alur Kerja per Fitur

Kerjakan **satu fitur per sesi**. Setelah selesai: pastikan kriteria selesai terpenuhi, buat catatan
belajar, ringkas di chat, lalu **berhenti** sampai pemilik proyek menguji dan menyatakan paham.

| Kode | Fitur | Pemilik | Bergantung pada | Kriteria selesai |
|---|---|---|---|---|
| F0 | Setup: Laravel, Breeze, SQLite, git, kerangka TaskPolicy | A | - | Aplikasi jalan, register dan login berfungsi, commit awal di `main` |
| F1 | Fondasi data: migrasi, model, relasi | A | F0 | `migrate:fresh` berhasil, relasi `assignees()` dan `creator()` bisa dicoba di tinker |
| F2 | Seeder dan factory | B1 | F1 | `migrate:fresh --seed` menghasilkan 1 admin, 3 member, sekitar 8 tugas dengan assignee |
| F3 | Daftar tugas dan filter status | B1 + B2 | F1, F2 | Filter Semua, Belum selesai, Selesai bekerja, tidak ada N+1 |
| F4 | Tambah tugas dan validasi | B1 + B2 | F3 | Judul kosong, hanya spasi, dan lebih dari 100 karakter ditolak di backend dan frontend, ada penghitung karakter |
| F5 | Ubah, centang selesai, hapus tugas | B1 + B2 | F4 | Ketiga aksi jalan lewat form dan Alpine, respons sesuai bagian 5.3 |
| F6 | Assignee: tambah dan hapus | B1 + B2 | F5 | Assignee ganda dan user tidak ada ditolak (422), daftar assignee berubah tanpa reload |
| F7 | TaskPolicy asli | A | F1 | Semua skenario hak akses (bagian 8) lolos lewat tinker dan browser |
| F8 | Tampilan per role | B2 | F3, F7 | Badge Admin, kolom pembuat, tombol tampil sesuai `@can` |
| F9 | README, pembersihan, uji akhir | A | Semua | Daftar cek bagian 9 terpenuhi |

F3 sampai F6: backend dahulu lalu tampilan, atau paralel jika B2 memakai data dummy sesuai kontrak.
F7 dikerjakan paralel dengan F2 sampai F6.

### Catatan belajar

Setelah setiap fitur, buat `docs/learning/<KODE>-<track>-<nama>.md`
(contoh: `docs/learning/F3-b1-daftar-tugas.md`) memakai `docs/learning/_TEMPLATE.md`
dengan bagian: Tujuan, File yang dibuat atau diubah, Alur kerja (dari klik sampai database),
Penjelasan kode penting, Kenapa dibuat begini, Cara mengujinya, Istilah baru, dan Cek pemahaman
(3 pertanyaan, tanpa jawaban). Pemilik proyek adalah pemula, jadi gunakan bahasa Indonesia yang sederhana.

## 8. Skenario Uji Manual

**Sebagai member:**

- Hanya melihat tugas yang ia buat atau ditugaskan kepadanya
- Membuka URL tugas milik orang lain langsung mendapat 403
- Tidak bisa menghapus tugas yang hanya ditugaskan kepadanya, tetapi bisa mengubahnya
- Tidak melihat tombol hapus atau kelola assignee pada tugas yang bukan miliknya
- Judul kosong, hanya spasi, dan 101 karakter ditolak

**Sebagai admin:**

- Melihat semua tugas dan kolom pembuat
- Bisa mengubah, menghapus, dan mengelola assignee pada semua tugas

**Umum:**

- Menambah assignee yang sama dua kali ditolak
- Menghapus tugas ikut menghapus baris pivot di `task_user`
- Filter status Semua, Belum selesai, Selesai bekerja
- Password tersimpan sebagai hash dan tidak muncul di respons
- Registrasi selalu menghasilkan role `member`

## 9. Daftar Cek Sebelum Kumpul

- [ ] Judul divalidasi di backend dan frontend (kosong, spasi saja, lebih dari 100 karakter)
- [ ] Password ter-hash dan tidak muncul di respons
- [ ] Assignee tidak ganda, user tidak ada ditolak
- [ ] Member tidak bisa mengakses atau mengubah tugas orang lain, termasuk lewat URL langsung
- [ ] Tidak ada N+1 (eager loading `creator` dan `assignees`)
- [ ] `.env` tidak di-commit, `.env.example` ada
- [ ] `php artisan migrate:fresh --seed` berhasil dari repo bersih
- [ ] README memuat deskripsi, cara menjalankan, akun demo, daftar route, asumsi hak akses, dan fitur yang belum sempat
- [ ] Riwayat commit rapi
- [ ] Pemilik proyek bisa menjelaskan seluruh kode, terutama migrasi, relasi, policy, dan alur Alpine ke controller

## 10. Bonus (hanya jika waktu sisa)

Urutan prioritas: Docker, halaman admin (daftar pengguna dan ubah role, cegah admin terakhir
menurunkan dirinya), test policy, tag. Fitur wajib yang rapi lebih penting daripada bonus setengah jadi.
