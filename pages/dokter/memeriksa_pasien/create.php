<?php
// Memasukkan file konfigurasi untuk koneksi database
include_once "../../../config/conn.php";

// Memulai sesi
session_start();

// Mengecek apakah sesi login sudah ada, jika tidak, mengalihkan ke halaman login
if (isset($_SESSION['login'])) {
    $_SESSION['login'] = true;  // Menandakan sesi login aktif
} else {
    echo "<meta http-equiv='refresh' content='0; url=..'>";  // Redirect jika tidak login
    die();
}

// Mendapatkan data dari sesi untuk username, akses, dan id dokter
$nama = $_SESSION['username'];
$akses = $_SESSION['akses'];
$id_dokter = $_SESSION['id'];

// Mengecek apakah pengguna memiliki akses sebagai 'dokter', jika tidak, alihkan ke halaman sebelumnya
if ($akses != 'dokter') {
    echo "<meta http-equiv='refresh' content='0; url=..'>";  // Redirect jika akses bukan dokter
    die();
}

// Mendapatkan ID pasien dari URL
$url = $_SERVER['REQUEST_URI'];
$url = explode("/", $url);
$id = $url[count($url) - 1];

// Query untuk mendapatkan data obat-obatan yang tersedia
$obat = query("SELECT * FROM obat");

// Query untuk mendapatkan data pasien berdasarkan id yang diterima
$pasiens = query("SELECT
                    p.nama AS nama_pasien,
                    dp.id AS id_daftar_poli
                FROM pasien p
                INNER JOIN daftar_poli dp ON p.id = dp.id_pasien
                WHERE p.id = '$id'")[0];

// Biaya periksa dan variabel total biaya obat
$biaya_periksa = 150000;
$total_biaya_obat = 0;
?>

<?php
// Menyiapkan judul halaman
$title = 'Poliklinik | Periksa Pasien';

// Bagian breadcrumb untuk navigasi
ob_start(); ?>
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="<?= $base_dokter; ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= $base_dokter . '/memeriksa_pasien'; ?>">Daftar Periksa</a></li>
    <li class="breadcrumb-item active">Periksa Pasien</li>
</ol>
<?php
$breadcrumb = ob_get_clean();
// ob_flush();

// Menyiapkan judul utama halaman
ob_start(); ?>
Periksa Pasien
<?php
$main_title = ob_get_clean();
// ob_flush();

// Bagian konten utama halaman
ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Periksa Pasien</h3>
    </div>
    <div class="card-body">
        <!-- Form untuk melakukan pemeriksaan pasien -->
        <form action="" method="POST">
            <!-- Kolom input untuk nama pasien (hanya bisa dibaca) -->
            <div class="form-group">
                <label for="nama_pasien">Nama Pasien</label>
                <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" value="<?= $pasiens["nama_pasien"] ?>" disabled>
            </div>

            <!-- Kolom input untuk tanggal periksa -->
            <div class="form-group">
                <label for="tgl_periksa">Tanggal Periksa</label>
                <input type="datetime-local" class="form-control" id="tgl_periksa" name="tgl_periksa">
            </div>

            <!-- Kolom input untuk catatan pemeriksaan -->
            <div class="form-group">
                <label for="catatan">Catatan</label>
                <input type="text" class="form-control" id="catatan" name="catatan">
            </div>

            <!-- Kolom untuk memilih obat-obatan -->
            <div class="form-group">
                <label for="nama_pasien">Obat</label>
                <select class="form-control" name="obat[]" multiple id="id_obat">
                    <?php foreach ($obat as $obats) : ?>
                        <!-- Menampilkan daftar obat dalam dropdown dengan harga obat tertera -->
                        <option value="<?= $obats['id']; ?>|<?= $obats['harga'] ?>"><?= $obats['nama_obat']; ?> - <?= $obats['kemasan']; ?> - Rp.<?= $obats['harga']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kolom untuk menampilkan total biaya -->
            <div class="form-group">
                <label for="total_harga">Total Harga</label>
                <input type="text" class="form-control" id="harga" name="harga" readonly>
            </div>

            <!-- Tombol simpan untuk mengirim form -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" id="simpan_periksa" name="simpan_periksa">
                    <i class="fa fa-save"></i> Simpan</button>
            </div>
        </form>

        <?php
        // Mengecek jika tombol simpan ditekan
        if (isset($_POST['simpan_periksa'])) {
            $tgl_periksa = $_POST['tgl_periksa'];
            $catatan = $_POST['catatan'];
            $obat = $_POST['obat'];
            $id_daftar_poli = $pasiens['id_daftar_poli'];
            $id_obat = [];
            
            // Memproses data obat yang dipilih dan menghitung total biaya obat
            for ($i = 0; $i < count($obat); $i++) {
                $data_obat = explode("|", $obat[$i]);
                $id_obat[] = $data_obat[0];  // Menyimpan ID obat
                $total_biaya_obat += $data_obat[1];  // Menambahkan biaya obat
            }
            $total_biaya = $biaya_periksa + $total_biaya_obat;  // Menghitung total biaya

            // Menyimpan data pemeriksaan ke dalam database
            $query = "INSERT INTO periksa (id_daftar_poli, tgl_periksa, catatan, biaya_periksa) VALUES
                    ($id_daftar_poli, '$tgl_periksa', '$catatan', '$total_biaya')";
            $result = mysqli_query($conn, $query);

            // Menyimpan detail obat yang diberikan dalam pemeriksaan
            $query2 = "INSERT INTO detail_periksa (id_obat, id_periksa) VALUES ";
            $periksa_id = mysqli_insert_id($conn);  // Mendapatkan ID pemeriksaan terbaru
            for ($i = 0; $i < count($id_obat); $i++) {
                $query2 .= "($id_obat[$i], $periksa_id),";
            }
            $query2 = substr($query2, 0, -1);  // Menghapus koma terakhir
            $result2 = mysqli_query($conn, $query2);

            // Mengupdate status periksa pasien
            $query3 = "UPDATE daftar_poli SET status_periksa = '1'
                        WHERE id = $id_daftar_poli";
            $result3 = mysqli_query($conn, $query3);

            // Menampilkan pesan sukses atau gagal
            if ($result && $result2 && $result3) {
                echo "
          <script>
            alert('Data berhasil diubah');
            document.location.href = '../ ';
          </script>
        ";
            } else {
                echo "
          <script>
            alert('Data gagal diubah');
            alert('$query');
            document.location.href = '../edit.php/$id';
          </script>
        ";
            }
        }
        ?>
    </div>
</div>

<!-- Script untuk menghitung total biaya obat secara otomatis -->
<script>
    $(document).ready(function() {
        $('#id_obat').select2();  // Menggunakan plugin select2 untuk dropdown obat
        $('#id_obat').on('change.select2', function (e) {
            var selectedValuesArray = $(this).val();
            
            // Menghitung total biaya
            var sum = 150000;  // Biaya pemeriksaan
            if (selectedValuesArray) {
                for (var i = 0; i < selectedValuesArray.length; i++) {
                    // Memisahkan nilai yang dipilih dan mengambil harga obat
                    var parts = selectedValuesArray[i].split("|");
                    if (parts.length === 2) {
                        sum += parseFloat(parts[1]);
                    }
                }
            }
            $('#harga').val(sum);  // Menampilkan total biaya pada input harga
        });
    });
</script>
<?php
$content = ob_get_clean();
// ob_flush();
?>

<?php include_once "../../../layouts/index.php"; ?>
