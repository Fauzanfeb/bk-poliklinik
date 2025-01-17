<?php
include_once "../../../config/conn.php"; // Menghubungkan file konfigurasi untuk koneksi database
session_start(); // Memulai session untuk melacak status login

// Cek apakah user sudah login
if (isset($_SESSION['login'])) {
    $_SESSION['login'] = true; // Set session login ke true
} else {
    // Jika belum login, arahkan ke halaman login
    echo "<meta http-equiv='refresh' content='0; url=..'>";
    die(); // Hentikan eksekusi jika belum login
}

$nama = $_SESSION['username']; // Mendapatkan nama pengguna dari session
$akses = $_SESSION['akses']; // Mendapatkan akses role pengguna dari session
$id_dokter = $_SESSION['id']; // Mendapatkan ID dokter dari session

// Cek apakah pengguna memiliki akses sebagai 'dokter'
if ($akses != 'dokter') {
    echo "<meta http-equiv='refresh' content='0; url=..'>"; // Redirect jika bukan dokter
    die(); // Hentikan eksekusi
}

// Mengambil ID jadwal dari URL
$url = $_SERVER['REQUEST_URI'];
$url = explode("/", $url); // Memecah URL untuk mendapatkan ID di bagian akhir
$id = $url[count($url) - 1]; // ID jadwal ada di bagian terakhir URL

// Mengambil data jadwal berdasarkan ID dari database
$jadwal = query("SELECT * FROM jadwal_periksa WHERE id = $id")[0];

// Mengambil data dokter berdasarkan ID dokter yang login
$dokter = query("SELECT * FROM jadwal_periksa WHERE id_dokter = $id_dokter")[0];

// Proses untuk memperbarui jadwal periksa jika tombol submit ditekan
if (isset($_POST["submit"])) {
    // Validasi inputan, pastikan tidak ada yang kosong
    if (empty($_POST["hari"]) || empty($_POST["jam_mulai"]) || empty($_POST["jam_selesai"])) {
        echo "
          <script>
              alert('Data tidak boleh kosong'); // Tampilkan pesan jika data kosong
              document.location.href = '../jadwal_periksa/edit.php'; // Redirect ke halaman edit
          </script>
      ";
        die; // Hentikan eksekusi
    } else {
        // Jika validasi lulus, perbarui jadwal di database
        updateJadwalPeriksa($_POST, $id);
        echo "
          <script>
              alert('Data berhasil diubah'); // Tampilkan pesan jika berhasil diubah
              document.location.href = '../'; // Redirect ke halaman utama
          </script>";
    }
}
?>

<?php
$title = 'Poliklinik | Edit Jadwal Periksa'; // Judul halaman

// Breadcrumb Section: untuk menampilkan navigasi
ob_start();?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?=$base_dokter;?>">Home</a></li>
  <li class="breadcrumb-item"><a href="<?=$base_dokter . '/jadwal_periksa';?>">Jadwal Periksa</a></li>
  <li class="breadcrumb-item active">Edit Jadwal Periksa</li>
</ol>
<?php
$breadcrumb = ob_get_clean(); // Menyimpan output breadcrumb ke variabel $breadcrumb

// Title Section
ob_start();?>
Edit Jadwal Periksa
<?php
$main_title = ob_get_clean(); // Menyimpan title ke variabel $main_title

// Content Section: Bagian utama dari halaman
ob_start();?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Edit Jadwal Periksa</h3> <!-- Judul pada halaman edit -->
  </div>
  <div class="card-body">
    <form action="" id="tambahJadwal" method="POST"> <!-- Form untuk mengedit jadwal -->
      <input type="hidden" name="id_dokter" value="<?=$id_dokter?>"> <!-- Menyimpan ID dokter di form -->
      <div class="form-group">
        <label for="hari">Hari</label>
        <input type="text" name="hari" id="hari" class="form-control" value="<?= $jadwal['hari'] ?>" > <!-- Menampilkan hari jadwal -->
      </div>
      <div class="form-group">
        <label for="jam_mulai">Jam Mulai</label>
        <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="<?= date('H:i', strtotime($jadwal['jam_mulai'])) ?>" > <!-- Menampilkan jam mulai -->
      </div>
      <div class="form-group">
        <label for="jam_selesai">Jam Selesai</label>
        <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="<?=date('H:i', strtotime($jadwal['jam_selesai']))?>" > <!-- Menampilkan jam selesai -->
      </div>
      <div class="form-group">
        <!-- Input radio button untuk status aktif/tidak aktif -->
        <label for="aktif">Status</label>
        <div class="form-check">
          <input type="radio" id="aktif1" class="form-check-input" name="aktif" value="Y" <?php if($jadwal['aktif'] == "Y"){echo "checked";} ?>> <!-- Status Aktif -->
          <label for="aktif1" class="form-check-label">Aktif</label>
        </div>
        <div class="form-check">
          <input type="radio" id="tidak-aktif" class="form-check-input" name="aktif" value="T" <?php if($jadwal['aktif'] == "T"){echo "checked";} ?>> <!-- Status Tidak Aktif -->
          <label for="tidak-aktif" class="form-check-label">Tidak Aktif</label>
        </div>
      </div>
      <div class="d-flex justify-content-end">
        <button type="submit" name="submit" id="submitButton" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button> <!-- Tombol untuk menyimpan perubahan -->
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean(); // Menyimpan konten form ke variabel $content

// JS Section: Bagian JavaScript untuk validasi form
ob_start();?>
<script>
  let jam_mulai = $('#jam_mulai'); // Menyimpan input jam mulai
  let jam_selesai = $('#jam_selesai'); // Menyimpan input jam selesai

  // Fungsi untuk mencegah pengiriman form jika jam mulai lebih besar atau sama dengan jam selesai
  $('#tambahJadwal').submit(function (e) {
    if (jam_mulai.val() >= jam_selesai.val()) { // Membandingkan waktu mulai dan selesai
      e.preventDefault(); // Mencegah pengiriman form
      alert('Jam mulai tidak boleh lebih dari jam selesai'); // Menampilkan pesan error
    }
  });

</script>
<?php
$js = ob_get_clean(); // Menyimpan script JS ke variabel $js

?>

<?php include_once "../../../layouts/index.php";?> <!-- Memanggil layout umum untuk halaman -->
