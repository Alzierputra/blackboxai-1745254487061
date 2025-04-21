<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="relative bg-green-600 text-white py-16 mb-8">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl">
            <h1 class="text-4xl font-bold mb-4">Selamat Datang di Futsal Sayan Bekasi</h1>
            <p class="text-xl mb-8">Nikmati pengalaman bermain futsal dengan fasilitas terbaik dan harga terjangkau</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-100 transition duration-300">Daftar Sekarang</a>
            <?php else: ?>
                <a href="booking.php" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-100 transition duration-300">Booking Lapangan</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fasilitas Section -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">Fasilitas Kami</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <i class="fas fa-parking text-4xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Parkir Luas</h3>
            <p class="text-gray-600">Area parkir yang luas dan aman untuk kendaraan anda</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <i class="fas fa-shower text-4xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Kamar Mandi & Ruang Ganti</h3>
            <p class="text-gray-600">Fasilitas kamar mandi dan ruang ganti yang bersih</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <i class="fas fa-store text-4xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Kantin</h3>
            <p class="text-gray-600">Tersedia kantin dengan berbagai makanan dan minuman</p>
        </div>
    </div>
</div>

<!-- Daftar Lapangan -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">Lapangan Kami</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php
        $query = "SELECT * FROM lapangan";
        $result = mysqli_query($conn, $query);
        
        while($lapangan = mysqli_fetch_assoc($result)):
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="assets/images/<?php echo $lapangan['gambar']; ?>" alt="<?php echo $lapangan['nama']; ?>" class="w-full h-48 object-cover">
            <div class="p-6">
                <h3 class="text-xl font-semibold mb-2"><?php echo $lapangan['nama']; ?></h3>
                <p class="text-gray-600 mb-4"><?php echo $lapangan['deskripsi']; ?></p>
                <div class="flex justify-between items-center">
                    <span class="text-green-600 font-semibold">Rp <?php echo number_format($lapangan['harga_per_jam'], 0, ',', '.'); ?>/jam</span>
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                        <a href="booking.php?lapangan=<?php echo $lapangan['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-300">Booking</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Cara Booking Section -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">Cara Booking Lapangan</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">1</div>
            <h3 class="font-semibold mb-2">Daftar/Login</h3>
            <p class="text-gray-600">Buat akun atau login jika sudah memiliki akun</p>
        </div>
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">2</div>
            <h3 class="font-semibold mb-2">Pilih Lapangan</h3>
            <p class="text-gray-600">Pilih lapangan yang ingin anda sewa</p>
        </div>
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">3</div>
            <h3 class="font-semibold mb-2">Pilih Jadwal</h3>
            <p class="text-gray-600">Pilih tanggal dan jam yang tersedia</p>
        </div>
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">4</div>
            <h3 class="font-semibold mb-2">Pembayaran</h3>
            <p class="text-gray-600">Lakukan pembayaran via transfer atau COD</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
