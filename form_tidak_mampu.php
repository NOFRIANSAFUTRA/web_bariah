<?php
// Load konfigurasi database
require_once 'config.php';
session_start();

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = htmlspecialchars($_POST['alasan']); // alasan kenapa butuh SKTM
    $jumlah_tanggungan = htmlspecialchars($_POST['jumlah_tanggungan']); // jumlah tanggungan keluarga
    $penghasilan = htmlspecialchars($_POST['penghasilan']); // rata-rata penghasilan

    // Handle upload dokumen pendukung (KK, KTP, slip gaji, dll)
    $target_dir = "uploads/";
    $file_name = basename($_FILES["dokumen_pendukung"]["name"]);
    $target_file = $target_dir . uniqid() . '_' . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($_FILES["dokumen_pendukung"]["size"] > 2000000) {
        $error = "Maaf, file terlalu besar. Maksimal 2MB.";
        $uploadOk = 0;
    }

    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowed_types)) {
        $error = "Maaf, hanya file PDF, JPG, JPEG, PNG yang diperbolehkan.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["dokumen_pendukung"]["tmp_name"], $target_file)) {
            $query = "INSERT INTO pengajuan_surat 
                      (user_id, jenis_surat, keperluan, keterangan, dokumen_pendukung, status, tanggal_pengajuan) 
                      VALUES (?, 'tidak-mampu', ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isss", $user_id, $alasan, $jumlah_tanggungan, $target_file);

            if ($stmt->execute()) {
                $success = "Pengajuan Surat Keterangan Tidak Mampu berhasil dikirim!";
            } else {
                $error = "Gagal menyimpan data: " . $conn->error;
            }
        } else {
            $error = "Maaf, terjadi kesalahan saat mengupload file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pengajuan Surat Keterangan Tidak Mampu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; }
        .form-header { text-align: center; margin-bottom: 20px; }
        .form-header h2 { margin: 0; color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        .form-group label { font-weight: bold; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-primary { background: #3498db; color: #fff; }
        .btn-primary:hover { background: #2980b9; }
        .btn-secondary { background: #6c757d; color: #fff; text-decoration: none; }
        .btn-secondary:hover { background: #5a6268; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <div class="form-header">
        <h2><i class="fas fa-hand-holding-heart"></i> Form Surat Keterangan Tidak Mampu</h2>
        <p>Isi form ini untuk mengajukan SKTM</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" readonly>
        </div>

        <div class="form-group">
            <label>Alasan Mengajukan SKTM *</label>
            <textarea name="alasan" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label>Jumlah Tanggungan Keluarga *</label>
            <input type="number" name="jumlah_tanggungan" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Penghasilan Rata-rata per Bulan (Rp) *</label>
            <input type="number" name="penghasilan" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Dokumen Pendukung *</label>
            <input type="file" name="dokumen_pendukung" class="form-control" required>
            <small>Upload scan KK, KTP, atau dokumen lain. Format: PDF/JPG/PNG (maks. 2MB)</small>
        </div>

        <div style="margin-top:20px; display:flex; justify-content:space-between;">
            <a href="dashboard_user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Pengajuan</button>
        </div>
    </form>
</div>
</body>
</html>
