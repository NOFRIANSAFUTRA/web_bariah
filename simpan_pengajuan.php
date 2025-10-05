<?php
// Koneksi database
$conn = new mysqli("localhost", "root", "", "kecamatan_simpang_kiri");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari form
$user_id        = $_POST['user_id']; // ini harus dikirim dari session atau form
$jenis_surat_id = $_POST['jenis_surat_id'];
$nama_pemohon   = $_POST['nama_pemohon'];
$nik            = $_POST['nik'];
$jenis_surat    = $_POST['jenis_surat'];
$tanggal_pengajuan = date("Y-m-d"); // otomatis tanggal hari ini
$status         = "pending"; // default
$keterangan     = $_POST['keterangan'] ?? null;

// Query insert sesuai struktur tabel
$stmt = $conn->prepare("INSERT INTO pengajuan_surat 
(user_id, jenis_surat_id, nama_pemohon, nik, jenis_surat, tanggal_pengajuan, status, keterangan) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissssss", $user_id, $jenis_surat_id, $nama_pemohon, $nik, $jenis_surat, $tanggal_pengajuan, $status, $keterangan);

if ($stmt->execute()) {
    echo "Pengajuan surat berhasil disimpan.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
