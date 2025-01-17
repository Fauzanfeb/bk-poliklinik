<?php
include_once "../../../config/conn.php"; // Menghubungkan ke file konfigurasi untuk koneksi database
session_start(); // Memulai session untuk melacak status login pengguna

if (isset($_SESSION['login'])) { // Mengecek apakah user sudah login
  $_SESSION['login'] = true; // Jika sudah login, tetapkan session login
} else {
  echo "<meta http-equiv='refresh' content='0; url=..'>"; // Jika belum login, redirect ke halaman sebelumnya
  die(); // Menghentikan eksekusi script setelah redirect
}

$nama = $_SESSION['username']; // Menyimpan nama pengguna yang login
$akses = $_SESSION['akses']; // Menyimpan level akses pengguna (misalnya dokter, admin, dll)
$id_dokter = $_SESSION['id']; // Menyimpan ID dokter yang login

if ($akses != 'dokter') { // Mengecek apakah pengguna memiliki akses sebagai dokter
  echo "<meta http-equiv='refresh' content='0; url=..'>"; // Jika bukan dokter, redirect ke halaman sebelumnya
  die(); // Menghentikan eksekusi script
}

$url = $_SERVER['REQUEST_URI']; // Mendapatkan URL dari request saat ini
$url = explode("/", $url); // Memecah URL berdasarkan '/'
$id = $url[count($url) - 1]; // Mendapatkan ID dari parameter URL terakhir

// Query untuk mengambil data pasien berdasarkan ID
$pasiens = query("SELECT
                          pasien.id AS id_pasien,
                          periksa.biaya_periksa AS biaya_periksa,
                          pasien.nama AS nama_pasien,
                          periksa.catatan AS catatan,
                          periksa.tgl_periksa AS tgl_periksa,
                          daftar_poli.id AS id_daftar_poli,
                          daftar_poli.no_antrian AS no_antrian,
                          daftar_poli.keluhan AS keluhan,
                          daftar_poli.status_periksa AS status_periksa
                        FROM pasien
                        INNER JOIN daftar_poli ON pasien.id = daftar_poli.id_pasien
                        INNER JOIN periksa ON daftar_poli.id = periksa.id_daftar_poli
                        WHERE periksa.id = '$id'")[0];

// Query untuk mengambil data obat yang tersedia
$obat = query("SELECT * FROM obat");

$selected_obat = []; // Inisialisasi array untuk obat yang dipilih
// Query untuk mengambil detail obat yang sudah dipilih sebelumnya
$detail_periksa = query("SELECT * FROM detail_periksa WHERE id_periksa='" . $id . "'");

// Menyimpan ID obat yang sudah dipilih dalam array
foreach ($detail_periksa as $dp) {
  $selected_obat[] = $dp['id_obat'];
}
?>

<?php
$title = 'Poliklinik | Edit Periksa Pasien'; // Menyimpan title halaman

// Breadcrumb section untuk navigasi di halaman
ob_start(); ?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?= $base_dokter; ?>">Home</a></li>
  <li class="breadcrumb-item"><a href="<?= $base_dokter . '/memeriksa_pasien'; ?>">Daftar Periksa</a></li>
  <li class="breadcrumb-item active">Edit Periksa</li>
</ol>
<?php
$breadcrumb = ob_get_clean(); // Menyimpan hasil output breadcrumb untuk ditampilkan nanti

// Title Section
ob_start(); ?>
Edit Periksa Pasien
<?php
$main_title = ob_get_clean(); // Menyimpan hasil output untuk title utama

// Content section
ob_start(); // Memulai buffer output untuk bagian konten
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Edit Periksa</h3>
  </div>
  <div class="card-body">
    <form action="" method="POST"> <!-- Membuat form dengan metode POST -->
      <!-- Kolom input untuk menambahkan data pasien -->
      <div class="form-group">
        <label for="nama_pasien">Nama Pasien</label>
        <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" value="<?= $pasiens["nama_pasien"] ?>" disabled> <!-- Menampilkan nama pasien yang tidak dapat diedit -->
      </div>

      <div class="form-group">
        <label for="tgl_periksa">Tanggal Periksa</label>
        <input type="datetime-local" class="form-control" id="tgl_periksa" name="tgl_periksa" value="<?= $pasiens["tgl_periksa"] ?>"> <!-- Kolom input untuk tanggal periksa -->
      </div>

      <div class="form-group">
        <label for="catatan">Catatan</label>
        <input type="text" class="form-control" id="catatan" name="catatan" value="<?= $pasiens["catatan"] ?>"> <!-- Kolom input untuk catatan pemeriksaan -->
      </div>

      <div class="form-group">
        <label for="nama_pasien">Obat</label>
        <select multiple="" class="form-control" name="obat[]" id="id_obat" multiple> <!-- Select untuk memilih obat yang akan diberikan -->
          <?php foreach ($obat as $obats) : ?>
            <?= var_dump($selected_obat); ?> <!-- Debug untuk melihat array obat yang dipilih -->
            <?php if (in_array($obats['id'], $selected_obat)) : ?>
              <option value="<?= $obats['id']; ?>|<?= $obats['harga'] ?>" selected><?= $obats['nama_obat']; ?> - <?= $obats['kemasan']; ?> - Rp.<?= $obats['harga']; ?></option> <!-- Menampilkan obat yang dipilih sebelumnya -->
            <?php else : ?>
              <option value="<?= $obats['id']; ?>|<?= $obats['harga'] ?>"> <?= $obats['nama_obat']; ?> - <?= $obats['kemasan']; ?> - Rp.<?= $obats['harga']; ?></option> <!-- Menampilkan obat yang tidak dipilih -->
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="total_harga">Total Harga</label>
        <input type="text" class="form-control" id="harga" name="harga" readonly value="<?= $pasiens["biaya_periksa"] ?>"> <!-- Menampilkan total biaya pemeriksaan, tidak dapat diedit -->
      </div>

      <!-- Tombol untuk mengirim form -->
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="simpan_periksa" name="simpan_periksa">
          <i class="fa fa-save"></i> Simpan</button> <!-- Tombol untuk menyimpan perubahan -->
      </div>
    </form>

    <?php
    if (isset($_POST['simpan_periksa'])) { // Mengecek apakah form disubmit
      $biaya_periksa = 150000; // Biaya periksa tetap
      $total_biaya_obat = 0; // Inisialisasi total biaya obat
      $obat = $_POST['obat']; // Mengambil data obat yang dipilih dari form
      $tgl_periksa = $_POST['tgl_periksa']; // Mengambil data tanggal periksa
      $catatan = $_POST['catatan']; // Mengambil data catatan
      $id_obat = []; // Inisialisasi array untuk menyimpan ID obat
      for ($i = 0; $i < count($obat); $i++) { // Loop untuk menghitung total biaya obat
        $data_obat = explode("|", $obat[$i]); // Memecah data obat berdasarkan tanda '|'
        $id_obat[] = $data_obat[0]; // Menyimpan ID obat
        $total_biaya_obat += $data_obat[1]; // Menambahkan harga obat ke total biaya obat
      }
      $total_biaya = $biaya_periksa + $total_biaya_obat; // Menghitung total biaya periksa dengan biaya obat

      $id_daftar_poli = $pasiens['id_daftar_poli']; // Mendapatkan ID pendaftaran poli
      // Query untuk memperbarui data pemeriksaan
      $query = "UPDATE periksa SET
                    tgl_periksa = '$tgl_periksa',
                    catatan = '$catatan',
                    biaya_periksa = '$total_biaya'
                  WHERE id_daftar_poli = $id_daftar_poli";
      // Query untuk menghapus detail pemeriksaan sebelumnya
      $query2 = "DELETE FROM detail_periksa WHERE id_periksa = $id";
      // Query untuk memasukkan detail obat yang baru
      $query3 = "INSERT INTO detail_periksa (id_obat, id_periksa) VALUES ";

      for ($i = 0; $i < count($id_obat); $i++) { // Loop untuk memasukkan obat baru ke dalam query
        $query3 .= "($id_obat[$i], $id),";
      }

      $query3 = substr($query3, 0, -1); // Menghapus koma terakhir

      $result = mysqli_query($conn, $query); // Menjalankan query untuk update periksa
      $result2 = mysqli_query($conn, $query2); // Menjalankan query untuk hapus detail sebelumnya
      $result3 = mysqli_query($conn, $query3); // Menjalankan query untuk memasukkan detail obat baru

      if ($result && $result2 && $result3) { // Mengecek apakah semua query berhasil
        echo "
          <script>
            alert('Data berhasil diubah'); // Menampilkan pesan sukses
            document.location.href = '../ '; // Redirect ke halaman daftar pemeriksaan
          </script>
        ";
      } else {
        echo "
          <script>
            alert('Data gagal diubah'); // Menampilkan pesan gagal
            alert('$query'); // Menampilkan query error
            document.location.href = '../edit.php/$id'; // Redirect kembali ke halaman edit
          </script>
        ";
      }
    }
    ?>
  </div>
</div>
<?php
$main_content = ob_get_clean(); // Menyimpan konten utama
?> 
