<?php 
include 'includes/header.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Ambil data booking user
$user_id = $_SESSION['user_id'];
$query = "SELECT b.*, l.nama as nama_lapangan, l.harga_per_jam 
          FROM booking b 
          JOIN lapangan l ON b.lapangan_id = l.id 
          WHERE b.user_id = '$user_id' 
          ORDER BY b.tanggal_booking DESC";
$result = mysqli_query($conn, $query);
?>

<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Riwayat Booking</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">No. Booking</th>
                            <th class="px-6 py-3 text-left">Lapangan</th>
                            <th class="px-6 py-3 text-left">Tanggal Main</th>
                            <th class="px-6 py-3 text-left">Waktu</th>
                            <th class="px-6 py-3 text-left">Total Harga</th>
                            <th class="px-6 py-3 text-left">Metode Pembayaran</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while($booking = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4"><?php echo $booking['nama_lapangan']; ?></td>
                                <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($booking['tanggal_main'])); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    echo date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                                         date('H:i', strtotime($booking['jam_selesai'])); 
                                    ?>
                                </td>
                                <td class="px-6 py-4">Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    echo $booking['metode_pembayaran'] == 'transfer' ? 'Transfer Bank' : 'Bayar di Tempat'; 
                                    ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch($booking['status_pembayaran']) {
                                        case 'pending':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            $status_text = 'Menunggu Pembayaran';
                                            break;
                                        case 'dikonfirmasi':
                                            $status_class = 'bg-green-100 text-green-800';
                                            $status_text = 'Pembayaran Diterima';
                                            break;
                                        case 'dibatalkan':
                                            $status_class = 'bg-red-100 text-red-800';
                                            $status_text = 'Dibatalkan';
                                            break;
                                    }
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-600">Anda belum memiliki riwayat booking.</p>
            <a href="booking.php" class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Booking Sekarang
            </a>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Informasi Pembayaran Transfer -->
<div class="max-w-4xl mx-auto mt-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-green-600 mb-4">Informasi Pembayaran Transfer</h3>
        <div class="space-y-2">
            <p><strong>Bank:</strong> Bank BCA</p>
            <p><strong>No. Rekening:</strong> 1234567890</p>
            <p><strong>Atas Nama:</strong> Futsal Sayan Bekasi</p>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <p>Catatan:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Mohon transfer sesuai dengan total harga booking</li>
                <li>Sertakan nomor booking pada berita transfer</li>
                <li>Konfirmasi pembayaran akan diproses dalam 1x24 jam</li>
                <li>Untuk pembayaran COD, silakan melakukan pembayaran di tempat minimal 30 menit sebelum jadwal main</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
