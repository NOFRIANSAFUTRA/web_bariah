<?php
// form_pengajuan.php (disesuaikan untuk Surat Domisili)

// Load konfigurasi database
require_once 'config.php';

session_start();

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil jenis surat dari GET atau POST (lebih fleksibel)
$jenis_surat = $_GET['jenis'] ?? $_POST['jenis'] ?? '';
$nama_surat = '';

// Tentukan nama surat berdasarkan jenis (kembalikan ke dashboard kalau bukan jenis valid)
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

// Inisialisasi pesan
$success = $error = null;

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil input umum
    $keperluan = trim($_POST['keperluan'] ?? '');
    $keterangan_tambahan = trim($_POST['keterangan'] ?? '');

    // Ambil input khusus Domisili
    $alamat = trim($_POST['alamat'] ?? '');
    $rt = trim($_POST['rt'] ?? '');
    $rw = trim($_POST['rw'] ?? '');
    $kelurahan = trim($_POST['kelurahan'] ?? '');
    $kecamatan_field = trim($_POST['kecamatan_field'] ?? '');
    $kode_pos = trim($_POST['kode_pos'] ?? '');
    $lama_tinggal = trim($_POST['lama_tinggal'] ?? '');

    // Validasi minimal
    if ($keperluan === '') {
        $error = "Isi keperluan surat terlebih dahulu.";
    } elseif ($jenis_surat === 'domisili' && $alamat === '') {
        $error = "Alamat lengkap harus diisi untuk Surat Domisili.";
    } elseif (!isset($_FILES['dokumen_pendukung']) || $_FILES['dokumen_pendukung']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Dokumen pendukung (scan KTP/KK/Surat RT/RW) wajib diupload.";
    }

    // Jika belum ada error, proses upload
    if (!$error) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_name = basename($_FILES["dokumen_pendukung"]["name"]);
        $unique_name = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-\_.]/', '_', $file_name);
        $target_file = $target_dir . $unique_name;
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

        // Jika ok, pindahkan file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["dokumen_pendukung"]["tmp_name"], $target_file)) {
                // Buat keterangan gabungan (simpan informasi domisili di satu kolom keterangan)
                $keterangan_full = "Alamat: {$alamat}\nRT/RW: {$rt}/{$rw}\nKel/Desa: {$kelurahan}\nKecamatan: {$kecamatan_field}\nKode Pos: {$kode_pos}\nLama Tinggal: {$lama_tinggal}\n\nKeterangan Tambahan: {$keterangan_tambahan}";

                // Simpan ke database (status default = pending)
                $insertQuery = "INSERT INTO pengajuan_surat 
                      (user_id, jenis_surat, keperluan, keterangan, dokumen_pendukung, status, tanggal_pengajuan) 
                      VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
                $stmtIns = $conn->prepare($insertQuery);
                $stmtIns->bind_param("issss", $user_id, $jenis_surat, $keperluan, $keterangan_full, $target_file);

                if ($stmtIns->execute()) {
                    $success = "Pengajuan surat berhasil dikirim!";
                    // Optional: redirect ke halaman status setelah submit
                    // header("Location: dashboard_user.php?msg=success");
                    // exit();
                } else {
                    $error = "Gagal menyimpan data: " . $conn->error;
                    // Jika gagal simpan, hapus file yang sudah diupload
                    if (file_exists($target_file)) unlink($target_file);
                }
            } else {
                $error = "Maaf, terjadi kesalahan saat mengupload file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan <?= htmlspecialchars($nama_surat) ?> - Kecamatan Simpang Kiri</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* style sama seperti sebelumnya (ringkas) */
        body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background:#f5f7fa; margin:0; }
        .container { max-width: 900px; margin:30px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.06); }
        .form-header { text-align:center; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid #eee; }
        .form-header h2{ margin:0; color:#2c3e50; }
        .alert { padding:12px; border-radius:4px; margin-bottom:15px; }
        .alert-success { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .alert-danger { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }
        .form-group{ margin-bottom:15px; }
        label{ display:block; margin-bottom:6px; color:#495057; font-weight:500; }
        .form-control{ width:100%; padding:10px 12px; border:1px solid #ced4da; border-radius:4px; }
        .small-input{ width:100px; display:inline-block; margin-right:10px; }
        textarea.form-control{ min-height:100px; }
        .form-actions{ display:flex; justify-content:space-between; margin-top:20px; }
        .btn{ padding:10px 16px; border:none; border-radius:4px; cursor:pointer;}
        .btn-primary{ background:#3498db; color:#fff; }
        .btn-secondary{ background:#6c757d; color:#fff; }
        .note{ font-size:0.9rem; color:#6c757d; }
        @media (max-width:600px){ .form-actions{ flex-direction:column; gap:8px; } .small-input{ width:48%; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h2><i class="fas fa-home"></i> Form Pengajuan <?= htmlspecialchars($nama_surat) ?></h2>
            <p>Isi form berikut dengan data yang valid dan lengkap</p>
        </div>

        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error) && $error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <!-- pastikan jenis dikirim kembali saat POST -->
            <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis_surat) ?>">

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="keperluan">Keperluan Surat *</label>
                <input type="text" id="keperluan" name="keperluan" class="form-control" required placeholder="Contoh: Pendaftaran sekolah, pembukaan rekening, dll" value="<?= htmlspecialchars($_POST['keperluan'] ?? '') ?>">
            </div>

            <h4>Data Domisili</h4>
            <div class="form-group">
                <label for="alamat">Alamat Lengkap *</label>
                <textarea id="alamat" name="alamat" class="form-control" required placeholder="Jalan, Nomor, RT/RW, Desa/Kelurahan"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:150px;">
                    <label for="rt">RT *</label>
                    <input type="text" id="rt" name="rt" class="form-control" placeholder="RT" value="<?= htmlspecialchars($_POST['rt'] ?? '') ?>">
                </div>
                <div style="flex:1; min-width:150px;">
                    <label for="rw">RW *</label>
                    <input type="text" id="rw" name="rw" class="form-control" placeholder="RW" value="<?= htmlspecialchars($_POST['rw'] ?? '') ?>">
                </div>
                <div style="flex:2; min-width:200px;">
                    <label for="kelurahan">Kelurahan / Desa</label>
                    <input type="text" id="kelurahan" name="kelurahan" class="form-control" placeholder="Kelurahan / Desa" value="<?= htmlspecialchars($_POST['kelurahan'] ?? '') ?>">
                </div>
                <div style="flex:2; min-width:200px;">
                    <label for="kecamatan_field">Kecamatan</label>
                    <input type="text" id="kecamatan_field" name="kecamatan_field" class="form-control" placeholder="Kecamatan" value="<?= htmlspecialchars($_POST['kecamatan_field'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:150px;">
                    <label for="kode_pos">Kode Pos</label>
                    <input type="text" id="kode_pos" name="kode_pos" class="form-control" placeholder="Kode Pos" value="<?= htmlspecialchars($_POST['kode_pos'] ?? '') ?>">
                </div>
                <div style="flex:1; min-width:150px;">
                    <label for="lama_tinggal">Lama Tinggal</label>
                    <input type="text" id="lama_tinggal" name="lama_tinggal" class="form-control" placeholder="Contoh: 3 tahun / 6 bulan" value="<?= htmlspecialchars($_POST['lama_tinggal'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan Tambahan</label>
                <textarea id="keterangan" name="keterangan" class="form-control" placeholder="Keterangan tambahan (opsional)"><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="dokumen_pendukung">Dokumen Pendukung * <span class="note">(scan KTP + KK + Surat RT/RW jika ada)</span></label>
                <input type="file" id="dokumen_pendukung" name="dokumen_pendukung" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="note">Format: PDF, JPG, PNG (maks. 2MB).</div>
            </div>

            <div class="form-actions">
                <a href="dashboard_user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</body>
</html>
