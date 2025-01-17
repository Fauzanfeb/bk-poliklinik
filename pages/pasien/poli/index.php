<?php
// Mengimpor konfigurasi database dan memulai sesi
include_once("../../../config/conn.php");
session_start();

// Mengecek apakah sesi 'signup' atau 'login' ada
if (isset($_SESSION['signup']) || isset($_SESSION['login'])) {
  $_SESSION['signup'] = true;
  $_SESSION['login'] = true;
} else {
  // Jika tidak ada sesi, arahkan kembali ke halaman sebelumnya
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

// Mengambil data dari sesi untuk ID pasien, nomor rekam medis, nama, dan akses
$id_pasien = $_SESSION['id'];
$no_rm = $_SESSION['no_rm'];
$nama = $_SESSION['username'];
$akses = $_SESSION['akses'];

// Mengecek jika akses bukan 'pasien', arahkan kembali ke halaman utama
if ($akses != 'pasien') {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

// Mengecek apakah form disubmit
if (isset($_POST['submit'])) {

  // Validasi jika ID jadwal yang dipilih adalah "900", yang menandakan jadwal belum dipilih
  if ($_POST['id_jadwal'] == "900") {
    echo "
        <script>
            alert('Jadwal tidak boleh kosong!');
        </script>
    ";
    echo "<meta http-equiv='refresh' content='0'>";
  }

  // Memanggil fungsi daftarPoli untuk mendaftarkan pasien ke poli yang dipilih
  if (daftarPoli($_POST) > 0) {
    // Jika berhasil mendaftar poli
    echo "
        <script>
            alert('Berhasil mendaftar poli');
        </script>
    ";
} else {
    // Jika gagal mendaftar poli
    echo "
        <script>
            alert('Gagal mendaftar poli');
        </script>
    ";
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Mengatur metadata halaman dan sumber daya eksternal seperti font dan CSS -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= getenv('APP_NAME') ?> | Dashboard</title>

  <!-- Memuat berbagai CSS library eksternal seperti fontawesome, ionicons, dan lainnya -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/jqvmap/jqvmap.min.css">
  <link rel="stylesheet" href="../../../dist/css/adminlte.min.css">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST']?>/bk-poliklinik/plugins/summernote/summernote-bs4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Memasukkan header layout dari file eksternal -->
  <?php include "../../../layouts/header.php"?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Daftar Poli</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Daftar Poli</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-4">
            <!-- Form untuk registrasi poli -->
            <div class="card">
              <h5 class="card-header bg-primary">Daftar Poli</h5>
              <div class="card-body">

                <form action="" method="POST">
                  <input type="hidden" value="<?= $id_pasien ?>" name="id_pasien">

                  <!-- Input Nomor Rekam Medis -->
                  <div class="mb-3">
                    <label for="no_rm" class="form-label">Nomor Rekam Medis</label>
                    <input type="text " class="form-control" id="no_rm" placeholder="nomor rekam medis" name="no_rm" value="<?= $no_rm ?>" disabled>
                  </div>

                  <!-- Dropdown untuk memilih Poli -->
                  <div class="mb-3">
                    <label for="inputPoli" class="form-label">Pilih Poli</label>
                    <select id="inputPoli" class="form-control">
                      <option>Open this select menu</option>
                      <?php
                      // Menampilkan daftar poli dari database
                      $data = $pdo->prepare("SELECT * FROM poli");
                      $data->execute();
                      if ($data->rowCount() == 0) {
                        echo "<option>Tidak ada poli</option>";
                      } else {
                        while($d = $data->fetch()) {
                      ?>
                        <option value="<?= $d['id'] ?>"><?= $d['nama_poli'] ?></option> 
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </div>

                  <!-- Dropdown untuk memilih Jadwal -->
                  <div class="mb-3">
                    <label for="inputJadwal" class="form-label">Pilih Jadwal</label>
                    <select id="inputJadwal" class="form-control" name="id_jadwal">
                      <option value="900">Open this select menu</option>
                      <?php
                      // Mengambil data jadwal pemeriksaan dari database
                      $data = $pdo->prepare("SELECT * FROM jadwal_periksa");
                      $data->execute();
                      if ($data->rowCount() == 0) {
                        echo "<option>Tidak ada poli</option>";
                      } else {
                        while($d = $data->fetch()) {
                      ?>
                        <option value="<?= $d['id'] ?>"><?= $d['hari'] ?>,<?= $d['jam_mulai'] ?> - <?= $d['jam_selesai'] ?></option> 
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </div>

                  <!-- Input Keluhan -->
                  <div class="mb-3">
                    <label for="keluhan" class="form-label">Keluhan</label>
                    <textarea class="form-control" id="keluhan" rows="3" name="keluhan"></textarea>
                  </div>
                  <!-- Tombol untuk submit form -->
                  <button type="submit" name="submit" class="btn btn-primary">Daftar</button>
                </form>
                
              </div>
            </div>
          </div>

          <div class="col-8">
            <!-- Tabel riwayat pendaftaran poli -->
            <div class="card">
              <h5 class="card-header bg-primary">Riwayat daftar poli</h5>
              <div class="card-body">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">No.</th>
                      <th scope="col">Poli</th>
                      <th scope="col">Dokter</th>
                      <th scope="col">Hari</th>
                      <th scope="col">Mulai</th>
                      <th scope="col">Selesai</th>
                      <th scope="col">Antrian</th>
                      <th scope="col">Status</th>
                      <th scope="col">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Mengambil data riwayat pendaftaran poli untuk pasien
                    $query = "
                        SELECT d.nama_poli as poli_nama,
                              c.nama as dokter_nama, 
                              b.hari as jadwal_hari, 
                              b.jam_mulai as jadwal_mulai, 
                              b.jam_selesai as jadwal_selesai,
                              a.no_antrian as antrian,
                              a.id as poli_id,
                              a.status_periksa,
                              e.tgl_periksa
                        FROM daftar_poli as a
                        INNER JOIN jadwal_periksa as b ON a.id_jadwal = b.id
                        INNER JOIN dokter as c ON b.id_dokter = c.id
                        INNER JOIN poli as d ON c.id_poli = d.id
                        LEFT JOIN periksa as e ON a.id = e.id_daftar_poli 
                        WHERE a.id_pasien = ?
                        ORDER BY a.id DESC";

                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$id_pasien]);
                    $no = 0;

                    if ($stmt->rowCount() == 0) {
                      echo "<tr><td colspan='9' align='center'>Tidak ada data</td></tr>";
                    } else {
                      while ($p = $stmt->fetch()) {
                          $no++;
                          ?>
                          <tr>
                              <th scope="row">
                                  <?= $no == 1 ? "<span class='badge badge-info'>New</span>" : $no ?>
                              </th>
                              <td><?= htmlspecialchars($p['poli_nama']) ?></td>
                              <td><?= htmlspecialchars($p['dokter_nama']) ?></td>
                              <td><?= htmlspecialchars($p['jadwal_hari']) ?></td>
                              <td><?= htmlspecialchars($p['jadwal_mulai']) ?></td>
                              <td><?= htmlspecialchars($p['jadwal_selesai']) ?></td>
                              <td><?= htmlspecialchars($p['antrian']) ?></td>
                              <td>
                                  <?php if ($p['status_periksa'] == 1): ?>
                                      <span class="badge bg-success">Sudah diperiksa</span><br>
                                      <span class="badge bg-default"><i><?= htmlspecialchars($p['tgl_periksa']) ?></i></span>
                                  <?php else: ?>
                                      <span class="badge bg-danger">Belum diperiksa</span>
                                  <?php endif; ?>
                              </td>
                              <td>
                                  <?php if ($p['status_periksa'] == 1): ?>
                                      <a href="detail_poli_periksa.php/<?= $p['poli_id'] ?>">
                                          <button class="btn btn-success btn-sm">Riwayat</button>
                                      </a>
                                  <?php else: ?>
                                      <a href="detail_poli.php/<?= $p['poli_id'] ?>">
                                          <button class="btn btn-info btn-sm">Detail</button>
                                      </a>
                                  <?php endif; ?>
                              </td>
                          </tr>
                          <?php
                      }
                  }
                  ?>
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Memasukkan footer layout dari file eksternal -->
  <?php include "../../../layouts/footer.php"; ?>
</div>

<!-- Memuat file JavaScript eksternal -->
<?php include "../../../layouts/pluginsexport.php"; ?>

<script>
  // Fungsi untuk menangani perubahan pada dropdown Poli dan memuat jadwal sesuai dengan poli yang dipilih
  document.getElementById('inputPoli').addEventListener('change', function() {
    var poliId = this.value; // Ambil nilai ID poli yang dipilih
    loadJadwal(poliId); // Panggil fungsi untuk memuat jadwal dokter
  });

  function loadJadwal(poliId) {
    // Membuat objek XMLHttpRequest untuk permintaan Ajax
    var xhr = new XMLHttpRequest();

    // Menyusun permintaan GET untuk memuat jadwal berdasarkan poli yang dipilih
    xhr.open('GET', 'http://' + window.location.host + '/bk-poliklinik/pages/pasien/poli/get_jadwal.php?poli_id=' + poliId, true);

    // Menetapkan header respons
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        // Menangani perubahan dropdown jadwal berdasarkan respons
        document.getElementById('inputJadwal').innerHTML = xhr.responseText;
      }
    };
    xhr.send(); // Kirim permintaan Ajax
  }
</script>

</body>
</html>
