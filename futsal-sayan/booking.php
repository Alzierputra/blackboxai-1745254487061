<?php 
include 'includes/header.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Ambil data lapangan yang dipilih jika ada
$selected_lapangan = null;
if (isset($_GET['lapangan'])) {
    $lapangan_id = mysqli_real_escape_string($conn, $_GET['lapangan']);
    $query = "SELECT * FROM lapangan WHERE id = '$lapangan_id'";
    $result = mysqli_query($conn, $query);
    $selected_lapangan = mysqli_fetch_assoc($result);
}

// Proses booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lapangan_id = mysqli_real_escape_string($conn, $_POST['lapangan_id']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    
    // Hitung durasi dan total harga
    $start = strtotime($jam_mulai);
    $end = strtotime($jam_selesai);
    $durasi = ceil(($end - $start) / 3600); // dalam jam
    
    // Ambil harga lapangan
    $query = "SELECT harga_per_jam FROM lapangan WHERE id = '$lapangan_id'";
    $result = mysqli_query($conn, $query);
    $lapangan = mysqli_fetch_assoc($result);
    $total_harga = $durasi * $lapangan['harga_per_jam'];
    
    // Cek ketersediaan lapangan
    $check_query = "SELECT * FROM booking WHERE 
                    lapangan_id = '$lapangan_id' AND 
                    tanggal_main = '$tanggal' AND 
                    ((jam_mulai <= '$jam_mulai' AND jam_selesai > '$jam_mulai') OR
                    (jam_mulai < '$jam_selesai' AND jam_selesai >= '$jam_selesai') OR
                    (jam_mulai >= '$jam_mulai' AND jam_selesai <= '$jam_selesai'))";
    
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Maaf, lapangan sudah dibooking untuk waktu tersebut.";
    } else {
        // Simpan booking
        $user_id = $_SESSION['user_id'];
        $query = "INSERT INTO booking (user_id, lapangan_id, tanggal_main, jam_mulai, jam_selesai, 
                                     total_harga, metode_pembayaran) 
                  VALUES ('$user_id', '$lapangan_id', '$tanggal', '$jam_mulai', '$jam_selesai', 
                         '$total_harga', '$metode_pembayaran')";
        
        if (mysqli_query($conn, $query)) {
            $booking_id = mysqli_insert_id($conn);
            header("Location: invoice.php?booking_id=" . $booking_id);
            exit();
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

// Ambil semua data lapangan
$query = "SELECT * FROM lapangan WHERE status = 'tersedia'";
$lapangan_result = mysqli_query($conn, $query);
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Booking Lapangan</h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="lapangan_id">
                Pilih Lapangan
            </label>
            <select class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="lapangan_id" 
                    name="lapangan_id" 
                    required>
                <option value="">Pilih Lapangan</option>
                <?php while($lapangan = mysqli_fetch_assoc($lapangan_result)): ?>
                    <option value="<?php echo $lapangan['id']; ?>" 
                            <?php echo ($selected_lapangan && $selected_lapangan['id'] == $lapangan['id']) ? 'selected' : ''; ?>>
                        <?php echo $lapangan['nama']; ?> - Rp <?php echo number_format($lapangan['harga_per_jam'], 0, ',', '.'); ?>/jam
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal">
                Tanggal Main
            </label>
            <input class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="tanggal" 
                   type="date" 
                   name="tanggal" 
                   min="<?php echo date('Y-m-d'); ?>"
                   required>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="jam_mulai">
                    Jam Mulai
                </label>
                <input class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="jam_mulai" 
                       type="time" 
                       name="jam_mulai" 
                       min="08:00" 
                       max="22:00"
                       required>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="jam_selesai">
                    Jam Selesai
                </label>
                <input class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="jam_selesai" 
                       type="time" 
                       name="jam_selesai" 
                       min="09:00" 
                       max="23:00"
                       required>
            </div>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Metode Pembayaran
            </label>
            <div class="space-y-2">
                <label class="block">
                    <input type="radio" name="metode_pembayaran" value="transfer" required> 
                    <i class="fas fa-university mr-2"></i>Transfer Bank
                </label>
                <label class="block">
                    <input type="radio" name="metode_pembayaran" value="qris" required> 
                    <i class="fas fa-qrcode mr-2"></i>QRIS (OVO, GoPay, Dana, dll)
                </label>
                <label class="block">
                    <input type="radio" name="metode_pembayaran" value="cod" required> 
                    <i class="fas fa-money-bill-wave mr-2"></i>Bayar di Tempat (COD)
                </label>
            </div>
        </div>

        <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                type="submit">
            Booking Sekarang
        </button>
    </form>
</div>

<script>
document.getElementById('jam_mulai').addEventListener('change', function() {
    document.getElementById('jam_selesai').min = this.value;
});
</script>

<?php include 'includes/footer.php'; ?>
