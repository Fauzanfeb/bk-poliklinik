<?php
include_once("../../../config/conn.php");
session_start();

if (isset($_SESSION['login'])) {
  $_SESSION['login'] = true;
} else {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}
$id_pasien = $_SESSION['id'];
$no_rm = $_SESSION['no_rm'];
$nama = $_SESSION['username'];
$akses = $_SESSION['akses'];

$url = $_SERVER['REQUEST_URI'];
$url = explode("/", $url);
$id_poli = $url[count($url) - 1];

if ($akses != 'pasien') {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}
?>

<?php
$title = 'Poliklinik | Riwayat Periksa';

// Breadcrumb Section
ob_start();?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?=$base_pasien;?>">Home</a></li>
  <li class="breadcrumb-item"><a href="<?=$base_pasien . '/poli';?>">Poli</a></li>
  <li class="breadcrumb-item active">Riwayat Periksa</li>
</ol>
<?php
$breadcrumb = ob_get_clean();
// ob_flush();

// Title Section
ob_start();?>
Riwayat Periksa
<?php
$main_title = ob_get_clean();
// ob_flush();

// Content Section
ob_start();?>

<div class="card">
  <div class="card-header bg-primary">
    <h3 class="card-title">Riwayat Periksa</h3>
  </div>
  <div class="card-body">
    <?php
    // Query untuk mendapatkan detail poli
    $poli = $pdo->prepare("
      SELECT d.nama_poli as poli_nama, c.nama as dokter_nama, 
             b.hari as jadwal_hari, b.jam_mulai as jadwal_mulai, 
             b.jam_selesai as jadwal_selesai, a.no_antrian as antrian, a.id as poli_id
      FROM daftar_poli as a
      INNER JOIN jadwal_periksa as b ON a.id_jadwal = b.id
      INNER JOIN dokter as c ON b.id_dokter = c.id
      INNER JOIN poli as d ON c.id_poli = d.id
      WHERE a.id = :id_poli");
    $poli->bindParam(':id_poli', $id_poli, PDO::PARAM_INT);
    $poli->execute();

    // Menampilkan detail poli
    if ($poli->rowCount() == 0) {
      echo "Tidak ada data";
    } else {
      $p = $poli->fetch();
    ?>
      <table class="table table-sm">
        <tr>
          <th>Nama Poli</th>
          <td><?= htmlspecialchars($p['poli_nama']) ?></td>
        </tr>
        <tr>
          <th>Nama Dokter</th>
          <td><?= htmlspecialchars($p['dokter_nama']) ?></td>
        </tr>
        <tr>
          <th>Hari</th>
          <td><?= htmlspecialchars($p['jadwal_hari']) ?></td>
        </tr>
        <tr>
          <th>Mulai</th>
          <td><?= htmlspecialchars($p['jadwal_mulai']) ?></td>
        </tr>
        <tr>
          <th>Selesai</th>
          <td><?= htmlspecialchars($p['jadwal_selesai']) ?></td>
        </tr>
        <tr>
          <th>Nomor Antrian</th>
          <td><button class="btn btn-success"><?= htmlspecialchars($p['antrian']) ?></button></td>
        </tr>
      </table>
      <br><br>
    <?php
    }

    // Query untuk mendapatkan daftar obat
    $list_obat = $pdo->prepare("
      SELECT a.id_daftar_poli, a.tgl_periksa, a.catatan, a.biaya_periksa, b.id_periksa, c.* 
      FROM periksa a
      JOIN detail_periksa b ON a.id = b.id_periksa 
      JOIN obat c ON b.id_obat = c.id
      WHERE a.id_daftar_poli = :id_poli");
    $list_obat->bindParam(':id_poli', $id_poli, PDO::PARAM_INT);
    $list_obat->execute();

    // Menampilkan daftar obat
    echo '<div class="card-body bg-light">';
    if ($list_obat->rowCount() == 0) {
        echo "Tidak ada data";
    } else {
        $firstRecord = $list_obat->fetch();
        echo "<i>Tgl Periksa: " . htmlspecialchars($firstRecord['tgl_periksa']) . "</i><br>";
        echo "Catatan: " . htmlspecialchars($firstRecord['catatan']) . "<br>";
        echo "Daftar Obat Diresepkan: <br>";
        echo "<ol>";
        echo "<li>" . htmlspecialchars($firstRecord['nama_obat']) . "</li>";
        
        while ($obats = $list_obat->fetch()) {
            echo "<li>" . htmlspecialchars($obats['nama_obat']) . "</li>";
        }
        
        echo "</ol>";
        echo "<h2><span class='bg-danger text-white p-1'> Biaya Periksa: " . htmlspecialchars($firstRecord['biaya_periksa']) . "</span></h2><br><br>";
    }
    echo '</div>';
    ?>
  </div>
</div>


<a href="<?=$base_pasien . '/poli';?>" class="btn btn-primary btn-block">Kembali</a>

<?php
$content = ob_get_clean();
// ob_flush();
?>

<?php include_once "../../../layouts/index.php";?>