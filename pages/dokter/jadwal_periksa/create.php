<?php
// Menghubungkan file konfigurasi database
include_once "../../../config/conn.php";

// Memulai sesi
session_start();

// Mengecek apakah pengguna sudah login
if (isset($_SESSION['login'])) {
    $_SESSION['login'] = true; // Menandai bahwa sesi login valid
} else {
    // Jika belum login, pengguna diarahkan ke halaman login
    echo "<meta http-equiv='refresh' content='0; url=..'>";
    die();
}

// Mendapatkan data dari sesi yang sedang aktif
$nama = $_SESSION['username']; // Nama pengguna
$akses = $_SESSION['akses']; // Hak akses pengguna
$id_dokter = $_SESSION['id']; // ID dokter yang sedang login

// Mengecek apakah pengguna memiliki akses sebagai dokter
if ($akses != 'dokter') {
    // Jika bukan dokter, diarahkan ke halaman lain
    echo "<meta http-equiv='refresh' content='0; url=..'>";
    die();
}

// Mendapatkan jadwal yang sudah ada untuk dokter tertentu
$query = "SELECT hari FROM jadwal_periksa WHERE id_dokter = ?";
$stmt = $conn->prepare($query); // Mempersiapkan query
$stmt->bind_param("i", $id_dokter); // Mengikat parameter ID dokter ke query
$stmt->execute(); // Menjalankan query
$result = $stmt->get_result(); // Mendapatkan hasil query
$existingDays = []; // Array untuk menyimpan hari yang sudah digunakan
while($row = $result->fetch_assoc()) {
    $existingDays[] = $row['hari']; // Menambahkan hari ke dalam array
}

// Daftar semua hari yang tersedia dalam seminggu
$allDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
// Menentukan hari yang masih tersedia (belum digunakan dokter)
$availableDays = array_diff($allDays, $existingDays);

// Logika untuk menangani input data dari form
if (isset($_POST["submit"])) {
    // Validasi input: memastikan semua data tidak kosong
    if (empty($_POST["hari"]) || empty($_POST["jam_mulai"]) || empty($_POST["jam_selesai"])) {
        // Jika ada data kosong, tampilkan pesan dan arahkan kembali ke halaman input
        echo "
            <script>
                alert('Data tidak boleh kosong');
                document.location.href = '../jadwal_periksa/create.php';
            </script>
        ";
        die;
    } else {  
        // Menambahkan jadwal baru ke database
        if (tambahJadwalPeriksa($_POST) > 0) { // Jika berhasil
            echo "
                <script>
                    alert('Data berhasil ditambahkan');
                    document.location.href = '../jadwal_periksa';
                </script>
            ";
        } else if (tambahJadwalPeriksa($_POST) == -2) { // Jika jadwal sudah ada
            echo "
                <script>
                    alert('Data Gagal ditambahkan, jadwal periksa sudah ada');
                    document.location.href = '../jadwal_periksa/create.php';
                </script>
            ";
        } else if (tambahJadwalPeriksa($_POST) == -1) { // Jika gagal karena alasan lain
            echo "
                <script>
                    alert('Data Gagal ditambahkan');
                    document.location.href = '../jadwal_periksa';
                </script>
            ";
        }
    }
}
?>
<?php
// Bagian untuk mengatur judul halaman
$title = 'Poliklinik | Tambah Jadwal Periksa';

// Breadcrumb navigation untuk menunjukkan lokasi halaman
ob_start();?>
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="<?=$base_dokter;?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?=$base_dokter . '/jadwal_periksa';?>">Jadwal Periksa</a></li>
    <li class="breadcrumb-item active">Tambah Jadwal Periksa</li>
</ol>
<?php
$breadcrumb = ob_get_clean();

// Menentukan judul utama untuk halaman
ob_start();?>
Tambah Jadwal Periksa
<?php
$main_title = ob_get_clean();

// Bagian konten utama halaman
ob_start();?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tambah Jadwal Periksa</h3> <!-- Judul dalam kartu -->
    </div>
    <div class="card-body">
        <!-- Form untuk menambahkan jadwal periksa -->
        <form action="" id="tambahJadwal" method="POST">
            <!-- Input tersembunyi untuk menyimpan ID dokter -->
            <input type="hidden" name="id_dokter" value="<?=$id_dokter?>">
            <!-- Dropdown untuk memilih hari -->
            <div class="form-group">
                <label for="hari">Hari</label>
                <select name="hari" id="hari" class="form-control">
                    <option value="">-- Pilih Hari --</option>
                    <?php foreach($availableDays as $day): ?>
                        <option value="<?= $day ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Input untuk jam mulai -->
            <div class="form-group">
                <label for="jam_mulai">Jam Mulai</label>
                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control">
            </div>
            <!-- Input untuk jam selesai -->
            <div class="form-group">
                <label for="jam_selesai">Jam Selesai</label>
                <input type="time" name="jam_selesai" id="jam_selesai" class="form-control">
            </div>
            <!-- Tombol submit -->
            <div class="d-flex justify-content-end">
                <button type="submit" name="submit" id="submitButton" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
?>
<!-- Menyertakan layout utama -->
<?php include_once "../../../layouts/index.php"; ?>
