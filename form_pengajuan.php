<?php
// Load konfigurasi database
require_once 'config.php';

session_start();

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil jenis surat dari parameter GET
$jenis_surat = $_GET['jenis'] ?? '';
$nama_surat = '';

// Tentukan nama surat berdasarkan jenis
switch ($jenis_surat) {
    case 'pengantar':
        $nama_surat = 'Surat Pengantar';
        break;
    case 'domisili':
        $nama_surat = 'Surat Domisili';
        break;
    case 'usaha':
        $nama_surat = 'Surat Keterangan Usaha';
        break;
    case 'tidak-mampu':
        $nama_surat = 'Surat Keterangan Tidak Mampu';
        break;
    case 'meninggal':
        $nama_surat = 'Surat Keterangan Meninggal';
        break;
    case 'rekomendasi-nikah':
        $nama_surat = 'Rekomendasi Nikah';
        break;
    default:
        header("Location: dashboard.php");
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

// Jika user tidak ditemukan
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keperluan = htmlspecialchars($_POST['keperluan']);
    $keterangan = htmlspecialchars($_POST['keterangan']);
    
    // Handle file upload
    $target_dir = "uploads/";
    $file_name = basename($_FILES["dokumen_pendukung"]["name"]);
    $target_file = $target_dir . uniqid() . '_' . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check file size (max 2MB)
    if ($_FILES["dokumen_pendukung"]["size"] > 2000000) {
        $error = "Maaf, file terlalu besar. Maksimal 2MB.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowed_types)) {
        $error = "Maaf, hanya file PDF, JPG, JPEG, PNG yang diperbolehkan.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["dokumen_pendukung"]["tmp_name"], $target_file)) {
            // Simpan ke database
            $query = "INSERT INTO pengajuan_surat 
                      (user_id, jenis_surat, keperluan, keterangan, dokumen_pendukung, status, tanggal_pengajuan) 
                      VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issss", $user_id, $jenis_surat, $keperluan, $keterangan, $target_file);
            
            if ($stmt->execute()) {
                $success = "Pengajuan surat berhasil dikirim!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan <?= $nama_surat ?> - Kecamatan Simpang Kiri</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Gunakan style yang sama dengan dashboard atau sesuaikan */
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        textarea.form-control {
            min-height: 120px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .file-upload {
            display: flex;
            flex-direction: column;
        }
        
        .file-upload-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h2><i class="fas fa-file-alt"></i> Form Pengajuan <?= $nama_surat ?></h2>
            <p>Isi form berikut dengan data yang valid dan lengkap</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="jenis_surat" value="<?= $jenis_surat ?>">
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="keperluan">Keperluan Surat *</label>
                <input type="text" id="keperluan" name="keperluan" class="form-control" required placeholder="Contoh: Pengurusan KTP, Pendaftaran Sekolah, dll">
            </div>
            
            <div class="form-group">
                <label for="keterangan">Keterangan Tambahan</label>
                <textarea id="keterangan" name="keterangan" class="form-control" placeholder="Jelaskan secara rinci keperluan surat ini"></textarea>
            </div>
            
            <div class="form-group">
                <label for="dokumen_pendukung">Dokumen Pendukung *</label>
                <div class="file-upload">
                    <input type="file" id="dokumen_pendukung" name="dokumen_pendukung" class="form-control" required>
                    <span class="file-upload-info">Format: PDF, JPG, PNG (maks. 2MB). Contoh: Scan KTP, KK, atau dokumen pendukung lainnya.</span>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="dashboard_user.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</body>
</html>