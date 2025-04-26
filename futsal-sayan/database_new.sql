-- Update tabel booking untuk menambahkan kolom hari_booking
ALTER TABLE booking
ADD COLUMN hari_booking ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu') DEFAULT NULL AFTER tanggal_akhir;
