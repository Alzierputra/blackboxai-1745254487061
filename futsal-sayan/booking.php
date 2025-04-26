<?php 
include 'includes/header.php';
require_once 'includes/functions.php';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lapangan_id = mysqli_real_escape_string($conn, $_POST['lapangan_id']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $tipe_booking = isset($_POST['tipe_booking']) ? mysqli_real_escape_string($conn, $_POST['tipe_booking']) : 'harian';
    $jumlah_booking = isset($_POST['jumlah_booking']) ? intval($_POST['jumlah_booking']) : 1;
    $hari_booking = isset($_POST['hari_booking']) ? mysqli_real_escape_string($conn, $_POST['hari_booking']) : null;

    // Validasi pembayaran minimal 30 menit sebelum bermain
    if (isPaymentDeadlinePassed($tanggal, $jam_mulai)) {
        $error = "Booking tidak dapat dilakukan karena waktu pembayaran kurang dari 30 menit sebelum bermain.";
    } else {
        // Ambil harga lapangan
        $query = "SELECT harga_per_jam FROM lapangan WHERE id = '$lapangan_id'";
        $result = mysqli_query($conn, $query);
        $lapangan = mysqli_fetch_assoc($result);

        // Hitung tanggal akhir booking
        if ($tipe_booking == 'harian') {
            $tanggal_akhir = date('Y-m-d', strtotime($tanggal . " + " . ($jumlah_booking - 1) . " day"));
        } elseif ($tipe_booking == 'mingguan') {
            $tanggal_akhir = date('Y-m-d', strtotime($tanggal . " + " . ($jumlah_booking - 1) . " week"));
        } elseif ($tipe_booking == 'bulanan') {
            $tanggal_akhir = date('Y-m-d', strtotime($tanggal . " + " . ($jumlah_booking - 1) . " month"));
        } else {
            $tanggal_akhir = $tanggal;
        }

        // Hitung total harga dengan promo
        $total_harga = calculatePromoPrice($lapangan['harga_per_jam'], $jam_mulai, $jam_selesai, $tipe_booking, $jumlah_booking);

        // Cek ketersediaan lapangan untuk periode booking
        $check_query = "SELECT * FROM booking WHERE 
                        lapangan_id = '$lapangan_id' AND 
                        ((tanggal_main BETWEEN '$tanggal' AND '$tanggal_akhir') OR
                         (tanggal_akhir BETWEEN '$tanggal' AND '$tanggal_akhir')) AND
                        ((jam_mulai <= '$jam_mulai' AND jam_selesai > '$jam_mulai') OR
                         (jam_mulai < '$jam_selesai' AND jam_selesai >= '$jam_selesai') OR
                         (jam_mulai >= '$jam_mulai' AND jam_selesai <= '$jam_selesai')) AND
                        status_pembayaran != 'dibatalkan'";

        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Maaf, lapangan sudah dibooking untuk waktu tersebut dalam periode yang dipilih.";
        } else {
            // Simpan booking
            $user_id = $_SESSION['user_id'];
            $query = "INSERT INTO booking (user_id, lapangan_id, tanggal_main, jam_mulai, jam_selesai, 
                                         total_harga, metode_pembayaran, tipe_booking, jumlah_booking, tanggal_akhir, hari_booking) 
                      VALUES ('$user_id', '$lapangan_id', '$tanggal', '$jam_mulai', '$jam_selesai', 
                             '$total_harga', '$metode_pembayaran', '$tipe_booking', '$jumlah_booking', '$tanggal_akhir', '$hari_booking')";

            if (mysqli_query($conn, $query)) {
                $booking_id = mysqli_insert_id($conn);
                header("Location: invoice.php?booking_id=" . $booking_id);
                exit();
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
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

        <!-- Container untuk menampilkan jadwal -->
        <div id="jadwal-container" class="mb-4"></div>

        <!-- Pemilihan Jam -->
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
                <p class="text-sm text-gray-500 mt-1">Buka: 08:00 - 22:00</p>
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
                <p class="text-sm text-gray-500 mt-1">Tutup: 23:00</p>
            </div>
        </div>

        <!-- Tipe Booking -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="tipe_booking">
                Tipe Booking
            </label>
            <select id="tipe_booking" name="tipe_booking" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="harian" selected>Harian</option>
                <option value="mingguan">Mingguan (Diskon 10%)</option>
                <option value="bulanan">Bulanan (Diskon 15%)</option>
            </select>
        </div>

        <!-- Jumlah Booking -->
        <div id="jumlah_booking_container" class="hidden">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="jumlah_booking">
                Jumlah Booking
            </label>
            <select id="jumlah_booking" name="jumlah_booking" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <!-- Options akan diisi oleh JS -->
            </select>
        </div>

        <!-- Hari Booking (untuk mingguan/bulanan) -->
        <div id="hari_booking_container" class="hidden">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="hari_booking">
                Hari Booking
            </label>
            <select id="hari_booking" name="hari_booking" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="senin">Senin</option>
                <option value="selasa">Selasa</option>
                <option value="rabu">Rabu</option>
                <option value="kamis">Kamis</option>
                <option value="jumat">Jumat</option>
                <option value="sabtu">Sabtu</option>
                <option value="minggu">Minggu</option>
            </select>
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

<!-- Script untuk booking -->
<script src="assets/js/booking.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi jam booking
    document.getElementById('jam_mulai').addEventListener('change', function() {
        const jamMulai = this.value;
        const jamSelesai = document.getElementById('jam_selesai');
        
        // Set minimum jam selesai 1 jam setelah jam mulai
        const [hours, minutes] = jamMulai.split(':');
        const date = new Date();
        date.setHours(parseInt(hours));
        date.setMinutes(parseInt(minutes));
        date.setHours(date.getHours() + 1);
        
        const minJamSelesai = 
            String(date.getHours()).padStart(2, '0') + ':' +
            String(date.getMinutes()).padStart(2, '0');
        
        jamSelesai.min = minJamSelesai;
        
        // Reset jam selesai jika lebih awal dari jam mulai
        if (jamSelesai.value && jamSelesai.value <= jamMulai) {
            jamSelesai.value = minJamSelesai;
        }
    });

    // Batasi tanggal booking maksimal 7 hari ke depan
    const tanggalInput = document.getElementById('tanggal');
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 7);

    tanggalInput.min = today.toISOString().split('T')[0];
    tanggalInput.max = maxDate.toISOString().split('T')[0];

    // Cek jadwal saat halaman dimuat jika ada lapangan yang dipilih
    const lapanganId = document.getElementById('lapangan_id').value;
    if (lapanganId) {
        checkJadwal();
    }

    // Tampilkan/hide jumlah booking dan hari booking sesuai tipe booking
    const tipeBookingSelect = document.getElementById('tipe_booking');
    const jumlahBookingContainer = document.getElementById('jumlah_booking_container');
    const jumlahBookingSelect = document.getElementById('jumlah_booking');
    const hariBookingContainer = document.getElementById('hari_booking_container');

    function updateBookingOptions() {
        const tipe = tipeBookingSelect.value;
        if (tipe === 'harian') {
            jumlahBookingContainer.classList.remove('hidden');
            hariBookingContainer.classList.add('hidden');
            jumlahBookingSelect.innerHTML = '';
            for (let i = 1; i <= 7; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.text = i + (i === 1 ? ' Hari' : ' Hari');
                jumlahBookingSelect.appendChild(option);
            }
        } else if (tipe === 'mingguan') {
            jumlahBookingContainer.classList.remove('hidden');
            hariBookingContainer.classList.remove('hidden');
            jumlahBookingSelect.innerHTML = '';
            for (let i = 1; i <= 3; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.text = i + (i === 1 ? ' Minggu' : ' Minggu');
                jumlahBookingSelect.appendChild(option);
            }
        } else if (tipe === 'bulanan') {
            jumlahBookingContainer.classList.remove('hidden');
            hariBookingContainer.classList.remove('hidden');
            jumlahBookingSelect.innerHTML = '';
            for (let i = 1; i <= 6; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.text = i + (i === 1 ? ' Bulan' : ' Bulan');
                jumlahBookingSelect.appendChild(option);
            }
        }
    }

    tipeBookingSelect.addEventListener('change', updateBookingOptions);
    updateBookingOptions();
});
</script>

<?php include 'includes/footer.php'; ?>
