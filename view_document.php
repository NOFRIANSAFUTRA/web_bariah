<?php
require_once 'config.php';
session_start();

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

// Ambil data pengajuan berdasarkan ID
$query = "SELECT dokumen_pendukung FROM pengajuan_surat WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data && !empty($data['dokumen_pendukung'])) {
    $file_path = $data['dokumen_pendukung'];
    
    // Deteksi tipe file
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png'])) {
        echo "<img src='$file_path' style='max-width:100%; height:auto;'>";
    } elseif ($ext === 'pdf') {
        echo "<embed src='$file_path' type='application/pdf' width='100%' height='600px'>";
    } else {
        echo "File tidak dapat ditampilkan.";
    }
} else {
    echo "Dokumen tidak ditemukan.";
}
