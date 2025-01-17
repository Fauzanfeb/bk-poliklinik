<?php
include_once("../../../config/conn.php"); // Memasukkan file koneksi database
session_start(); // Memulai sesi

// Mengecek apakah pengguna sudah login
if (isset($_SESSION['login'])) {
  $_SESSION['login'] = true; // Menandakan pengguna telah login
} else {
  echo "<meta http-equiv='refresh' content='0; url=../auth/login.php'>"; // Redirect ke halaman login jika belum login
  die(); // Menghentikan eksekusi kode lebih lanjut
}

$nama = $_SESSION['username']; // Mengambil nama pengguna yang login
$akses = $_SESSION['akses']; // Mengambil akses pengguna (dokter)

if ($akses != 'dokter') { // Mengecek apakah yang login adalah dokter
  echo "<meta http-equiv='refresh' content='0; url=../..'>"; // Redirect ke halaman lain jika bukan dokter
  die(); // Menghentikan eksekusi kode lebih lanjut
}

// Query untuk mendapatkan ID dokter berdasarkan nama yang login
$stmt = $pdo->prepare("SELECT id FROM dokter WHERE nama = ?");
$stmt->execute([$nama]); // Menjalankan query
$dokter = $stmt->fetch(); // Mengambil data dokter
$id_dokter = $dokter['id']; // Menyimpan ID dokter dalam variabel
?>
<?php
$title = 'Poliklinik | Riwayat Pasien'; // Menetapkan judul halaman
ob_start(); ?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?= $base_dokter; ?>">Home</a></li> <!-- Tautan menuju halaman home -->
  <li class="breadcrumb-item active">Riwayat Pasien</li> <!-- Menandakan halaman aktif -->
</ol>
<?php
$breadcrumb = ob_get_clean(); // Menyimpan breadcrumb dalam variabel

ob_start(); ?>
Riwayat Pasien <!-- Judul utama halaman -->
<?php
$main_title = ob_get_clean(); // Menyimpan judul utama halaman dalam variabel

ob_start(); ?>
<div class="card"> <!-- Card untuk menampilkan informasi pasien -->
  <div class="card-header">
    <h3 class="card-title">Daftar Riwayat Pasien</h3> <!-- Judul daftar riwayat pasien -->
  </div>
  <div class="card-body">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>No</th> <!-- Nomor urut -->
          <th>Nama Pasien</th> <!-- Nama pasien -->
          <th>Alamat</th> <!-- Alamat pasien -->
          <th>No. KTP</th> <!-- Nomor KTP pasien -->
          <th>No. Telepon</th> <!-- Nomor telepon pasien -->
          <th>No. RM</th> <!-- Nomor rekam medis pasien -->
          <th>Aksi</th> <!-- Tindakan untuk melihat detail -->
        </tr>
      </thead>
      <tbody>
        <?php
        $index = 1; // Variabel untuk nomor urut
        // Query untuk mendapatkan pasien yang terdaftar dengan dokter yang login
        $query = "SELECT DISTINCT p.* 
                 FROM pasien p 
                 INNER JOIN daftar_poli dp ON p.id = dp.id_pasien
                 INNER JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id 
                 WHERE jp.id_dokter = :id_dokter";
        $stmt = $pdo->prepare($query); // Menyiapkan query
        $stmt->execute(['id_dokter' => $id_dokter]); // Menjalankan query dengan parameter ID dokter
        
        if ($stmt->rowCount() == 0) { // Jika tidak ada data pasien
          echo "<tr><td colspan='7' align='center'>Tidak ada data</td></tr>"; // Menampilkan pesan jika tidak ada pasien
        } else {
          while ($d = $stmt->fetch()) { // Mengambil data pasien dari query
        ?>
            <tr>
              <td><?= $index++; ?></td> <!-- Menampilkan nomor urut pasien -->
              <td><?= $d['nama']; ?></td> <!-- Menampilkan nama pasien -->
              <td><?= $d['alamat']; ?></td> <!-- Menampilkan alamat pasien -->
              <td><?= $d['no_ktp']; ?></td> <!-- Menampilkan nomor KTP pasien -->
              <td><?= $d['no_hp']; ?></td> <!-- Menampilkan nomor telepon pasien -->
              <td><?= $d['no_rm']; ?></td> <!-- Menampilkan nomor rekam medis pasien -->
              <td>
                <!-- Tombol untuk melihat detail riwayat periksa pasien -->
                <button data-toggle="modal" data-target="#detailModal<?= $d['id'] ?>" class="btn btn-info btn-sm">
                  <i class="fa fa-eye"></i> Detail Riwayat Periksa
                </button>
              </td>
            </tr>
            <!-- Modal untuk menampilkan detail riwayat periksa pasien -->
          <?php
          $no = 1;
          $pasien_id = $d['id']; // ID pasien
          // Query untuk mendapatkan detail periksa pasien oleh dokter yang login
          $detail_query = "SELECT 
                            p.nama AS 'nama_pasien',
                            pr.*,
                            d.nama AS 'nama_dokter',
                            dpo.keluhan AS 'keluhan',
                            GROUP_CONCAT(o.nama_obat SEPARATOR ', ') AS 'obat'
                        FROM periksa pr
                        LEFT JOIN daftar_poli dpo ON (pr.id_daftar_poli = dpo.id)
                        LEFT JOIN jadwal_periksa jp ON (dpo.id_jadwal = jp.id)
                        LEFT JOIN dokter d ON (jp.id_dokter = d.id)
                        LEFT JOIN pasien p ON (dpo.id_pasien = p.id)
                        LEFT JOIN detail_periksa dp ON (pr.id = dp.id_periksa)
                        LEFT JOIN obat o ON (dp.id_obat = o.id)
                        WHERE dpo.id_pasien = :pasien_id
                        AND jp.id_dokter = :dokter_id
                        GROUP BY pr.id
                        ORDER BY pr.tgl_periksa DESC"; // Query untuk mengambil riwayat periksa pasien oleh dokter

          $stmt2 = $pdo->prepare($detail_query); // Menyiapkan query detail periksa
          $stmt2->execute([
            'pasien_id' => $pasien_id,
            'dokter_id' => $id_dokter
          ]); // Menjalankan query dengan parameter pasien dan dokter
          ?>
          <div class="modal fade" id="detailModal<?= $d['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true" data-backdrop="static" >
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalScrollableTitle">Riwayat <?= $d['nama'] ?></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <?php if ($stmt2->rowCount() == 0) : ?>
                    <h5>Tidak Ditemukan Riwayat Periksa</h5> <!-- Menampilkan pesan jika tidak ada riwayat periksa -->
                  <?php else : ?>
                    <div class="grid-container">
                      <!-- Menampilkan data riwayat periksa pasien dalam format grid -->
                      <div class="grid-item">No</div>
                      <div class="grid-item">Tanggal Periksa</div>
                      <div class="grid-item">Nama Pasien</div>
                      <div class="grid-item">Nama Dokter</div>
                      <div class="grid-item">Keluhan</div>
                      <div class="grid-item">Catatan</div>
                      <div class="grid-item">Obat</div>
                      <div class="grid-item">Biaya Periksa</div>
                      <?php while ($da = $stmt2->fetch()) : ?>
                        <div class="grid-item"><?= $no++; ?></div> <!-- Nomor urut riwayat periksa -->
                        <div class="grid-item"><?= $da['tgl_periksa']; ?></div> <!-- Tanggal periksa -->
                        <div class="grid-item"><?= $da['nama_pasien']; ?></div> <!-- Nama pasien -->
                        <div class="grid-item"><?= $da['nama_dokter']; ?></div> <!-- Nama dokter -->
                        <div class="grid-item"><?= $da['keluhan']; ?></div> <!-- Keluhan pasien -->
                        <div class="grid-item"><?= $da['catatan']; ?></div> <!-- Catatan dokter -->
                        <div class="grid-item"><?= $da['obat']; ?></div> <!-- Obat yang diberikan -->
                        <div class="grid-item"><?= formatRupiah($da['biaya_periksa']); ?></div> <!-- Biaya pemeriksaan -->
                      <?php endwhile ?>
                      <?php $no = 1; ?>
                    </div>
                  <?php endif ?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <!-- Tombol untuk menutup modal -->
                </div>
              </div>
            </div>
          </div>
        <?php }
        } ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean(); // Menyimpan konten halaman dalam variabel
?>

<?php include '../../../layouts/index.php'; ?> <!-- Memasukkan layout utama halaman -->
