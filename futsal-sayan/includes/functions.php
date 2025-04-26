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
    } else {
        $total = $harga_per_jam * $durasi * $jumlah_booking;
    }
    return $total;
}

/**
 * Mengecek apakah pembayaran dilakukan minimal 30 menit sebelum jam mulai.
 * Jika waktu sekarang melewati batas (jam mulai - 30 menit), maka fungsi mengembalikan true.
 */
function isPaymentDeadlinePassed($tanggal, $jam_mulai) {
    $bookingStart = strtotime($tanggal . ' ' . $jam_mulai);
    $deadline = $bookingStart - (30 * 60); // 30 menit sebelum mulai
    return (time() >= $deadline);
}
?>
