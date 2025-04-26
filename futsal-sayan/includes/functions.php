<?php
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
?>
