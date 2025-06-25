# Panel Admin E-commerce "Rewalk Store"

##  Deskripsi Proyek

Rewalk Store adalah sebuah proyek **Panel Admin** yang fungsional dan modern untuk sebuah toko online (e-commerce) yang berfokus pada penjualan sepatu. Proyek ini dibangun sebagai bagian dari Uji Kompetensi untuk sertifikasi Junior Web Developer dan menampilkan berbagai implementasi praktik pengembangan web modern, mulai dari keamanan backend, logika bisnis yang kompleks, hingga pengalaman pengguna (UX) yang interaktif.

Aplikasi ini memungkinkan seorang admin untuk mengelola seluruh aspek operasional toko, mulai dari data master produk hingga menganalisis laporan penjualan.

## Fitur Unggulan

Proyek ini tidak hanya sekadar aplikasi CRUD biasa, tetapi juga dilengkapi dengan fitur-fitur profesional yang menunjukkan pemahaman mendalam tentang arsitektur aplikasi web:

#### Dashboard & Pelaporan
* **Dashboard Analitik:** Menampilkan KPI utama (Pendapatan, Jumlah Produk, dll).
* **Grafik Interaktif:** Grafik pendapatan dinamis dengan filter rentang waktu (7 Hari, 30 Hari, 6 Bulan) yang di-update menggunakan **AJAX** tanpa me-reload halaman.
* **Laporan Penjualan Profesional:** Fitur untuk memfilter laporan berdasarkan tanggal dan mengekspor data ke dalam format file **`.xlsx`** yang rapi dan ber-style menggunakan library **PhpSpreadsheet**.

#### Manajemen Toko & Logika Bisnis
* **Manajemen Produk & Stok:** Operasi CRUD penuh untuk produk dan manajemen stok yang cepat via AJAX.
* **Ongkir Dinamis:** Biaya pengiriman dihitung secara otomatis berdasarkan **total berat** produk dalam sebuah pesanan.
* **Sistem Kupon Diskon:** Admin bisa membuat kupon dengan tipe potongan harga persen (%) atau tetap (Rp), yang kemudian bisa diterapkan saat membuat pesanan manual.
* **Manajemen Kategori, Customer, & Banner:** Fitur CRUD yang solid dengan UX modern menggunakan modal pop-up.

#### Keamanan & Performa
* **Keamanan Database:** Mencegah **SQL Injection** dengan menggunakan **PDO dan Prepared Statements** untuk semua query database.
* **Keamanan Output:** Mencegah **XSS (Cross-Site Scripting)** dengan menggunakan `htmlspecialchars()` pada semua data yang ditampilkan.
* **Integritas Data:** Menggunakan **Transaksi Database** (`beginTransaction`, `commit`, `rollBack`) untuk operasi yang melibatkan banyak tabel, seperti saat memproses pesanan.
* **Optimasi Gambar:** Gambar yang di-upload (untuk produk dan banner) secara otomatis di-**resize dan dikompres** menggunakan PHP GD Library untuk mempercepat waktu muat dan menghemat storage.

#### Teknologi & UX
* **Tampilan Responsif:** Dibangun dengan **Tailwind CSS** untuk memastikan tampilan yang optimal di berbagai perangkat, dari desktop hingga mobile.
* **Pengalaman Pengguna Modern:** Interaksi yang mulus menggunakan **modal pop-up**, notifikasi **SweetAlert2**, dan update data instan tanpa refresh halaman.

## Teknologi yang Digunakan

* **Backend:** PHP
* **Database:** MySQL / MariaDB (dengan koneksi via PDO)
* **Frontend:** HTML, Tailwind CSS, JavaScript, jQuery
* **Library:**
    * [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) (diinstal via Composer)
    * [Chart.js](https://www.chartjs.org/)
    * [SweetAlert2](https://sweetalert2.github.io/)
    * [Font Awesome](https://fontawesome.com/)
* **Development Environment:** XAMPP

## Instalasi & Konfigurasi

Untuk menjalankan proyek ini secara lokal:

1.  **Clone Repository**
    ```bash
    git clone [URL_REPOSITORY_ANDA]
    ```
2.  **Database**
    * Buat database baru di phpMyAdmin dengan nama `db_toko_sepatu`.
    * Impor file `db_toko_sepatu.sql` yang ada di dalam repository ke dalam database yang baru Anda buat.

3.  **Konfigurasi Koneksi**
    * Buka file `config/database.php`.
    * Sesuaikan nilai `$host`, `$dbname`, `$user`, dan `$pass` dengan konfigurasi XAMPP Anda.

4.  **Install Dependencies**
    * Pastikan Anda sudah menginstal [Composer](https://getcomposer.org/).
    * Buka terminal/CMD, arahkan ke folder proyek, lalu jalankan perintah:
        ```bash
        composer install
        ```
    * Ini akan membuat folder `vendor` dan mengunduh library PhpSpreadsheet.

5.  **Jalankan Proyek**
    * Letakkan folder proyek di dalam direktori `htdocs` XAMPP Anda.
    * Akses proyek melalui browser, misalnya: `http://localhost/tokosepatu/`

## Akun Demo

Anda bisa login ke panel admin menggunakan kredensial berikut:

-   **Email:** `admin@rewalk.com`
-   **Password:** `password`

*(Catatan: Anda bisa mengubah ini atau menambah user baru di tabel `users` di database).*

## Screenshot

**(Saran: Buat folder bernama `screenshots` di proyek Anda, letakkan gambar-gambar di bawah ini di sana, lalu sesuaikan path-nya)**

![Dashboard](screenshots/dashboard.png)
_Tampilan Dashboard yang interaktif._

![Laporan](screenshots/laporan.png)
_Halaman Laporan Penjualan dengan filter dan tombol ekspor._

![Manajemen Stok](screenshots/stok.png)
_Manajemen Stok dengan update via AJAX._

![Invoice](screenshots/invoice.png)
_Contoh Invoice profesional yang dihasilkan oleh sistem._

---

*Proyek ini dikembangkan sebagai bagian dari Uji Kompetensi dan Tugas Akhir Semester.*
*Dibuat oleh: **[TULIS NAMA LENGKAP ANDA DI SINI]***
