# Website Futsal Sayan Bekasi

Sistem informasi pemesanan lapangan futsal berbasis web untuk Futsal Sayan Bekasi.

## Fitur

- Sistem registrasi dan login user
- Booking lapangan futsal
- Pembayaran via transfer dan COD
- Riwayat booking untuk user
- Dashboard admin untuk mengelola booking
- Konfirmasi pembayaran oleh admin
- Manajemen lapangan oleh admin

## Teknologi yang Digunakan

- PHP Native
- MySQL Database
- Tailwind CSS untuk styling
- Font Awesome Icons
- Google Fonts (Poppins)

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)

## Cara Instalasi

1. Clone atau download repository ini
2. Buat database baru dengan nama `db_futsal_sayan`
3. Import database menggunakan file `database.sql`
4. Sesuaikan konfigurasi database di file `config/database.php`:
   ```php
   $host = "localhost";
   $username = "root";
   $password = "";
   $database = "db_futsal_sayan";
   ```
5. Pastikan folder `assets/images` memiliki permission yang sesuai
6. Akses website melalui web browser

## Akun Default

### Admin
- Username: admin
- Password: admin123

## Struktur Database

### Tabel users
- id (Primary Key)
- nama
- telepon
- alamat
- username
- password
- role (user/admin)

### Tabel lapangan
- id (Primary Key)
- nama
- deskripsi
- harga_per_jam
- gambar
- status (tersedia/maintenance)

### Tabel booking
- id (Primary Key)
- user_id (Foreign Key)
- lapangan_id (Foreign Key)
- tanggal_main
- jam_mulai
- jam_selesai
- total_harga
- metode_pembayaran (cod/transfer)
- status_pembayaran (pending/dikonfirmasi/dibatalkan)
- tanggal_booking

## Alur Penggunaan

### User
1. User melakukan registrasi akun
2. Login menggunakan akun yang telah dibuat
3. Pilih lapangan yang tersedia
4. Isi form booking (tanggal, jam, metode pembayaran)
5. Jika memilih transfer, lakukan pembayaran ke rekening yang tertera
6. Cek status booking di halaman riwayat

### Admin
1. Login menggunakan akun admin
2. Kelola data lapangan (tambah, edit status)
3. Konfirmasi pembayaran user
4. Monitor booking melalui dashboard

## Informasi Pembayaran Transfer

Bank: BCA
No. Rekening: 1234567890
Atas Nama: Futsal Sayan Bekasi

## Jam Operasional

- Senin - Jumat: 08.00 - 23.00
- Sabtu - Minggu: 07.00 - 23.00

## Kontak

- Alamat: Jl. Contoh No. 123, Bekasi
- Telepon: 0812-3456-7890
- Email: info@futsalsayan.com

## Pengembangan Selanjutnya

1. Implementasi sistem notifikasi (Email/WhatsApp)
2. Integrasi payment gateway
3. Sistem membership
4. Booking paket turnamen
5. Sistem point reward

## Lisensi

© 2024 Futsal Sayan Bekasi. All rights reserved.
