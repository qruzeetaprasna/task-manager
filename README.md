# Aplikasi Manajemen Tugas

Aplikasi web untuk mencatat tugas tim dan menetapkan anggota sebagai penanggung jawab. Admin dapat mengelola semua tugas, sedangkan member bekerja pada tugas yang dibuatnya atau yang ditugaskan kepadanya.

## Tech stack

| Teknologi | Versi atau konfigurasi |
|---|---|
| PHP | `^8.3` (terpasang 8.3.29) |
| Laravel | `^13.17` (terpasang 13.42.0) |
| Laravel Breeze | `^2.4`, autentikasi session dengan Blade |
| Tailwind CSS | `^3.1` (terpasang 3.4.19) |
| Alpine.js | `^3.4.2` (terpasang 3.17.5) |
| Vite | `^8.0` (terpasang 8.3.8) |
| Database | SQLite |

## Fitur

- Registrasi, login, dan logout.
- Daftar tugas dengan filter semua, belum selesai, dan selesai.
- Membuat tugas dengan deskripsi opsional; mengubah judul, deskripsi, dan status; serta menghapus tugas.
- Form tambah tugas ditampilkan dalam modal.
- Menambah dan menghapus penanggung jawab tugas.
- Daftar admin mencakup semua tugas. Daftar member hanya mencakup tugas yang dibuat atau ditugaskan kepadanya.
- Tombol ubah, hapus, dan kelola penanggung jawab mengikuti hak akses masing-masing peran.
- Halaman profil untuk memperbarui nama, email, kata sandi, atau menghapus akun; menu profil pada navbar juga menyediakan tautan profil dan keluar.
- Admin dapat membuat akun admin atau member, melihat daftar pengguna, mencari dan memfilter pengguna pada halaman aktif, serta mengubah peran admin/member.
- Tes otomatis untuk autentikasi dan operasi tugas.

## Cara menjalankan

Prasyarat: Git, PHP 8.3 atau lebih baru dengan ekstensi PDO SQLite, Composer, serta Node.js dan npm.

Jalankan perintah berikut di PowerShell:

```powershell
git clone https://github.com/qruzeetaprasna/task-manager.git
Set-Location task-manager
composer install
Copy-Item .env.example .env
php artisan key:generate
if (-not (Test-Path database\database.sqlite)) {
    New-Item -ItemType File -Path database\database.sqlite
}
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000/login` untuk masuk atau `http://127.0.0.1:8000/register` untuk membuat akun. Setelah masuk, daftar tugas tersedia di `http://127.0.0.1:8000/tasks`.

## Akun demo

Semua akun berikut dibuat oleh seeder. Kata sandi masing-masing adalah `password`.

| Peran | Email | Kata sandi |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Member | `sari@example.com` | `password` |
| Member | `andi@example.com` | `password` |
| Member | `dewi@example.com` | `password` |

## Struktur database

| Tabel | Kolom penting | Relasi |
|---|---|---|
| `users` | `id`, `name`, `email` (unik), `password`, `role` (`admin` atau `member`, default `member`), timestamps | Satu pengguna dapat membuat banyak tugas dan menjadi penanggung jawab banyak tugas. |
| `tasks` | `id`, `title` (maksimal 100 karakter), `description` (boleh kosong), `is_done` (default `false`), `created_by`, timestamps | `created_by` mengacu ke `users.id`; setiap tugas memiliki satu pembuat dan dapat memiliki banyak penanggung jawab. |
| `task_user` | `id`, `task_id`, `user_id`, timestamps; pasangan `task_id` dan `user_id` unik | Tabel penghubung many-to-many antara tugas dan pengguna. Foreign key tugas dan pengguna menghapus baris penghubung terkait saat data induknya dihapus. |

## Route

Route tugas hanya dapat digunakan setelah login. Route registrasi dan login tersedia untuk pengguna yang belum login.

| Method | URL | Fungsi |
|---|---|---|
| GET | `/register` | Menampilkan formulir registrasi |
| POST | `/register` | Membuat akun member |
| GET | `/login` | Menampilkan formulir login |
| POST | `/login` | Memproses login |
| POST | `/logout` | Memproses logout |
| GET | `/` | Mengarahkan pengguna yang sudah login ke daftar tugas |
| GET | `/profile` | Menampilkan halaman profil |
| PATCH | `/profile` | Memperbarui nama atau email |
| DELETE | `/profile` | Menghapus akun setelah kata sandi saat ini dikonfirmasi |
| PUT | `/password` | Memperbarui kata sandi setelah kata sandi saat ini dikonfirmasi |
| GET | `/admin/users` | Menampilkan daftar pengguna untuk admin (menerima JSON jika diminta) |
| POST | `/admin/users` | Membuat akun pengguna dengan peran yang dipilih (admin saja) |
| PATCH | `/admin/users/{user}/role` | Mengubah peran pengguna untuk admin |
| GET | `/tasks` | Menampilkan daftar tugas; filter memakai `?status=pending` atau `?status=done` |
| POST | `/tasks` | Membuat tugas |
| PUT | `/tasks/{task}` | Mengubah tugas atau status selesai |
| DELETE | `/tasks/{task}` | Menghapus tugas |
| POST | `/tasks/{task}/assignees` | Menambah penanggung jawab |
| DELETE | `/tasks/{task}/assignees/{user}` | Menghapus penanggung jawab |

Tidak ada route halaman detail tugas tersendiri. Nilai filter status selain `pending` dan `done` diperlakukan seperti tanpa filter.

## Hak akses

| Aksi | Admin | Member |
|---|---|---|
| Melihat daftar tugas | Semua tugas | Tugas yang dibuat atau ditugaskan kepadanya |
| Membuat tugas | Ya | Ya |
| Mengubah tugas | Semua tugas | Tugas yang dibuat atau ditugaskan kepadanya |
| Menghapus tugas | Semua tugas | Hanya tugas yang dibuatnya |
| Menambah atau menghapus penanggung jawab | Semua tugas | Hanya tugas yang dibuatnya |
| Melihat daftar pengguna, membuat pengguna, dan mengubah peran | Ya | Tidak (HTTP `403`) |
| Mengelola profil sendiri | Ya | Ya |
| Menghapus akun sendiri setelah konfirmasi kata sandi | Ya | Ya |

Hak akses tugas diperiksa di backend melalui `TaskPolicy`. Admin melewati seluruh pemeriksaan policy; member mengikuti aturan pada tabel. Tidak ada halaman detail tugas yang memakai aksi `view` policy.

## Asumsi dan keputusan desain

- Registrasi publik selalu membuat akun dengan peran `member`; formulir registrasi tidak menerima pilihan peran. Admin dapat membuat akun admin atau member melalui halaman pengelolaan pengguna, dan seeder juga membuat satu akun admin demo.
- Pembuat tugas tidak otomatis menjadi penanggung jawab. Tugas tanpa penanggung jawab diperbolehkan.
- Pengguna yang belum login diarahkan ke login untuk route tugas. Permintaan pengguna yang sudah login tetapi tidak berhak mendapat HTTP `403`.
- Judul wajib berupa teks, tidak boleh hanya spasi, dan maksimal 100 karakter. Deskripsi boleh kosong. Perubahan tugas menerima kolom yang dikirim saja.
- Kata sandi harus dikonfirmasi dan mengikuti `Password::defaults()` Laravel, yang pada konfigurasi ini mensyaratkan minimal 8 karakter. Kata sandi disimpan dalam bentuk hash.
- Saat membuat tugas, pembuat diambil dari pengguna yang sedang login dan status awal selalu belum selesai.
- Penanggung jawab harus merujuk ke pengguna yang ada dan tidak boleh ditambahkan dua kali pada tugas yang sama; kesalahan validasi pada request JSON mendapat HTTP `422`.
- Pembuatan tugas melalui request JSON mendapat HTTP `201`; perubahan dan penghapusan melalui JSON mendapat HTTP `200`. Form biasa kembali ke daftar tugas dengan pesan sesi.
- Daftar pengguna admin menampilkan 15 pengguna per halaman; pencarian dan filter peran pada tampilan hanya berlaku untuk halaman yang sedang dibuka.
- Form admin untuk membuat pengguna mewajibkan nama, email unik, kata sandi minimal 8 karakter, dan peran `admin` atau `member`.
- Admin terakhir tidak dapat mengubah perannya sendiri menjadi member. Perubahan peran admin membutuhkan nilai `admin` atau `member`.
- Perubahan email profil menghapus tanda verifikasi email; form profil hanya menampilkan pengiriman ulang verifikasi jika pengguna belum terverifikasi.

## Validasi dan keamanan

Judul divalidasi di server dan diperiksa juga pada tampilan sebelum dikirim. Form menggunakan perlindungan CSRF Laravel; permintaan JavaScript mengirim token CSRF. `TaskPolicy` memeriksa hak akses tugas, sedangkan `UserPolicy` membatasi daftar pengguna dan perubahan peran untuk admin. Endpoint pembuatan pengguna memeriksa peran admin di controller. Atribut kata sandi di model di-hash dan disembunyikan dari serialisasi pengguna.

## Pengujian

Jalankan tes otomatis dengan:

```powershell
php artisan test
```

## Belum dikerjakan dan keterbatasan

- Pencarian dan filter peran pada daftar admin hanya mencakup halaman pagination yang sedang dibuka.
- Tag tugas belum tersedia.
- Konfigurasi Docker belum tersedia.
- Belum ada halaman detail tugas; pengubahan dilakukan dari daftar tugas.

## Penggunaan AI

Saya menggunakan AI agent untuk membantu menulis controller, validasi, seeder, dan tampilan Blade/Alpine. Saya sendiri merencanakan arsitektur dan hak akses, menulis migrasi, model, relasi, dan TaskPolicy, menguji semua fitur secara manual, serta mereview kode yang dihasilkan.
