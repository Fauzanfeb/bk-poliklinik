<?php
include_once("../../../config/conn.php"); // Menyertakan file koneksi ke database
session_start(); // Memulai session PHP

// Mengecek apakah pengguna sudah login
if (isset($_SESSION['login'])) {
  $_SESSION['login'] = true; // Menandakan pengguna sudah login
} else {
  // Jika pengguna belum login, redirect ke halaman login
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

$nama = $_SESSION['username']; // Mengambil nama pengguna dari session
$akses = $_SESSION['akses']; // Mengambil akses pengguna dari session
$id = $_SESSION['id']; // Mengambil id pengguna (dokter) dari session

// Mengecek apakah pengguna memiliki akses sebagai dokter
if ($akses != 'dokter') {
  echo "<meta http-equiv='refresh' content='0; url=..'>"; // Redirect jika akses bukan dokter
  die();
}

// Mengambil data dokter berdasarkan id
$dokter = query("SELECT * FROM dokter WHERE id = $id")[0];

// Mengecek apakah form di-submit
if (isset($_POST["submit"])) {
  // Memanggil fungsi ubahDokter untuk mengupdate data dokter
  if (ubahDokter($_POST) > 0) {
    $_SESSION['username'] = $_POST['nama']; // Memperbarui nama pengguna di session

    echo "
        <script>
            alert('Data berhasil diubah'); // Menampilkan alert jika data berhasil diubah
            document.location.href = '../profil'; // Redirect ke halaman profil
        </script>
    ";
    session_write_close(); // Menutup session setelah perubahan
    header("Refresh:0"); // Me-refresh halaman setelah perubahan data
    exit;
  } else {
    echo "
        <script>
            alert('Data Gagal diubah'); // Menampilkan alert jika data gagal diubah
            document.location.href = '../profil'; // Redirect ke halaman profil
        </script>
    ";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= getenv('APP_NAME') ?> | Profil</title>

  <?php include "../../../layouts/plugin_header.php" ?> <!-- Menyertakan file header layout untuk plugin -->
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Preloader untuk menampilkan logo saat memuat halaman -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/dist/img/Logo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <?php include "../../../layouts/header.php" ?> <!-- Menyertakan header layout -->

    <!-- Content Wrapper untuk membungkus halaman utama -->
    <div class="content-wrapper">
      <!-- Content Header (Bagian header halaman) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Profil <?= ucwords($_SESSION['akses']) ?></h1> <!-- Menampilkan judul profil sesuai dengan akses -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content (Konten utama halaman) -->
      <section class="content">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Edit <small>Data Dokter</small></h3> <!-- Judul untuk form edit data dokter -->
          </div>
          <form id="editForm" action="" method="POST">
            <input type="hidden" name="id" value="<?= $dokter["id"]; ?>"> <!-- Menyembunyikan id dokter untuk proses update -->
            <div class="card-body">
              <!-- Form untuk mengedit data dokter -->
              <div class="form-group">
                <label for="nama">Nama Dokter</label>
                <input type="text" id="nama" name="nama" class="form-control" value="<?= $dokter['nama']; ?>"> <!-- Input untuk nama dokter -->
              </div>
              <div class="form-group">
                <label for="alamat">Alamat Dokter</label>
                <input type="text" id="alamat" name="alamat" class="form-control" value="<?= $dokter['alamat']; ?>"> <!-- Input untuk alamat dokter -->
              </div>
              <div class="form-group">
                <label for="no_hp">Telepon Dokter</label>
                <input type="number" id="no_hp" name="no_hp" class="form-control" value="<?= $dokter['no_hp']; ?>"> <!-- Input untuk nomor telepon dokter -->
              </div>
              <div class="d-flex justify-content-center">
                <button type="submit" name="submit" id="submitButton" class="btn btn-primary" disabled>Simpan Perubahan</button> <!-- Tombol simpan perubahan, di-disable jika tidak ada perubahan -->
              </div>
            </div>
          </form>
        </div>
      </section>

      <!-- Script untuk mengecek apakah ada perubahan pada form, dan mengaktifkan tombol submit jika ada perubahan -->
      <script>
        const form = document.getElementById('editForm');
        const inputs = form.querySelectorAll('input');

        // Fungsi untuk mengecek apakah ada perubahan pada form
        const checkChanges = () => {
          let changes = false;
          inputs.forEach(input => {
            if (input.defaultValue !== input.value) {
              changes = true; // Menandakan ada perubahan jika nilai default tidak sama dengan nilai input
            }
          });
          return changes; // Mengembalikan status perubahan
        };

        // Fungsi untuk mengaktifkan/menonaktifkan tombol submit berdasarkan perubahan
        const toggleSubmit = () => {
          const submitButton = document.getElementById('submitButton');
          if (checkChanges()) {
            submitButton.disabled = false; // Aktifkan tombol submit jika ada perubahan
          } else {
            submitButton.disabled = true; // Nonaktifkan tombol submit jika tidak ada perubahan
          }
        };

        // Menambahkan event listener untuk setiap input pada form agar tombol submit bisa diaktifkan atau dinonaktifkan
        inputs.forEach(input => {
          input.addEventListener('input', toggleSubmit);
        });
      </script>

      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php include "../../../layouts/footer.php"; ?> <!-- Menyertakan footer layout -->
  </div>
  <!-- ./wrapper -->

  <?php include "../../../layouts/pluginsexport.php"; ?> <!-- Menyertakan file footer layout untuk plugin eksternal -->
</body>

</html>
