# 🍽️ FoodFinder - Aplikasi Pencarian & Rekomendasi Tempat Makan

Selamat datang di repositori resmi **FoodFinder**, proyek perangkat lunak berbasis website untuk membantu mahasiswa dan masyarakat umum menemukan tempat makan, kafe, dan spot kuliner terbaik sesuai preferensi, budget, suasana, rating, dan kategori makanan.

Proyek ini dibangun menggunakan **PHP Native** dan **MySQL** yang dirancang modular, aman, dan dapat dijalankan langsung di server lokal **XAMPP** tanpa memerlukan instalasi framework pihak ketiga atau dependensi berbayar. Antarmuka mengusung konsep desain modern bertema **Pink & White Clean Minimalist** dengan pengalaman pengguna (UX) yang responsif dan interaktif.

---

## 📑 Daftar Isi
1. [Deskripsi Proyek](#1-deskripsi-proyek)
2. [Tujuan dan Manfaat Aplikasi](#2-tujuan-dan-manfaat-aplikasi)
3. [Daftar Fitur Lengkap](#3-daftar-fitur-lengkap)
4. [Teknologi yang Digunakan](#4-teknologi-yang-digunakan)
5. [Struktur Folder Proyek](#5-struktur-folder-proyek)
6. [Cara Menginstal dan Menjalankan melalui XAMPP](#6-cara-menginstal-dan-menjalankan-melalui-xampp)
7. [Cara Membuat dan Mengimpor Database](#7-cara-membuat-dan-mengimpor-database)
8. [Cara Mengakses Website](#8-cara-mengakses-website)
9. [Cara Menyiapkan Akun Admin Secara Aman](#9-cara-menyiapkan-akun-admin-secara-aman)
10. [Skenario Pengujian dan Keterbatasan Aplikasi](#10-skenario-pengujian-dan-keterbatasan-aplikasi)

---

## 1. Deskripsi Proyek
**FoodFinder** adalah solusi perangkat lunak yang menjembatani pencari kuliner dengan aneka pilihan tempat makan terpercaya. Di kehidupan kampus maupun perkantoran, sering kali muncul pertanyaan: *"Lagi pengin makan apa hari ini?"*, *"Tempat mana yang cocok buat ngerjain tugas (WFC) dengan colokan dan WiFi kencang?"*, atau *"Di mana tempat makan siang enak yang ramah di kantong akhir bulan?"*. 

FoodFinder memecahkan persoalan ini dengan menyediakan sistem pencarian multi-parameter dan sistem rekomendasi pencocokan preferensi cerdas.

---

## 2. Tujuan dan Manfaat Aplikasi

### Tujuan:
- Memberikan kemudahan bagi pengguna dalam mencari referensi tempat makan secara cepat dan akurat.
- Menyediakan sistem rekomendasi berbasis filter preferensi pengguna (kategori, budget min-max, suasana, rating).
- Menyediakan wadah ulasan jujur dan penilaian objektif dari sesama pengunjung.
- Memberikan platform bagi pemilik usaha kuliner (UMKM) untuk terdata dan dikenal lebih luas.

### Manfaat:
- **Bagi Mahasiswa & Pengguna:** Menghemat waktu pencarian, menghindari salah pilih tempat makan, dan mengontrol pengeluaran sesuai budget.
- **Bagi Pengelola/Admin:** Mempermudah kurasi direktori kuliner melalui panel manajemen yang praktis dan terstruktur.

---

## 3. Daftar Fitur Lengkap

### A. Fitur Pengguna Publik & Terdaftar
1. **Beranda Interaktif (Home):**
   - Hero banner dengan bilah pencarian cepat (*quick search bar*).
   - Tag pencarian populer (Kafe WFC, Geprek, Ramen, Gelato, Bebek).
   - Grid kategori makanan dengan ikon representatif.
   - Seksi tempat makan populer, rekomendasi pilihan kurator, dan rating tertinggi.
2. **Pencarian & Penjelajahan (Search & Explore):**
   - Pencarian berdasarkan nama tempat makan, jenis makanan, atau menu.
   - Filter dinamis berdasarkan kategori kuliner.
   - Filter rentang harga / budget (Pelajar < Rp 25.000, Rp 25.000–Rp 50.000, dsb.).
   - Filter suasana tempat (*Santai*, *Cocok Nugas / WFC*, *Keluarga*, *Romantis*).
   - Filter rating bintang minimum (4.0+, 4.5+).
   - Pengurutan (*sorting*) berdasarkan Rating Tertinggi, Harga Termurah, Harga Termahal, dan Terbaru.
3. **Detail Tempat Makan (Restaurant Detail):**
   - Galeri foto tempat makan & menu.
   - Informasi jam operasional, kisaran harga, alamat lengkap, dan suasana.
   - Daftar fasilitas bertag ikon (WiFi, Stopkontak, AC, Mushola, Area Merokok, dll.).
   - Daftar menu andalan beserta harga resmi Rupiah.
   - Tombol penunjuk arah langsung ke **Google Maps**.
   - Daftar ulasan pengunjung beserta nilai rating bintang.
   - Formulir kirim ulasan interaktif dengan *star picker* (khusus pengguna yang login).
4. **Sistem Rekomendasi Pintar (Recommendation Engine):**
   - Pencocokan preferensi multi-faktor (*rule-based preference matching*) sesuai kategori, budget maksimum, suasana, dan standar rating.
   - Fitur **Rekomendasi Acak (*Surprise Me!*)** untuk pengguna yang ingin mencoba kuliner baru secara spontan.
5. **Koleksi Tempat Favorit (Wishlist / Bookmarks):**
   - Menambahkan dan menghapus tempat makan ke favorit secara instan menggunakan teknologi **AJAX** tanpa reload halaman.
   - Halaman khusus daftar favorit tersimpan aman berdasarkan akun masing-masing pengguna.
6. **Autentikasi Pengguna:**
   - Registrasi akun baru dengan validasi format email, keunikan data, dan panjang password.
   - Login dengan session management yang aman dan proteksi fiksasi sesi.
   - Logout bersih dengan penghapusan session cookie.
   - Tombol pengisian cepat (*quick-fill demo credentials*) untuk kenyamanan pengujian dosen dan mahasiswa.

### B. Fitur Panel Administrator (`/admin/`)
1. **Dashboard Metrik & Statistik:**
   - Kartu metrik: Total Tempat Makan, Total Pengguna, Total Kategori, dan Total Ulasan.
   - Tabel ringkasan tempat makan rating tertinggi.
   - Widget pemantauan ulasan pengunjung terbaru.
2. **Manajemen Tempat Makan (CRUD):**
   - Menambah data tempat makan baru dengan upload foto aman (validasi MIME, ekstensi, batas ukuran 2MB).
   - Mengedit informasi tempat makan, fasilitas, harga, dan mengganti foto.
   - Menghapus tempat makan dengan modal konfirmasi aman (*HTTP POST request*).
   - Mengatur status publikasi (*Published* / *Draft*).
3. **Manajemen Kategori Kuliner:**
   - Menambah, mengedit, dan menghapus kategori kuliner.
   - Pengamanan penghapusan: mencegah penghapusan kategori jika masih digunakan oleh tempat makan aktif.
4. **Manajemen Pengguna:**
   - Menampilkan daftar akun terdaftar, peran (*role: admin / user*), total ulasan, dan tanggal bergabung.
5. **Moderasi Ulasan:**
   - Meninjau semua ulasan pengunjung.
   - Menghapus ulasan tidak pantas atau berindikasi spam.
   - **Otomatisasi**: Sistem otomatis menghitung ulang rating rata-rata tempat makan setiap kali ulasan baru masuk atau ulasan dihapus.

---

## 4. Teknologi yang Digunakan

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Front-end** | HTML5, CSS3 murni (Vanilla), JavaScript Vanilla (ES6+) | Tanpa framework CSS pihak ketiga yang berat untuk performa kilat |
| **Back-end** | PHP Native (versi 8.0 / 8.1 / 8.2+) | Arsitektur modular, terstruktur, dan mudah dipelajari |
| **Database** | MySQL (MariaDB) | Menggunakan PDO dengan Prepared Statements |
| **Web Server** | Apache HTTP Server (via XAMPP) | Port standar 80 / 443 |
| **Tipografi** | Poppins & Plus Jakarta Sans | Google Fonts CDN |
| **Ikonografi** | Font Awesome 6 (Free) | Font Awesome CDN |
| **Tema Warna** | Pink & White Aesthetic | `#F05A9D`, `#D83F83`, `#FFF0F6`, `#FFFFFF`, `#33252C` |

---

## 5. Struktur Folder Proyek

```text
foodfinder/
│
├── index.php                 # Halaman utama (Beranda)
├── search.php                # Halaman pencarian & filter kuliner
├── restaurant-detail.php     # Halaman detail restoran, menu & ulasan
├── recommendation.php        # Halaman rekomendasi preferensi & acak
├── favorites.php             # Halaman daftar favorit pengguna
├── about.php                 # Halaman tentang aplikasi, visi misi & FAQ
├── login.php                 # Halaman masuk (login)
├── register.php              # Halaman pendaftaran akun baru
├── logout.php                # Script logout aman
│
├── config/
│   └── database.php          # Konfigurasi koneksi PDO & Base URL dinamis
│
├── includes/
│   ├── header.php            # Template header HTML, font, & styling
│   ├── navbar.php            # Navigasi responsif & dropdown profil
│   ├── footer.php            # Template footer informasi & toast
│   └── functions.php         # Fungsi pembantu (XSS, auth, upload, rating)
│
├── ajax/
│   └── toggle-favorite.php   # Endpoint async AJAX untuk tombol favorit
│
├── admin/
│   ├── auth_check.php        # Gatekeeper hak akses Administrator
│   ├── sidebar.php           # Navigasi sidebar admin
│   ├── topbar.php            # Header panel admin
│   ├── index.php             # Dashboard statistik utama
│   ├── restaurants.php       # Kelola data tempat makan
│   ├── restaurant-add.php    # Formulir tambah tempat makan
│   ├── restaurant-edit.php   # Formulir edit tempat makan
│   ├── categories.php        # Kelola kategori kuliner
│   ├── users.php             # Daftar data pengguna
│   └── reviews.php           # Moderasi ulasan pengunjung
│
├── assets/
│   ├── css/
│   │   ├── style.css         # Desain sistem pink & putih modern
│   │   └── admin.css         # Desain antarmuka panel admin
│   ├── js/
│   │   └── script.js         # Logika interaktif, AJAX, toast, filter
│   └── images/               # Foto kategori & aset grafis
│
├── uploads/
│   ├── restaurants/          # Direktori penyimpanan foto restoran
│   └── menus/                # Direktori foto menu (opsional)
│
├── database/
│   └── foodfinder_db.sql     # Skema database & data contoh (demo seed)
│
└── README.md                 # Dokumentasi lengkap proyek
```

---

## 6. Cara Menginstal dan Menjalankan melalui XAMPP

1. **Unduh dan Pasang XAMPP:**
   Pastikan XAMPP telah terpasang di komputer Anda (versi PHP 8.0 ke atas direkomendasikan).
2. **Salin Folder Proyek:**
   Tempatkan folder proyek ini ke dalam direktori web server XAMPP:
   - Lokasi standar: `C:\xampp\htdocs\foodfinder` (atau `C:\xampp\htdocs\FoodFinderPPL`)
3. **Jalankan Layanan XAMPP:**
   - Buka aplikasi **XAMPP Control Panel**.
   - Klik tombol **Start** pada modul **Apache**.
   - Klik tombol **Start** pada modul **MySQL**.
   - Pastikan kedua modul berwarna hijau dengan status *Running*.

---

## 7. Cara Membuat dan Mengimpor Database

Terdapat 2 cara mudah untuk menyiapkan database:

### Opsi A: Melalui phpMyAdmin (Antarmuka Web)
1. Buka browser dan akses `http://localhost/phpmyadmin/`.
2. Klik tab **Databases** (Basis Data).
3. Buat database baru dengan nama `foodfinder_db` (Collation: `utf8mb4_unicode_ci`), lalu klik **Create**.
4. Pilih database `foodfinder_db` pada panel sebelah kiri.
5. Klik tab **Import** pada menu atas.
6. Klik tombol **Choose File** / Telusuri, lalu pilih file:
   `C:\xampp\htdocs\foodfinder\database\foodfinder_db.sql`
7. Gulir ke bawah dan klik tombol **Import**.
8. Tunggu hingga muncul notifikasi hijau bahwa seluruh tabel dan data demo berhasil diimpor.

### Opsi B: Melalui Terminal / Command Prompt
Jalankan perintah berikut:
```bash
# Masuk ke direktori mysql xampp
cd C:\xampp\mysql\bin

# Eksekusi file SQL
mysql.exe -u root < "C:\xampp\htdocs\foodfinder\database\foodfinder_db.sql"
```

---

## 8. Cara Mengakses Website

Setelah Apache dan MySQL berjalan serta database telah diimpor:
- **Aplikasi Pengguna (Website Utama):**
  Buka browser dan kunjungi:
  [http://localhost/FoodFinderPPL/](http://localhost/FoodFinderPPL/) atau [http://localhost/foodfinder/](http://localhost/foodfinder/)
- **Halaman Masuk (Login):**
  [http://localhost/FoodFinderPPL/login.php](http://localhost/FoodFinderPPL/login.php)
- **Halaman Registrasi Akun:**
  [http://localhost/FoodFinderPPL/register.php](http://localhost/FoodFinderPPL/register.php)
- **Halaman Panel Admin:**
  [http://localhost/FoodFinderPPL/admin/index.php](http://localhost/FoodFinderPPL/admin/index.php)

---

## 9. Cara Menyiapkan Akun Admin Secara Aman

Aplikasi telah dilengkapi dengan data demo untuk memudahkan evaluasi dan pengujian dosen penguji maupun mahasiswa:

### Kredensial Demo Bawaan:
- **Akun Administrator:**
  - Email: `admin@foodfinder.com`
  - Kata Sandi: `admin123`
  - Akses: Penuh ke seluruh panel admin (`/admin/`) dan website publik.
- **Akun Pengguna Biasa (Demo):**
  - Email: `user@foodfinder.com`
  - Kata Sandi: `user123`
  - Akses: Jelajahi, simpan favorit, dan tulis ulasan.

> 💡 **Tip:** Pada halaman `login.php`, terdapat tombol praktis **"Akun Admin"** dan **"Akun User"** yang dapat diklik untuk mengisi email dan kata sandi demo secara instan.

### Menambahkan / Mengubah Akun Admin Baru Secara Aman:
Jika ingin membuat akun admin baru:
1. Daftarkan akun baru melalui halaman `register.php`.
2. Buka phpMyAdmin di `http://localhost/phpmyadmin/`.
3. Masuk ke tabel `users` pada database `foodfinder_db`.
4. Ubah nilai kolom `role` pada baris pengguna tersebut dari `'user'` menjadi `'admin'`.
5. Pengguna tersebut kini memiliki hak akses penuh ke panel admin.
6. Seluruh kata sandi dienkripsi menggunakan algoritma standar industri `PASSWORD_BCRYPT`.

---

## 10. Skenario Pengujian dan Keterbatasan Aplikasi

### A. Skenario Pengujian yang Telah Dilakukan:
1. **Pengujian Autentikasi & Otorisasi:**
   - Pendaftaran akun baru dengan validasi email duplikat, password pendek (< 6 huruf), dan konfirmasi password tidak cocok.
   - Login dengan kombinasi email/password salah menampilkan notifikasi bahaya dalam bahasa Indonesia.
   - Proteksi URL: mengakses `/admin/index.php` tanpa login sebagai admin otomatis memicu redirect ke login dengan peringatan penolakan akses.
2. **Pengujian Fitur Favorit (AJAX):**
   - Mengklik ikon hati pada card tempat makan mengirim request asinkron ke `ajax/toggle-favorite.php`.
   - Data langsung tersimpan di tabel `favorites` tanpa merefresh browser.
   - Mengklik kembali menghapus tempat makan dari favorit.
   - Pengguna belum login otomatis diarahkan ke halaman login.
3. **Pengujian Algoritma Rekomendasi:**
   - Filter budget maksimum Rp 25.000 hanya menampilkan makanan hemat (seperti Ayam Geprek Juara).
   - Filter suasana "Cocok Tugas / WFC" mengutamakan kafe dengan fasilitas stopkontak dan WiFi cepat.
   - Fitur "Rekomendasi Acak" mengembalikan tempat makan berkualitas tinggi dengan rating di atas 4.5.
4. **Pengujian Ulasan & Hitung Ulang Rating Otomatis:**
   - Pengunjung mengirim ulasan baru dengan rating 5 bintang.
   - Fungsi `update_restaurant_rating_stats()` secara otomatis menghitung ulang rata-rata bintang dan total ulasan pada tabel `restaurants`.
   - Admin menghapus ulasan spam di panel moderasi, dan rata-rata rating restoran langsung tersinkronisasi kembali.
5. **Pengujian Upload Gambar:**
   - Memeriksa penolakan file non-gambar (seperti `.exe` atau `.pdf`) dan file melebihi 2MB.
   - File gambar yang sah disimpan dengan nama unik acak (`bin2hex`) untuk mencegah konflik nama dan eksekusi skrip berbahaya.

### B. Keterbatasan Aplikasi Saat Ini & Rencana Pengembangan:
- **Peta Lokasi:** Saat ini tautan peta mengarahkan langsung ke Google Maps resmi berdasarkan koordinat dan nama tempat. Integrasi Mapbox / Google Maps JavaScript API interaktif dapat ditambahkan pada iterasi berikutnya.
- **Reservasi Meja:** Sistem saat ini berfokus pada direktori pencarian dan rekomendasi, belum mencakup transaksi pembayaran reservasi meja atau pemesanan makanan secara online.
- **Notifikasi Real-time:** Belum menggunakan WebSocket untuk notifikasi ulasan masuk secara live, melainkan menggunakan AJAX dan polling standar.

---

## 👩‍💻 Tim Pengembang
Proyek ini dikembangkan dengan dedikasi tinggi sebagai implementasi praktis mata kuliah **Rekayasa Perangkat Lunak (RPL)**.

*Semoga aplikasi FoodFinder bermanfaat bagi para pencinta kuliner dan mahasiswa dalam menemukan santapan terbaik setiap harinya!*
