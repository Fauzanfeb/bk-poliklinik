<?php
// Menyertakan file koneksi ke database
include_once("../../../config/conn.php");

// Memulai sesi untuk mengakses data sesi pengguna
session_start();

// Mengecek apakah sesi login sudah ada
if (isset($_SESSION['login'])) {
  $_SESSION['login'] = true; // Menjaga status login tetap aktif
} else {
  // Jika tidak ada sesi login, redirect ke halaman sebelumnya
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

// Mendapatkan data sesi pengguna
$id_pasien = $_SESSION['id'];       // ID pasien
$no_rm = $_SESSION['no_rm'];       // Nomor rekam medis
$nama = $_SESSION['username'];     // Nama pengguna
$akses = $_SESSION['akses'];       // Hak akses pengguna

// Mendapatkan ID poli dari URL
$url = $_SERVER['REQUEST_URI'];    // Mengambil URL saat ini
$url = explode("/", $url);         // Memecah URL berdasarkan tanda '/'
$id_poli = $url[count($url) - 1];  // ID poli adalah bagian terakhir dari URL

// Mengecek apakah hak akses adalah pasien
if ($akses != 'pasien') {
  // Jika bukan pasien, redirect ke halaman sebelumnya
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}
?>

<?php
// Menentukan judul halaman
$title = 'Poliklinik | Tambah Jadwal Periksa';

// Bagian breadcrumb untuk navigasi
ob_start();?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?=$base_pasien;?>">Home</a></li> <!-- Link ke halaman Home -->
  <li class="breadcrumb-item"><a href="<?=$base_pasien . '/poli';?>">Poli</a></li> <!-- Link ke halaman Poli -->
  <li class="breadcrumb-item active">Detail Poli</li> <!-- Menampilkan halaman saat ini -->
</ol>
<?php
$breadcrumb = ob_get_clean(); // Menyimpan breadcrumb untuk ditampilkan
// ob_flush();

// Bagian judul utama halaman
ob_start();?>
Detail Poli
<?php
$main_title = ob_get_clean(); // Menyimpan judul utama halaman
// ob_flush();

// Bagian konten utama halaman
ob_start();?>

<div class="card">
  <div class="card-header bg-primary">
    <h3 class="card-title">Detail Poli</h3> <!-- Judul di bagian header kartu -->
  </div>
  <div class="card-body">
  <?php
  // Query untuk mendapatkan detail informasi poli
  $poli = $pdo->prepare("
    SELECT d.nama_poli as poli_nama, c.nama as dokter_nama, 
           b.hari as jadwal_hari, b.jam_mulai as jadwal_mulai, 
           b.jam_selesai as jadwal_selesai, a.no_antrian as antrian, a.id as poli_id
    FROM daftar_poli as a
    INNER JOIN jadwal_periksa as b ON a.id_jadwal = b.id
    INNER JOIN dokter as c ON b.id_dokter = c.id
    INNER JOIN poli as d ON c.id_poli = d.id
    WHERE a.id = :id_poli"); // Mendapatkan data berdasarkan ID poli
  $poli->bindParam(':id_poli', $id_poli, PDO::PARAM_INT); // Menghubungkan parameter
  $poli->execute(); // Menjalankan query

  // Mengecek apakah ada data poli yang ditemukan
  if ($poli->rowCount() == 0) {
    echo "Tidak ada data"; // Pesan jika tidak ada data
  } else {
    $p = $poli->fetch(); // Mengambil data poli
  ?>
    <!-- Menampilkan detail poli -->
    <center>
      <h5>Nama Poli</h5>
      <p><?= htmlspecialchars($p['poli_nama']) ?></p>
      <hr>

      <h5>Nama Dokter</h5>
      <p><?= htmlspecialchars($p['dokter_nama']) ?></p>
      <hr>

      <h5>Hari</h5>
      <p><?= htmlspecialchars($p['jadwal_hari']) ?></p>
      <hr>

      <h5>Mulai</h5>
      <p><?= htmlspecialchars($p['jadwal_mulai']) ?></p>
      <hr>

      <h5>Selesai</h5>
      <p><?= htmlspecialchars($p['jadwal_selesai']) ?></p>
      <hr>

      <h5>Nomor Antrian</h5>
      <button class="btn btn-success"><?= htmlspecialchars($p['antrian']) ?></button>
      <hr>
    </center>
  <?php
  }
  ?>
  </div>
</div>

<!-- Tombol kembali ke halaman sebelumnya -->
<a href="<?=$base_pasien . '/poli';?>" class="btn btn-primary btn-block">Kembali</a>

<?php
$content = ob_get_clean(); // Menyimpan konten utama halaman
// ob_flush();
?>

<!-- Menyertakan layout utama -->
<?php include_once "../../../layouts/index.php";?>
