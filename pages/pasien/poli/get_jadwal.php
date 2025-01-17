<?php
// Menyertakan file koneksi ke database
include_once("../../../config/conn.php");

// Ambil ID poli dari parameter GET
$poliId = isset($_GET['poli_id']) ? $_GET['poli_id'] : null; // Memastikan nilai poli_id dari request GET

// Query untuk mendapatkan jadwal dokter berdasarkan ID poli
$dataJadwal = $pdo->prepare("
    SELECT 
        a.nama as nama_dokter,       -- Nama dokter
        b.hari as hari,             -- Hari jadwal
        b.id as id_jp,              -- ID jadwal pemeriksaan
        b.jam_mulai as jam_mulai,   -- Jam mulai pemeriksaan
        b.jam_selesai as jam_selesai -- Jam selesai pemeriksaan
    FROM dokter as a
    INNER JOIN jadwal_periksa as b
    ON a.id = b.id_dokter
    WHERE b.aktif = 'Y' AND a.id_poli = :poli_id"); // Hanya menampilkan jadwal aktif dan berdasarkan ID poli
$dataJadwal->bindParam(':poli_id', $poliId); // Menghubungkan parameter ID poli
$dataJadwal->execute(); // Menjalankan query

// Membuat opsi jadwal dokter untuk dropdown
if ($dataJadwal->rowCount() == 0) {
    // Jika tidak ada jadwal ditemukan, tampilkan opsi default
    echo '<option>Tidak ada jadwal</option>';
} else {
    // Jika ada jadwal, tampilkan setiap jadwal dalam opsi dropdown
    while ($jd = $dataJadwal->fetch()) {
        echo '<option value="' . $jd['id_jp'] . '"> Dokter ' . htmlspecialchars($jd['nama_dokter']) . 
             ' | ' . htmlspecialchars($jd['hari']) . 
             ' | ' . htmlspecialchars($jd['jam_mulai']) . ' - ' . htmlspecialchars($jd['jam_selesai']) . 
             '</option>';
    }
}
?>
