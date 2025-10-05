<?php
include "config.php"; // sesuaikan dengan file koneksi kamu

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    // Validasi status agar hanya nilai tertentu yg boleh
    $allowed_status = ['pending', 'diproses', 'diterima', 'ditolak'];
    if (!in_array($status, $allowed_status)) {
        echo "Status tidak valid!";
        exit;
    }

    // Update database
    $query = "UPDATE pengajuan_surat SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "Status berhasil diperbarui menjadi: " . ucfirst($status);
    } else {
        echo "Gagal update status: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Data tidak lengkap!";
}
?>
