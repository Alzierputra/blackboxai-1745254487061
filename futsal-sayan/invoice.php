 <?php
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    header("Location: index.php");
    exit();
}

$booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);
$user_id = $_SESSION['user_id'];

// Mengambil data booking
$query = "SELECT b.*, u.nama as nama_user, u.telepon, u.alamat, 
          l.nama as nama_lapangan, l.harga_per_jam 
          FROM booking b 
          JOIN users u ON b.user_id = u.id 
          JOIN lapangan l ON b.lapangan_id = l.id 
          WHERE b.id = '$booking_id' AND b.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: index.php");
    exit();
}
?>

<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8 my-8">
    <!-- Header Invoice -->
    <div class="border-b pb-4 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-green-600">INVOICE</h1>
                <p class="text-gray-600">No. Invoice: INV-<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></p>
                <p class="text-gray-600">Tanggal: <?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold">Futsal Sayan Bekasi</h2>
                <p class="text-gray-600">Jl. Contoh No. 123</p>
                <p class="text-gray-600">Bekasi</p>
                <p class="text-gray-600">Telp: 0812-3456-7890</p>
            </div>
        </div>
    </div>

    <!-- Informasi Pelanggan -->
    <div class="mb-6">
        <h3 class="font-bold mb-2">Informasi Pelanggan:</h3>
        <p>Nama: <?php echo $booking['nama_user']; ?></p>
        <p>Telepon: <?php echo $booking['telepon']; ?></p>
        <p>Alamat: <?php echo $booking['alamat']; ?></p>
    </div>

    <!-- Detail Booking -->
    <div class="mb-6">
        <h3 class="font-bold mb-2">Detail Booking:</h3>
        <table class="w-full mb-4">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-left">Deskripsi</th>
                    <th class="px-4 py-2 text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2 border-b">
                        <p class="font-semibold"><?php echo $booking['nama_lapangan']; ?></p>
                        <p class="text-sm text-gray-600">
                            Tanggal: <?php echo date('d/m/Y', strtotime($booking['tanggal_main'])); ?><br>
                            Waktu: <?php echo date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                                              date('H:i', strtotime($booking['jam_selesai'])); ?>
                        </p>
                    </td>
                    <td class="px-4 py-2 text-right border-b">
                        Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td class="px-4 py-2 text-right">Total:</td>
                    <td class="px-4 py-2 text-right">
                        Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Metode Pembayaran -->
    <div class="mb-6">
        <h3 class="font-bold mb-2">Metode Pembayaran:</h3>
        <p><?php echo $booking['metode_pembayaran'] == 'transfer' ? 'Transfer Bank' : 'Bayar di Tempat (COD)'; ?></p>
        
        <?php if($booking['metode_pembayaran'] == 'transfer'): ?>
        <div class="mt-2 p-4 bg-gray-50 rounded">
            <p class="font-semibold">Informasi Transfer:</p>
            <p><i class="fas fa-university mr-2"></i>Bank BCA</p>
            <p><i class="fas fa-credit-card mr-2"></i>No. Rekening: 1234567890</p>
            <p><i class="fas fa-user mr-2"></i>A.n: Futsal Sayan Bekasi</p>
            <p class="mt-2 text-sm text-gray-600">Mohon transfer sesuai nominal dan sertakan nomor booking pada berita transfer</p>
        </div>
        <?php elseif($booking['metode_pembayaran'] == 'qris'): ?>
        <div class="mt-2 p-4 bg-gray-50 rounded text-center">
            <p class="font-semibold mb-2">Scan QRIS untuk Pembayaran:</p>
            <p class="text-sm text-gray-600 mb-4">Pembayaran dapat dilakukan melalui:</p>
            <div class="flex justify-center space-x-4 mb-4">
                <span><i class="fas fa-wallet text-2xl"></i> OVO</span>
                <span><i class="fas fa-wallet text-2xl"></i> GoPay</span>
                <span><i class="fas fa-wallet text-2xl"></i> DANA</span>
                <span><i class="fas fa-wallet text-2xl"></i> ShopeePay</span>
            </div>
            <div class="flex justify-center mb-4">
                <img src="assets/images/qris/qris-code.png" alt="QRIS Code" class="w-48 h-48 border p-2">
            </div>
            <p class="text-sm text-gray-600">
                Nominal yang harus dibayar:<br>
                <span class="font-bold text-lg">Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></span>
            </p>
            <div class="mt-4 text-sm text-gray-600">
                <p>1. Buka aplikasi e-wallet Anda</p>
                <p>2. Pilih Scan QR/QRIS</p>
                <p>3. Scan QR Code di atas</p>
                <p>4. Masukkan nominal sesuai yang tertera</p>
                <p>5. Pembayaran akan dikonfirmasi otomatis</p>
            </div>
        </div>
        <?php elseif($booking['metode_pembayaran'] == 'cod'): ?>
        <div class="mt-2 p-4 bg-gray-50 rounded">
            <p class="font-semibold"><i class="fas fa-info-circle mr-2"></i>Informasi Pembayaran di Tempat:</p>
            <ul class="mt-2 space-y-2 text-gray-600">
                <li><i class="fas fa-clock mr-2"></i>Harap datang 30 menit sebelum jadwal main</li>
                <li><i class="fas fa-money-bill-wave mr-2"></i>Siapkan uang pas sebesar Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></li>
                <li><i class="fas fa-exclamation-triangle mr-2"></i>Booking akan dibatalkan jika tidak hadir 15 menit setelah jadwal mulai</li>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <!-- Status Pembayaran -->
    <div class="mb-6">
        <h3 class="font-bold mb-2">Status Pembayaran:</h3>
        <?php 
        $status_class = '';
        $status_text = '';
        $status_icon = '';
        
        switch($booking['status_pembayaran']) {
            case 'pending':
                $status_class = 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                $status_text = 'Menunggu Pembayaran';
                $status_icon = 'clock';
                break;
            case 'dikonfirmasi':
                $status_class = 'bg-green-100 text-green-800 border border-green-300';
                $status_text = 'Pembayaran Diterima';
                $status_icon = 'check-circle';
                break;
            case 'dibatalkan':
                $status_class = 'bg-red-100 text-red-800 border border-red-300';
                $status_text = 'Dibatalkan';
                $status_icon = 'times-circle';
                break;
        }
        ?>
        <div class="flex items-center space-x-2">
            <span class="px-4 py-2 rounded-full <?php echo $status_class; ?> flex items-center">
                <i class="fas fa-<?php echo $status_icon; ?> mr-2"></i>
                <?php echo $status_text; ?>
            </span>
            
            <?php if($booking['status_pembayaran'] == 'pending' && $booking['metode_pembayaran'] != 'cod'): ?>
                <span class="text-sm text-red-600">
                    Harap selesaikan pembayaran dalam 2 jam
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tombol Print -->
    <div class="flex justify-between items-center mt-8 pt-4 border-t print:hidden">
        <div class="text-sm text-gray-600">
            <p class="font-semibold">Invoice ini adalah bukti pembayaran yang sah.</p>
            <p>Silakan cetak atau simpan invoice ini.</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.history.back()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </button>
            <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-print mr-2"></i> Cetak Invoice
            </button>
        </div>
    </div>

    <!-- Watermark untuk status pembayaran saat print -->
    <div class="hidden print:block fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 -rotate-45 opacity-10 text-6xl font-bold text-<?php echo $booking['status_pembayaran'] == 'dikonfirmasi' ? 'green' : 'red'; ?>-600">
        <?php echo $booking['status_pembayaran'] == 'dikonfirmasi' ? 'LUNAS' : 'BELUM LUNAS'; ?>
    </div>
</div>

<!-- Style khusus untuk print -->
<style type="text/css" media="print">
    @page {
        size: auto;
        margin: 0mm;
    }
    
    nav, button, .container > *:not(.max-w-3xl) {
        display: none !important;
    }
    
    .max-w-3xl {
        max-width: none !important;
        margin: 0 !important;
        padding: 20px !important;
    }
    
    body {
        background: white !important;
    }
</style>

<?php include 'includes/footer.php'; ?>
