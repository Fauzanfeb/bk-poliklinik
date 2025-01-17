<?php
include_once("../../../config/conn.php"); // Mengimpor konfigurasi koneksi database.
session_start(); // Memulai sesi untuk melacak data pengguna.

if (isset($_SESSION['login'])) { // Mengecek apakah sesi login ada.
  $_SESSION['login'] = true; // Menandai bahwa pengguna dalam status login.
} else {
  echo "<meta http-equiv='refresh' content='0; url=..'>"; // Mengarahkan pengguna ke halaman sebelumnya jika tidak login.
  die(); // Menghentikan eksekusi skrip.
}
$id_pasien = $_SESSION['id']; // Menyimpan ID pasien dari sesi.
$no_rm = $_SESSION['no_rm']; // Menyimpan nomor rekam medis dari sesi.
$nama = $_SESSION['username']; // Menyimpan nama pengguna dari sesi.
$akses = $_SESSION['akses']; // Menyimpan hak akses pengguna dari sesi.

$url = $_SERVER['REQUEST_URI']; // Mendapatkan URL saat ini.
$url = explode("/", $url); // Memisahkan URL berdasarkan tanda "/".
$id_poli = $url[count($url) - 1]; // Mendapatkan ID poli dari URL.

if ($akses != 'pasien') { // Mengecek apakah akses pengguna bukan pasien.
  echo "<meta http-equiv='refresh' content='0; url=..'>"; // Mengarahkan pengguna ke halaman sebelumnya jika bukan pasien.
  die(); // Menghentikan eksekusi skrip.
}
?>

<?php
$title = 'Poliklinik | Riwayat Periksa'; // Menentukan judul halaman.

// Bagian breadcrumb untuk navigasi halaman.
ob_start(); ?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?=$base_pasien;?>">Home</a></li> <!-- Link ke halaman utama pasien. -->
  <li class="breadcrumb-item"><a href="<?=$base_pasien . '/poli';?>">Poli</a></li> <!-- Link ke daftar poli. -->
  <li class="breadcrumb-item active">Riwayat Periksa</li> <!-- Breadcrumb aktif untuk halaman riwayat periksa. -->
</ol>
<?php
$breadcrumb = ob_get_clean(); // Menyimpan breadcrumb untuk digunakan dalam layout halaman.

// Bagian judul utama halaman.
ob_start(); ?>
Riwayat Periksa
<?php
$main_title = ob_get_clean(); // Menyimpan judul utama untuk layout halaman.

// Bagian konten utama halaman.
ob_start(); ?>

<div class="card">
  <div class="card-header bg-primary">
    <h3 class="card-title">Riwayat Periksa</h3> <!-- Judul bagian riwayat periksa. -->
  </div>
  <div class="card-body">
    <?php
    // Query untuk mendapatkan detail poli.
    $poli = $pdo->prepare("
      SELECT d.nama_poli as poli_nama, c.nama as dokter_nama, 
             b.hari as jadwal_hari, b.jam_mulai as jadwal_mulai, 
             b.jam_selesai as jadwal_selesai, a.no_antrian as antrian, a.id as poli_id
      FROM daftar_poli as a
      INNER JOIN jadwal_periksa as b ON a.id_jadwal = b.id
      INNER JOIN dokter as c ON b.id_dokter = c.id
      INNER JOIN poli as d ON c.id_poli = d.id
      WHERE a.id = :id_poli"); // Mengambil detail poli berdasarkan ID poli.
    $poli->bindParam(':id_poli', $id_poli, PDO::PARAM_INT); // Mengikat parameter ID poli.
    $poli->execute(); // Menjalankan query.

    if ($poli->rowCount() == 0) { // Mengecek apakah tidak ada data.
      echo "Tidak ada data"; // Menampilkan pesan jika data tidak ditemukan.
    } else {
      $p = $poli->fetch(); // Mengambil data poli.
    ?>
      <table class="table table-sm">
        <tr>
          <th>Nama Poli</th>
          <td><?= htmlspecialchars($p['poli_nama']) ?></td> <!-- Menampilkan nama poli. -->
        </tr>
        <tr>
          <th>Nama Dokter</th>
          <td><?= htmlspecialchars($p['dokter_nama']) ?></td> <!-- Menampilkan nama dokter. -->
        </tr>
        <tr>
          <th>Hari</th>
          <td><?= htmlspecialchars($p['jadwal_hari']) ?></td> <!-- Menampilkan hari jadwal. -->
        </tr>
        <tr>
          <th>Mulai</th>
          <td><?= htmlspecialchars($p['jadwal_mulai']) ?></td> <!-- Menampilkan waktu mulai jadwal. -->
        </tr>
        <tr>
          <th>Selesai</th>
          <td><?= htmlspecialchars($p['jadwal_selesai']) ?></td> <!-- Menampilkan waktu selesai jadwal. -->
        </tr>
        <tr>
          <th>Nomor Antrian</th>
          <td><button class="btn btn-success"><?= htmlspecialchars($p['antrian']) ?></button></td> <!-- Menampilkan nomor antrian. -->
        </tr>
      </table>
      <br><br>
    <?php
    }

    // Query untuk mendapatkan daftar obat yang diresepkan.
    $list_obat = $pdo->prepare("
      SELECT a.id_daftar_poli, a.tgl_periksa, a.catatan, a.biaya_periksa, b.id_periksa, c.* 
      FROM periksa a
      JOIN detail_periksa b ON a.id = b.id_periksa 
      JOIN obat c ON b.id_obat = c.id
      WHERE a.id_daftar_poli = :id_poli"); // Mengambil daftar obat berdasarkan ID poli.
    $list_obat->bindParam(':id_poli', $id_poli, PDO::PARAM_INT); // Mengikat parameter ID poli.
    $list_obat->execute(); // Menjalankan query.

    // Menampilkan daftar obat.
    echo '<div class="card-body bg-light">';
    if ($list_obat->rowCount() == 0) { // Jika tidak ada data obat.
        echo "Tidak ada data"; // Menampilkan pesan.
    } else {
        $firstRecord = $list_obat->fetch(); // Mengambil data pertama.
        echo "<i>Tgl Periksa: " . htmlspecialchars($firstRecord['tgl_periksa']) . "</i><br>"; // Tanggal periksa.
        echo "Catatan: " . htmlspecialchars($firstRecord['catatan']) . "<br>"; // Catatan dari pemeriksaan.
        echo "Daftar Obat Diresepkan: <br>";
        echo "<ol>";
        echo "<li>" . htmlspecialchars($firstRecord['nama_obat']) . "</li>"; // Obat pertama.
        
        while ($obats = $list_obat->fetch()) { // Mengambil data obat lainnya.
            echo "<li>" . htmlspecialchars($obats['nama_obat']) . "</li>";
        }
        
        echo "</ol>";
        echo "<h2><span class='bg-danger text-white p-1'> Biaya Periksa: " . htmlspecialchars($firstRecord['biaya_periksa']) . "</span></h2><br><br>"; // Menampilkan biaya pemeriksaan.
    }
    echo '</div>';
    ?>
  </div>
</div>

<a href="<?=$base_pasien . '/poli';?>" class="btn btn-primary btn-block">Kembali</a> <!-- Tombol kembali ke halaman poli. -->

<?php
$content = ob_get_clean(); // Menyimpan konten untuk layout halaman.
?>

<?php include_once "../../../layouts/index.php"; ?> <!-- Memasukkan layout utama halaman. -->
