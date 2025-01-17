<?php
// Mengimpor koneksi database
include_once("../../../config/conn.php");
// Memulai sesi
session_start();

// Mengecek apakah user sudah login
if (isset($_SESSION['login'])) {
  $_SESSION['login'] = true;
} else {
  // Jika belum login, arahkan ke halaman login
  echo "<meta http-equiv='refresh' content='0; url=../auth/login.php'>";
  die(); // Menghentikan eksekusi lebih lanjut
}

// Mengambil data pengguna yang sedang login
$nama = $_SESSION['username'];
$akses = $_SESSION['akses'];
$id_dokter = $_SESSION['id']; // ID dokter yang login

// Mengecek apakah akses yang dimiliki adalah dokter
if ($akses != 'dokter') {
  // Jika akses bukan dokter, arahkan ke halaman utama
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

// Mengambil data pasien yang terkait dengan dokter yang sedang login
$pasien = query("SELECT
                  periksa.id AS id_periksa,
                  pasien.id AS id_pasien,
                  periksa.catatan AS catatan,
                  daftar_poli.no_antrian AS no_antrian, 
                  pasien.nama AS nama_pasien, 
                  daftar_poli.keluhan AS keluhan,
                  daftar_poli.status_periksa AS status_periksa
                FROM pasien 
                INNER JOIN daftar_poli ON pasien.id = daftar_poli.id_pasien
                LEFT JOIN periksa ON daftar_poli.id = periksa.id_daftar_poli
                INNER JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
                WHERE jadwal_periksa.id_dokter = $id_dokter"); // Mengambil data berdasarkan id dokter

// Mengambil data pemeriksaan (periksa)
$periksa = query("SELECT * from periksa");

// Mengambil data obat-obatan
$obat = query("SELECT * FROM obat");
?>

<?php
// Menentukan title halaman
$title = 'Poliklinik | Daftar Periksa Pasien';

// Membuat breadcrumb untuk navigasi
ob_start(); ?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?= $base_dokter; ?>">Home</a></li>
  <li class="breadcrumb-item active">Daftar Periksa</li>
</ol>
<?php
$breadcrumb = ob_get_clean(); // Menyimpan breadcrumb untuk digunakan nanti

// Menentukan main title halaman
ob_start(); ?>
Daftar Periksa Pasien
<?php
$main_title = ob_get_clean(); // Menyimpan main title untuk digunakan nanti
?>

<?php
// Membuat konten utama halaman
ob_start();
?>
        <div class="card">
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 8%">No Urut</th>
                  <th style="width: 40%">Nama Pasien</th>
                  <th style="width: 40%">Keluhan</th>
                  <th style="width: 15%">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pasien as $pasiens) : ?>
                  <tr>
                    <td id="id" class="text-center"><?= $pasiens["no_antrian"] ?></td>
                    <td><?= $pasiens["nama_pasien"] ?></td>
                    <td><?= $pasiens["keluhan"] ?></td>

                    <td>
                      <?php if ($pasiens["status_periksa"] == 0) { ?>
                        <!-- Menampilkan tombol "Periksa" jika pasien belum diperiksa -->
                        <a href="create.php/<?= $pasiens['id_pasien'] ?>" class="btn btn-primary"><i class="fas fa-stethoscope"></i> Periksa </a>
                        <?php } else { ?>
                          <!-- Menampilkan tombol "Edit" jika pasien sudah diperiksa -->
                          <a href="edit.php/<?= $pasiens['id_periksa'] ?>" class="btn btn-warning"><i class="fa fa-edit"></i> Edit </a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal untuk memasukkan detail pemeriksaan -->
        <div class="modal fade" id="modalTambahPeriksa" tabindex="-1" role="dialog" aria-labelledby="modalTambahPeriksaLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalTambahPeriksaLabel">Detail Periksa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Form untuk menambahkan data pemeriksaan -->
                <form action="" method="POST">
                  <div class="form-group">
                    <label for="nama_pasien">Nama Pasien</label>
                    <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" value="<?= $pasiens["nama_pasien"] ?>" disabled>
                  </div>
                  
                  <div class="form-group">
                    <label for="tgl_periksa">Tanggal Periksa</label>
                    <input type="datetime-local" class="form-control" id="tgl_periksa" name="tgl_periksa" value="<?= $pariksa["tgl_periksa"] ?>">
                  </div>
                  
                  <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <input type="text" class="form-control" id="catatan" name="catatan" value="<?= $pasiens["catatan"] ?>">
                  </div>

                  <div class="form-group">
                    <label for="nama_pasien">Obat</label>
                    <select multiple="" class="form-control">
                      <?php foreach ($obat as $obats) : ?>
                        <option value="<?= $obats['id']; ?>"><?= $obats['nama_obat']; ?> - <?= $obats['kemasan']; ?> - Rp.<?= $obats['harga']; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Tombol untuk menyimpan data pemeriksaan -->
                  <button type="submit" class="btn btn-primary" id="simpan_periksa" name="simpan_periksa">Simpan</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <?php
        // Mengecek apakah tombol simpan periksa sudah ditekan
        if (isset($_POST['simpan_periksa'])) {
          // Mengecek apakah ID sudah ada
          if (isset($_POST['$id'])) {
            try {
              $tgl_periksa = mysqli_real_escape_string($conn, $_POST['tgl_periksa']);
              $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

              // Menyimpan data pemeriksaan ke database
              $query = "INSERT INTO pasien VALUES ('', '$tgl_periksa', '$catatan')";
              mysqli_query($conn, $query);
            } catch (\Exception $e) {
              var_dump($e->getMessage()); // Menampilkan pesan error jika terjadi kesalahan
            }
          }
        }
        ?>
    </div>

<?php
// Menyimpan konten utama halaman
$content = ob_get_clean();
?>

<!-- Menyertakan layout halaman utama -->
<?php include_once "../../../layouts/index.php"; ?>
