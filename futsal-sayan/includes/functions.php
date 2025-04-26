<?php
// Fungsi untuk format jam
function formatJam($jam) {
    return date('H:i', strtotime($jam));
}

/**
 * Menghitung harga promo berdasarkan tipe booking.
 * Untuk "harian": total = harga_per_jam * durasi (jam) * jumlah_booking.
 * Untuk "mingguan": total = harga_per_jam * durasi * (7 * jumlah_booking) * 0.90.
 * Untuk "bulanan":  total = harga_per_jam * durasi * (30 * jumlah_booking) * 0.85.
 */
function calculatePromoPrice($harga_per_jam, $jam_mulai, $jam_selesai, $tipe_booking, $jumlah_booking = 1) {
    $start = strtotime($jam_mulai);
    $end = strtotime($jam_selesai);
    if ($end <= $start) {
        return 0; // atau lempar error
    }
    $durasi = ceil(($end - $start) / 3600); // Durasi dalam jam

    if ($tipe_booking === 'mingguan') {
        $total = $harga_per_jam * $durasi * (7 * $jumlah_booking) * 0.90;
    } else if ($tipe_booking === 'bulanan') {
        $total = $harga_per_jam * $durasi * (30 * $jumlah_booking) * 0.85;
    } else { // harian
        $total = $harga_per_jam * $durasi * $jumlah_booking;
    }
    return $total;
}

/**
 * Mengecek apakah pembayaran dilakukan minimal 30 menit sebelum waktu mulai.
 * @param string $tanggal Tanggal booking (Y-m-d).
 * @param string $jam_mulai Jam mulai booking (HH:MM:SS).
 * @return bool True jika deadline sudah terlewati.
 */
function isPaymentDeadlinePassed($tanggal, $jam_mulai) {
    $bookingStart = strtotime($tanggal . ' ' . $jam_mulai);
    $deadline = $bookingStart - (30 * 60); // 30 menit sebelum mulai
    return (time() >= $deadline);
}

/**
 * Fungsi cekStatusLapangan untuk menampilkan status lapangan secara realtime.
 */
function cekStatusLapangan($conn, $lapangan_id, $tanggal, $jam) {
    $query = "SELECT b.*, u.nama as nama_user 
              FROM booking b 
              JOIN users u ON b.user_id = u.id 
              WHERE b.lapangan_id = '$lapangan_id' 
                AND '$tanggal' BETWEEN b.tanggal_main AND IFNULL(b.tanggal_akhir, b.tanggal_main)
                AND '$jam' BETWEEN b.jam_mulai AND b.jam_selesai
                AND b.status_pembayaran != 'dibatalkan'
              LIMIT 1";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}
?>
