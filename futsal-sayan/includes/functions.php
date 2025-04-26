<?php
// Fungsi untuk mengecek status lapangan pada tanggal dan jam tertentu
function cekStatusLapangan($conn, $lapangan_id, $tanggal, $jam) {
    $query = "SELECT b.*, u.nama as nama_user 
              FROM booking b 
              JOIN users u ON b.user_id = u.id 
              WHERE b.lapangan_id = '$lapangan_id' 
              AND b.tanggal_main = '$tanggal' 
              AND (
                  (b.jam_mulai <= '$jam' AND b.jam_selesai > '$jam')
                  OR
                  (b.jam_mulai > '$jam' AND b.jam_mulai < DATE_ADD('$jam', INTERVAL 1 HOUR))
              )
              AND b.status_pembayaran != 'dibatalkan'";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $booking = mysqli_fetch_assoc($result);
        $status = [
            'tersedia' => false,
            'keterangan' => 'Dibooking oleh ' . $booking['nama_user'],
            'jam_mulai' => $booking['jam_mulai'],
            'jam_selesai' => $booking['jam_selesai']
        ];
        
        // Cek apakah sedang digunakan saat ini
        $now = date('Y-m-d H:i:s');
        $booking_start = $booking['tanggal_main'] . ' ' . $booking['jam_mulai'];
        $booking_end = $booking['tanggal_main'] . ' ' . $booking['jam_selesai'];
        
        if ($now >= $booking_start && $now <= $booking_end) {
            $status['keterangan'] = 'Sedang digunakan oleh ' . $booking['nama_user'];
        }
        
        return $status;
    }
    
    return [
        'tersedia' => true,
        'keterangan' => 'Tersedia',
        'jam_mulai' => null,
        'jam_selesai' => null
    ];
}

// Fungsi untuk mendapatkan jadwal booking lapangan pada tanggal tertentu
function getJadwalLapangan($conn, $lapangan_id, $tanggal) {
    $query = "SELECT b.*, u.nama as nama_user 
              FROM booking b 
              JOIN users u ON b.user_id = u.id 
              WHERE b.lapangan_id = '$lapangan_id' 
              AND b.tanggal_main = '$tanggal' 
              AND b.status_pembayaran != 'dibatalkan'
              ORDER BY b.jam_mulai ASC";
    
    $result = mysqli_query($conn, $query);
    $jadwal = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $jadwal[] = [
            'nama_user' => $row['nama_user'],
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
            'status_pembayaran' => $row['status_pembayaran']
        ];
    }
    
    return $jadwal;
}

// Fungsi untuk format jam
function formatJam($jam) {
    return date('H:i', strtotime($jam));
}

/**
 * Menghitung harga promo berdasarkan tipe booking
 * @param float $harga_per_jam Harga per jam lapangan
 * @param string $jam_mulai Jam mulai booking (format HH:MM:SS)
 * @param string $jam_selesai Jam selesai booking (format HH:MM:SS)
 * @param string $tipe_booking Tipe booking: 'harian', 'mingguan', 'bulanan'
 * @return float Total harga setelah promo
 */
function calculatePromoPrice($harga_per_jam, $jam_mulai, $jam_selesai, $tipe_booking) {
    $start = strtotime($jam_mulai);
    $end = strtotime($jam_selesai);
    if ($end <= $start) {
        return 0; // atau lempar error
    }
    $jam = ceil(($end - $start) / 3600); // Durasi dalam jam
    
    // Perhitungan untuk harian (default)
    $total = $harga_per_jam * $jam;
    
    if ($tipe_booking === 'mingguan') {
        // Booking mingguan: berlaku untuk 7 hari dengan diskon 10%
        $total = $harga_per_jam * $jam * 7 * 0.90;
    }
    else if ($tipe_booking === 'bulanan') {
        // Booking bulanan: berlaku untuk 30 hari dengan diskon 15%
        $total = $harga_per_jam * $jam * 30 * 0.85;
    }
    return $total;
}
?>
