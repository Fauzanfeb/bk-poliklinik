<?php
session_start(); // Memulai session untuk melacak data pengguna selama proses pendaftaran
include_once("../../config/conn.php"); // Menghubungkan ke file konfigurasi untuk koneksi database


// Mengecek apakah request yang diterima adalah metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Mendapatkan nilai dari form -- atribut name di input
  $nama = $_POST['nama'];
  $alamat = $_POST['alamat'];
  $no_ktp = $_POST['no_ktp'];
  $no_hp = $_POST['no_hp'];

  //   -------   SITUASI 1 -------
  
  // Query untuk mengecek apakah pasien sudah terdaftar berdasarkan nomor KTP
  $query_check_pasien = "SELECT id, nama ,no_rm FROM pasien WHERE no_ktp = '$no_ktp'";
  $result_check_pasien = mysqli_query($conn, $query_check_pasien);

  // Jika pasien sudah terdaftar
  if (mysqli_num_rows($result_check_pasien) > 0) {
    $row = mysqli_fetch_assoc($result_check_pasien);

    // Mengecek apakah nama yang dimasukkan sesuai dengan nama yang terdaftar
    if ( $row['nama'] != $nama) {
      // Jika nama tidak sesuai dengan nomor KTP yang terdaftar
      echo "<script>alert(`Nama pasien tidak sesuai dengan nomor KTP yang terdaftar.`);</script>";
      echo "<meta http-equiv='refresh' content='0; url=register.php'>"; // Mengarahkan ulang ke halaman registrasi
      die(); // Menghentikan eksekusi script selanjutnya
    }
    
    // Jika nomor KTP terdaftar dan nama sesuai, set session dan arahkan ke halaman pasien
    $_SESSION['signup'] = true;
    $_SESSION['id'] = $row['id'];
    $_SESSION['username'] = $nama;
    $_SESSION['no_rm'] = $row['no_rm'];
    $_SESSION['akses'] = 'pasien';

    echo "<meta http-equiv='refresh' content='0; url=../pasien'>"; // Redirect ke halaman pasien
    die(); // Menghentikan eksekusi script
  }
  

  //   -------   SITUASI 2 -------

  // Query untuk mendapatkan nomor rekam medis (no_rm) terakhir
  $queryGetRm = "SELECT MAX(SUBSTRING(no_rm, 8)) as last_queue_number FROM pasien";
  $resultRm = mysqli_query($conn, $queryGetRm);

  // Periksa apakah query berhasil
  if (!$resultRm) {
      die("Query gagal: " . mysqli_error($conn)); // Jika gagal, tampilkan pesan error
  }

  // Ambil nomor antrian terakhir
  $rowRm = mysqli_fetch_assoc($resultRm);
  $lastQueueNumber = $rowRm['last_queue_number'];

  // Jika tidak ada data, inisialisasi nomor antrian dengan 0
  $lastQueueNumber = $lastQueueNumber ? $lastQueueNumber : 0;

  // --- Menyusun nomor rekam medis baru ---
  
  // Mendapatkan tahun dan bulan saat ini
  $tahun_bulan = date("Ym");

  // Menambahkan 1 pada nomor antrian terakhir untuk membuat nomor rekam medis baru
  $newQueueNumber = $lastQueueNumber + 1;

  // Membentuk nomor rekam medis dengan format YYYYMM-XXX
  $no_rm = $tahun_bulan . "-" . str_pad($newQueueNumber, 3, '0', STR_PAD_LEFT);

  // --- Lakukan operasi INSERT ---
  
  // Query untuk menyimpan data pasien baru ke database
  $query = "INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm) VALUES ('$nama', '$alamat', '$no_ktp', '$no_hp', '$no_rm')";

  // Eksekusi query untuk menambah data
  if (mysqli_query($conn, $query)) {
    // Set session variables untuk menandakan pengguna berhasil terdaftar
    $_SESSION['signup'] = true;  //Menandakan langsung ke dashboard
    $_SESSION['id'] = mysqli_insert_id($conn); // Mengambil id terakhir yang dimasukkan
    $_SESSION['username'] = $nama;
    $_SESSION['no_rm'] = $no_rm;
    $_SESSION['akses'] = 'pasien';

    // Redirect ke halaman dashboard pasien setelah sukses mendaftar
    echo "<meta http-equiv='refresh' content='0; url=../pasien'>";
    die(); // Menghentikan eksekusi script
  } else {
    // Menampilkan pesan error jika query gagal
    echo "Error: " . $query . "<br>" . mysqli_error($conn);
  }

  // Tutup koneksi database
  mysqli_close($conn);
}
?>

<!-- HTML untuk form registrasi pasien -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Poliklinik | Registration Page (v2)</title>

  <!-- Link ke file CSS untuk tampilan -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="../../index2.html" class="h1"><b>Poli</b>klinik</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Register a new account</p>

      <!-- Form pendaftaran -->
      <form action="" method="post">
        <!-- Nama -->
        <div class="input-group mb-3">
          <input type="text" class="form-control" required placeholder="Full name" name="nama" >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>

        <!-- Alamat -->
        <div class="input-group mb-3">
          <input type="text" class="form-control" required placeholder="Alamat" name="alamat" >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fa fa-map-marker"></span>
            </div>
          </div>
        </div>

        <!-- Nomor KTP -->
        <div class="input-group mb-3">
          <input type="number" class="form-control" required placeholder="No KTP" name="no_ktp" >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fa fa-address-book"></span>
            </div>
          </div>
        </div>

        <!-- Nomor HP -->
        <div class="input-group mb-3">
          <input type="number" class="form-control" required placeholder="NO HP" name="no_hp" >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-phone-square"></span>
            </div>
          </div>
        </div>
        
        <!-- Checkbox untuk menyetujui syarat dan ketentuan -->
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="agreeTerms" required name="terms" value="agree">
              <label for="agreeTerms">
               I agree to the <a href="#">terms</a>
              </label>
            </div>
          </div>
          <!-- Button untuk submit form -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
          </div>
        </div>
      </form>

      <!-- Link untuk login jika sudah memiliki akun -->
      <div class="row">
        <div class="col-12">
          <a href="../../pages/auth/login-pasien.php">already have an account?</a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Script JavaScript untuk fungsionalitas form -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>

</body>
</html>
