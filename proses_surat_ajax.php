<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

if (isset($_POST['id']) && isset($_POST['aksi'])) {
    $id = intval($_POST['id']);
    $aksi = $_POST['aksi'];
    
    // Validasi aksi
    if (!in_array($aksi, ['terima', 'tolak'])) {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        exit();
    }
    
    // Update status di database
    $status = ($aksi == 'terima') ? 'diterima' : 'ditolak';
    $sql = "UPDATE pengajuan_surat SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
}
?>