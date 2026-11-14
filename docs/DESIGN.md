# DESIGN: Spesifikasi Tampilan

Acuan tampilan untuk **Track B2 (frontend)**. Letakkan di `docs/DESIGN.md`.
Kontrak data dan route ada di `docs/PLANNING.md` (bagian 5) dan tidak diulang di sini.
Mockup visual yang disetujui: simpan tangkapan layarnya di `docs/design/mockup-admin.png`.

## 1. Prinsip

- Bersih, konsisten, dan jelas. Penilaian ada di fungsi dan hak akses, bukan dekorasi.
- Pakai komponen bawaan Breeze (`x-text-input`, `x-input-label`, `x-primary-button`,
  `x-secondary-button`, `x-danger-button`, `x-modal`, `x-dropdown`) sebelum membuat komponen sendiri.
- Satu halaman utama (`tasks.index`). Tidak ada halaman detail atau edit terpisah; edit lewat modal Alpine.
- Hanya Tailwind dan Alpine. Tidak ada library UI tambahan, tidak ada emoji sebagai ikon.

## 2. Token

### Warna (padanan kelas Tailwind)

| Peran | Hex | Tailwind |
|---|---|---|
| Latar halaman | `#f3f4f6` | `bg-gray-100` |
| Kartu | `#ffffff`, border `#e5e7eb` | `bg-white border-gray-200` |
| Kartu selesai | `#f9fafb` | `bg-gray-50` |
| Teks utama | `#111827` | `text-gray-900` |
| Teks sekunder | `#4b5563` | `text-gray-600` |
| Border input dan tombol | `#d1d5db` | `border-gray-300` |
| Aksen utama (tombol primer, tab aktif, logo) | `#4f46e5` | `bg-indigo-600` |
| Chip assignee | bg `#eef2ff`, teks `#3730a3` | `bg-indigo-50 text-indigo-800` |
| Badge Admin | bg `#ede9fe`, teks `#5b21b6` | `bg-violet-100 text-violet-800` |
| Pill Selesai | bg `#dcfce7`, teks `#166534` | `bg-green-100 text-green-800` |
| Bahaya (Hapus) | teks `#b91c1c`, border `#fecaca` | `text-red-700 border-red-200` |
| Peringatan penghitung | `#b45309` | `text-amber-700` |

Jangan memakai warna di luar tabel ini tanpa izin. Pasangan warna di atas sudah memenuhi kontras teks 4,5:1.

### Tipografi

Font Inter (bobot 400, 500, 600, 700) dengan cadangan `system-ui, sans-serif`.

| Elemen | Ukuran / bobot |
|---|---|
| Judul halaman | 28px / 700 (`text-[28px] font-bold`) |
| Judul tugas | 17px / 600 |
| Teks isi dan input | 15px / 400-500 |
| Meta (dibuat oleh, label kecil, chip) | 13px |
| Badge dan pill | 12px / 700 |

### Bentuk dan jarak

- Kartu: radius 12px, padding 20px, border 1px. Jarak antar kartu 12px.
- Input dan tombol: radius 8px, tinggi 44px (target sentuh minimal 44px).
- Chip dan pill: bentuk kapsul (`rounded-full`).
- Wadah konten: lebar maksimal 960px, di tengah, padding horizontal 24px, vertikal 32px.
- Jarak antar bagian utama (judul, baris filter dan tombol tambah tugas, daftar): 24px.

## 3. Tata letak halaman `tasks.index`

Urutan dari atas ke bawah:

1. **Navbar** (tinggi 64px, putih, garis bawah): logo "TugasKu" di kiri (aksen utama, 20px/700),
   tautan "Tugas" (aktif, garis bawah aksen) dan "Kelola Pengguna" (hanya admin, jika bonus dikerjakan).
   Di kanan: badge Admin (hanya admin), nama pengguna, tombol "Keluar".
2. **Judul halaman** "Tugas" dengan keterangan kecil di bawahnya.
   Admin: "Menampilkan semua tugas tim." Member: "Menampilkan tugas yang kamu buat atau ditugaskan kepadamu."
3. **Baris filter dan aksi**: tab filter di kiri berupa tiga tautan biasa (`?status=`): Semua,
   Belum selesai, Selesai. Yang aktif berlatar aksen dengan teks putih; yang lain putih dengan border.
   Tombol "+ Tambah tugas" sejajar di kanan dan tetap terlihat saat tab filter digulir horizontal pada layar sempit.
4. Saat ditekan, tombol membuka modal `x-modal` berjudul
   "Tambah tugas". Di dalam modal: label "Judul tugas", input, penghitung "N / 100" di kanan label,
   input "Deskripsi (opsional)", dan tombol primer "Tambah tugas". Modal juga menyediakan tombol "Batal"
   dan tombol tutup.
5. **Daftar kartu tugas.**

### Kartu tugas

Susunan satu baris: checkbox di kiri, isi di tengah (fleksibel), tombol aksi di kanan.

- Checkbox 22px dengan `accent-indigo-600`.
- Isi, dari atas: judul, baris meta "Dibuat oleh {nama} · {tanggal}", lalu baris penanggung jawab.
- Baris penanggung jawab: label "Penanggung jawab:", chip per assignee (nama + tombol x), dan tombol "+ Tambah"
  (border putus-putus, kapsul). Tanpa assignee: tampilkan "Penanggung jawab: belum ada" dan tombol "+ Tambah".
- Aksi: "Edit" (tombol sekunder) dan "Hapus" (teks dan border bahaya).
- **Tugas selesai**: kartu berlatar `bg-gray-50`, judul dicoret berwarna abu-abu, ada pill "Selesai",
  checkbox tercentang.

## 4. Perilaku (Alpine.js)

| Elemen | Perilaku |
|---|---|
| Tombol tambah tugas | Membuka modal `x-modal` lewat event Alpine `open-modal`. Modal bisa ditutup dengan tombol tutup, tombol Batal, Escape, atau klik area luar |
| Penghitung judul | Tampil "N / 100". Mulai 90 karakter: teks `amber-700`. Lebih dari 100: merah dan tombol Tambah nonaktif. Hitung setelah `trim()` |
| Error validasi | Pesan dari backend tampil di bawah input terkait (merah, 13px). Input yang salah diberi border merah |
| Checkbox selesai | Kirim `PUT /tasks/{task}` lewat fetch (JSON), perbarui tampilan kartu tanpa reload. Gagal: kembalikan checkbox dan tampilkan pesan |
| Tambah assignee | Tombol "+ Tambah" membuka dropdown berisi pengguna yang belum menjadi assignee. Pilih satu untuk mengirim `POST /tasks/{task}/assignees` |
| Hapus assignee | Tombol x pada chip mengirim `DELETE /tasks/{task}/assignees/{user}`, chip hilang tanpa reload |
| Edit | Modal (`x-modal`) berisi judul dan deskripsi, dengan penghitung dan validasi yang sama seperti form tambah |
| Hapus tugas | Dialog konfirmasi sebelum `DELETE`. Teks: "Hapus tugas ini? Tindakan ini tidak bisa dibatalkan." |
| Loading | Tombol yang sedang memproses dinonaktifkan dan teksnya berganti, misalnya "Menyimpan..." |

Semua fetch memakai header `Accept: application/json`, `X-CSRF-TOKEN` dari meta tag, dan format respons
sesuai `PLANNING.md` bagian 5.3.

## 5. Perbedaan per role

Semua tombol dikendalikan `@can`, bukan pengecekan `role` manual di Blade.

| Elemen | Admin | Member |
|---|---|---|
| Badge Admin di navbar | Tampil | Tidak ada |
| Menu "Kelola Pengguna" | Tampil (jika bonus ada) | Tidak ada |
| Isi daftar | Semua tugas | Hanya yang ia buat atau ditugaskan kepadanya |
| Baris "Dibuat oleh" | Tampil | Tampil hanya jika bukan buatannya |
| Tombol Edit (`@can('update')`) | Semua tugas | Yang ia buat dan yang ditugaskan kepadanya |
| Tombol Hapus (`@can('delete')`) | Semua tugas | Hanya yang ia buat |
| Tombol x dan "+ Tambah" assignee (`@can('manageAssignees')`) | Semua tugas | Hanya yang ia buat. Selain itu chip tampil tanpa tombol x |

## 6. Kondisi khusus

- **Daftar kosong**: kartu dengan pesan "Belum ada tugas. Buat yang pertama di atas." Saat filter
  tidak menghasilkan apa-apa: "Tidak ada tugas pada filter ini."
- **Pesan sukses** (flash message setelah aksi form): banner hijau di bawah judul halaman. Aksi lewat fetch
  memakai toast sederhana di pojok kanan atas yang hilang sendiri.
- **Error 403**: halaman sederhana "Kamu tidak punya akses ke halaman ini" dengan tautan kembali ke daftar tugas.
- **Responsif**: di layar sempit, tombol aksi turun ke bawah isi kartu, tab filter bisa digulir horizontal,
  form tambah menumpuk vertikal. Tidak boleh ada gulir horizontal pada halaman.

## 7. Aksesibilitas

- Gunakan elemen semantik: `<button>`, `<a href>`, `<input>` dengan `<label>`. Jangan pasang `@click` pada `div`.
- Tombol ikon (x pada chip) wajib punya `aria-label`, misalnya "Hapus Andi Pratama dari penanggung jawab".
- Checkbox punya `aria-label` yang menyebut judul tugas.
- Fokus keyboard harus terlihat (pakai ring fokus bawaan Breeze).
- Status tidak boleh hanya dibedakan lewat warna: tugas selesai juga dicoret dan diberi pill teks.

## 8. Yang tidak perlu dikerjakan

Tema gelap, animasi rumit, drag-and-drop, avatar gambar, dan library komponen tambahan.
Fokus ke kerapian dan konsistensi sesuai spesifikasi di atas.
