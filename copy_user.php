<?php
// =============================================
// KONEKSI DATABASE & KONFIGURASI
// =============================================

// Load konfigurasi database
require_once 'config.php';


// =============================================
// INISIALISASI & AUTENTIKASI
// =============================================

session_start();
include 'config.php'; // pastikan file ini berisi koneksi $conn

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pastikan hanya role masyarakat yang bisa akses
if ($_SESSION['role'] !== 'masyarakat') {
    header("Location: unauthorized.php");
    exit();
}

// =============================================
// AMBIL DATA USER DARI DATABASE
// =============================================

$user_id = intval($_SESSION['user_id']); // konversi ke int untuk keamanan
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");

if (!$query || mysqli_num_rows($query) === 0) {
    // Jika user tidak ditemukan
    die("User tidak ditemukan.");
}

$user = mysqli_fetch_assoc($query);

// Ambil data yang dibutuhkan
$namaLengkap = $user['nama_lengkap'];
$username = $user['username'];
$inisial = strtoupper(substr($namaLengkap, 0, 1)); // Ambil huruf pertama nama

// =============================================
// FUNGSI HELPER
// =============================================

/**
 * Mengembalikan class badge berdasarkan status
 */
function getStatusBadge($status)
{
    $statusClasses = [
        'Selesai'  => 'badge-success',
        'Proses'   => 'badge-primary',
        'Menunggu' => 'badge-warning',
        'Ditolak'  => 'badge-danger'
    ];

    return $statusClasses[$status] ?? 'badge-secondary';
}

/**
 * Format tanggal ke format Indonesia (YYYY-MM-DD → DD NamaBulan YYYY)
 */
function formatTanggal($tanggal)
{
    $bulan = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    $pecah = explode('-', $tanggal);
    if (count($pecah) === 3) {
        return $pecah[2] . ' ' . ($bulan[$pecah[1]] ?? $pecah[1]) . ' ' . $pecah[0];
    }

    return $tanggal; // fallback jika format tidak sesuai
}

/**
 * Sanitasi input untuk mencegah XSS
 */
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validasi format NIK (16 digit angka)
 */
function validateNik($nik)
{
    return preg_match('/^[0-9]{16}$/', $nik);
}

// =============================================
// CEK & INISIALISASI TABEL DATABASE (Jika Perlu)
// =============================================
// Bisa ditambahkan di bawah sini jika ada pengecekan tabel
function checkAndCreateTables($conn)
{
    // Cek apakah tabel pengajuan_surat ada
    $check_table = "SHOW TABLES LIKE 'pengajuan_surat'";
    $result = mysqli_query($conn, $check_table);

    if (mysqli_num_rows($result) == 0) {
        // Buat tabel pengajuan_surat jika belum ada
        $create_table = "CREATE TABLE pengajuan_surat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            jenis_surat_id INT NOT NULL,
            nama_pemohon VARCHAR(255) NOT NULL,
            nik VARCHAR(20) NOT NULL,
            alamat TEXT NOT NULL,
            tanggal_pengajuan DATE NOT NULL,
            status ENUM('Menunggu', 'Proses', 'Selesai', 'Ditolak') DEFAULT 'Menunggu',
            keterangan TEXT NULL,
            notified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        if (!mysqli_query($conn, $create_table)) {
            error_log("Error creating pengajuan_surat table: " . mysqli_error($conn));
        }
    }

    // Cek apakah tabel jenis_surat ada
    $check_jenis = "SHOW TABLES LIKE 'jenis_surat'";
    $result_jenis = mysqli_query($conn, $check_jenis);

    if (mysqli_num_rows($result_jenis) == 0) {
        // Buat tabel jenis_surat jika belum ada
        $create_jenis = "CREATE TABLE jenis_surat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_surat VARCHAR(255) NOT NULL,
            deskripsi TEXT NULL,
            persyaratan TEXT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (mysqli_query($conn, $create_jenis)) {
            // Insert data default jenis surat
            $insert_jenis = "INSERT INTO jenis_surat (nama_surat, deskripsi) VALUES
                ('Surat Keterangan Domisili', 'Surat keterangan tempat tinggal'),
                ('Surat Keterangan Usaha', 'Surat keterangan untuk usaha'),
                ('Surat Keterangan Tidak Mampu', 'Surat keterangan ekonomi tidak mampu'),
                ('Surat Pengantar KTP', 'Surat pengantar untuk membuat KTP'),
                ('Surat Pengantar KK', 'Surat pengantar untuk membuat Kartu Keluarga')";
            mysqli_query($conn, $insert_jenis);
        }
    }
}

// Panggil fungsi inisialisasi tabel
checkAndCreateTables($conn);

// =============================================
// PROSES DATA UNTUK DASHBOARD
// =============================================

// Ambil data profil user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = ? AND role = 'masyarakat'";
$stmt = mysqli_prepare($conn, $query_user);

if (!$stmt) {
    die("Error preparing user query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_user = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result_user);

if (!$user_data) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Format data profil user
$user_profile = [
    'nama_lengkap' => $user_data['nama_lengkap'] ?? 'User',
    'username' => $user_data['username'] ?? 'user123',
    'nik' => $user_data['nik'] ?? '-',
    'alamat' => $user_data['alamat'] ?? '-',
    'no_telepon' => $user_data['no_telepon'] ?? '-',
    'email' => $user_data['email'] ?? '-'
];

// Ambil statistik surat
$stats_surat = [
    'menunggu' => 0,
    'proses' => 0,
    'selesai' => 0,
    'ditolak' => 0,
    'total' => 0
];

$query_stats = "SELECT 
    COUNT(CASE WHEN status = 'Menunggu' THEN 1 END) as menunggu,
    COUNT(CASE WHEN status = 'Proses' THEN 1 END) as proses,
    COUNT(CASE WHEN status = 'Selesai' THEN 1 END) as selesai,
    COUNT(CASE WHEN status = 'Ditolak' THEN 1 END) as ditolak,
    COUNT(*) as total
    FROM pengajuan_surat WHERE user_id = ?";

$stmt_stats = mysqli_prepare($conn, $query_stats);
if ($stmt_stats) {
    mysqli_stmt_bind_param($stmt_stats, "i", $user_id);
    mysqli_stmt_execute($stmt_stats);
    $result_stats = mysqli_stmt_get_result($stmt_stats);
    $stats_data = mysqli_fetch_assoc($result_stats);

    if ($stats_data) {
        $stats_surat = [
            'menunggu' => $stats_data['menunggu'] ?? 0,
            'proses' => $stats_data['proses'] ?? 0,
            'selesai' => $stats_data['selesai'] ?? 0,
            'ditolak' => $stats_data['ditolak'] ?? 0,
            'total' => $stats_data['total'] ?? 0
        ];
    }
    mysqli_stmt_close($stmt_stats);
}

// Ambil pengajuan surat terakhir
$last_requests = [];
$query_recent = "SELECT ps.*, js.nama_surat 
    FROM pengajuan_surat ps 
    LEFT JOIN jenis_surat js ON ps.jenis_surat_id = js.id 
    WHERE ps.user_id = ? 
    ORDER BY ps.tanggal_pengajuan DESC 
    LIMIT 5";

$stmt_recent = mysqli_prepare($conn, $query_recent);
if ($stmt_recent) {
    mysqli_stmt_bind_param($stmt_recent, "i", $user_id);
    mysqli_stmt_execute($stmt_recent);
    $result_recent = mysqli_stmt_get_result($stmt_recent);

    while ($row = mysqli_fetch_assoc($result_recent)) {
        $last_requests[] = [
            'id' => $row['id'],
            'jenis' => $row['nama_surat'] ?? 'Jenis Surat Tidak Diketahui',
            'tanggal' => $row['tanggal_pengajuan'],
            'status' => $row['status'],
            'keterangan' => $row['keterangan'] ?? ''
        ];
    }
    mysqli_stmt_close($stmt_recent);
}

// =============================================
// HANDLE AJAX REQUESTS
// =============================================

if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'get_notifications':
            $query_notif = "SELECT COUNT(*) as count FROM pengajuan_surat 
                         WHERE user_id = ? AND status IN ('Selesai', 'Ditolak') 
                         AND notified = 0";
            $stmt_notif = mysqli_prepare($conn, $query_notif);
            $count = 0;

            if ($stmt_notif) {
                mysqli_stmt_bind_param($stmt_notif, "i", $user_id);
                mysqli_stmt_execute($stmt_notif);
                $result_notif = mysqli_stmt_get_result($stmt_notif);
                $notif_data = mysqli_fetch_assoc($result_notif);
                $count = $notif_data['count'] ?? 0;
                mysqli_stmt_close($stmt_notif);
            }

            echo json_encode(['count' => $count]);
            break;

        case 'mark_notified':
            $query_update = "UPDATE pengajuan_surat SET notified = 1 
                           WHERE user_id = ? AND status IN ('Selesai', 'Ditolak')";
            $stmt_update = mysqli_prepare($conn, $query_update);
            $success = false;

            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "i", $user_id);
                $success = mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            }

            echo json_encode(['success' => $success]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action not found']);
    }

    exit();
}

// =============================================
// TAMPILAN HTML
// =============================================

// Tutup koneksi database setelah semua query selesai
mysqli_close($conn);

// Lanjutkan dengan menampilkan HTML
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Masyarakat - Kecamatan Simpang Kiri</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* RESET & GLOBAL STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* General Styling */
        .content-section {
            margin-bottom: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .page-title {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            color: #7f8c8d;
            margin-top: 0;
            font-size: 1rem;
        }

        /* Card Grid Layout */
        .surat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .surat-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border-top: 4px solid #3498db;
        }

        .surat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .btn-ajukan {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            margin-top: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-ajukan:hover {
            background: #2980b9;
        }

        /* Info Section */
        .surat-info-card {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .info-card-icon {
            font-size: 2rem;
            color: #3498db;
            margin-right: 1.5rem;
        }

        /* Panduan Section */
        .panduan-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .panduan-list {
            padding-left: 1.5rem;
        }

        .panduan-list li {
            margin-bottom: 0.5rem;
            color: #555;
        }

        /* Info Card */
        .surat-info-card {
            display: flex;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #3498db;
        }

        .info-card-icon {
            font-size: 2rem;
            color: #3498db;
            margin-right: 1.5rem;
        }

        .info-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.5rem;
            border-radius: 5px;
            font-size: 0.85rem;
            margin-top: 1rem;
            display: inline-block;
        }

        /* Card Grid */
        .surat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .surat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .surat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
            margin: 1rem 0;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .card-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Button */
        .btn-ajukan {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .btn-ajukan:hover {
            background: #2980b9;
        }

        /* Panduan */
        .panduan-pengajuan {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        .panduan-pengajuan ol {
            padding-left: 1.5rem;
            line-height: 1.8;
        }

        .catatan-penting {
            background: #fff8e1;
            color: #ff6d00;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
            border-left: 3px solid #ffc107;
        }

        /* LAYOUT */
        .user-container {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 1rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 100;
        }

        .sidebar-header {
            text-align: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
        }

        .sidebar-header h3 {
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
            color: #3498db;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            color: #bdc3c7;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: white;
            border-radius: 10px;
            transition: all 0.3s;
            text-decoration: none;
            cursor: pointer;
            position: relative;
        }

        .sidebar-nav a i {
            margin-right: 1rem;
            width: 20px;
            font-size: 1.1rem;
        }

        .sidebar-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar-nav .active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transform: translateX(5px);
        }

        .sidebar-nav .active::after {
            content: '';
            position: absolute;
            right: -1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
            border-left: 10px solid #3498db;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .content-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CARD STYLES */
        .card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .card-header h3 {
            color: #2c3e50;
            display: flex;
            align-items: center;
            font-size: 1.4rem;
        }

        .card-header h3 i {
            margin-right: 0.8rem;
            color: #3498db;
            font-size: 1.2rem;
        }

        /* FORM STYLES */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
            background: white;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color), transparent);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h4 {
            margin-bottom: 1rem;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .stat-card h2 {
            font-size: 2.8rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .stat-card.warning {
            --color: #f39c12;
            border: 2px solid #f39c12;
        }

        .stat-card.warning h2 {
            color: #f39c12;
        }

        .stat-card.primary {
            --color: #3498db;
            border: 2px solid #3498db;
        }

        .stat-card.primary h2 {
            color: #3498db;
        }

        .stat-card.success {
            --color: #27ae60;
            border: 2px solid #27ae60;
        }

        .stat-card.success h2 {
            color: #27ae60;
        }

        .stat-card.danger {
            --color: #e74c3c;
            border: 2px solid #e74c3c;
        }

        .stat-card.danger h2 {
            color: #e74c3c;
        }

        /* TABLE STYLES */
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            background: white;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-weight: bold;
            color: #2c3e50;
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .badge-success {
            background: linear-gradient(135deg, #27ae60, #219a52);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .badge-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #219a52);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #219a52, #1e8449);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        /* USER INFO */
        .user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            margin-right: 15px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        /* PROFILE INFO */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .profile-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }

        .profile-item label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .profile-item p {
            font-size: 1.1rem;
            color: #555;
            margin: 0;
        }

        /* MOBILE MENU */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e50;
            cursor: pointer;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
            color: #bdc3c7;
        }

        .empty-state h4 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        /* LETTER REQUEST FORM */
        .letter-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .letter-type {
            padding: 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .letter-type:hover,
        .letter-type.selected {
            border-color: #3498db;
            background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
            transform: translateY(-2px);
        }

        .letter-type i {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                width: 280px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .mobile-menu-btn {
                display: block;
            }

            .content-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        /* LOADING */
        .loading {
            display: none;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="user-container">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-building"></i> Kecamatan Simpang Kiri</h3>
                <p>Pelayanan Digital Masyarakat</p>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" onclick="showSection('dashboard')" class="nav-link active" data-section="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a></li>
                    <li><a href="#" onclick="showSection('ajukan-surat')" class="nav-link" data-section="ajukan-surat">
                            <i class="fas fa-plus-circle"></i> Ajukan Surat
                        </a></li>
                    <li><a href="#" onclick="showSection('status-surat')" class="nav-link" data-section="status-surat">
                            <i class="fas fa-list-alt"></i> Status Surat
                        </a></li>
                    <li><a href="#" onclick="showSection('riwayat-surat')" class="nav-link" data-section="riwayat-surat">
                            <i class="fas fa-history"></i> Riwayat
                        </a></li>
                    <li><a href="#" onclick="showSection('profile')" class="nav-link" data-section="profile">
                            <i class="fas fa-user-edit"></i> Edit Profil
                        </a></li>
                    <li><a href="login.php" onclick="confirmLogout()" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                </ul>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- DASHBOARD SECTION -->
            <div id="dashboard-section" class="content-section active">
                <div class="content-header">
                    <div>
                        <button class="mobile-menu-btn" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2><i class="fas fa-tachometer-alt"></i> Dashboard Masyarakat</h2>
                        <p style="color: #7f8c8d; margin-top: 0.5rem;">
                            Selamat datang di sistem pelayanan digital Kecamatan Simpang Kiri
                        </p>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar" title="<?= htmlspecialchars($namaLengkap) ?>">
                            <?= htmlspecialchars($inisial) ?>
                        </div>
                        <div class="user-details">
                            <strong><?= htmlspecialchars($namaLengkap) ?></strong>
                            <div class="user-username">@<?= htmlspecialchars($username) ?></div>
                        </div>
                    </div>

                </div>
            </div>
            
            <section id="status-surat-section" class="content-section">
                di sini kamu akan liat status surat kamu
            </section>

            <section id="riwayat-surat-section" class="content-section">
                adalah adalah riwayat pengajuan surat
            </section>
            <section id="ajukan-surat-section" class="content-section" aria-labelledby="ajukan-surat-heading">
                <header class="content-header">
                    <div class="header-content-wrapper">
                        <button class="mobile-menu-btn" onclick="togglesidebar()" aria-label="Toggle navigation menu">
                            <i class="fas fa-bars" aria-hidden="true"></i>
                        </button>

                        <div class="header-text">
                            <h1 id="ajukan-surat-heading" class="page-title">
                                <i class="fas fa-user-edit" aria-hidden="true"></i>
                                Ajukan Surat
                            </h1>
                            <p class="header-subtitle">
                                Layanan pengajuan surat resmi Kecamatan Simpang Kiri
                            </p>
                        </div>
                    </div>
                </header>

                <div class="content-body">
                    <div class="surat-info-card">
                        <div class="info-card-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="info-card-content">
                            <h3>Layanan Surat Digital</h3>
                            <p>Ajukan berbagai surat keterangan dan rekomendasi secara online tanpa perlu antri panjang. Proses cepat dengan verifikasi data terintegrasi.</p>
                            <div class="info-badge">
                                <i class="fas fa-clock"></i> Rata-rata proses: 1-3 hari kerja
                            </div>
                        </div>
                    </div>

                    <div class="surat-grid">
                        <!-- Surat Pengantar -->
                        <div class="surat-card" onclick="openSuratForm('pengantar')">
                            <div class="card-icon" style="color: #3498db;">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <h3>Surat Pengantar</h3>
                            <p>Untuk keperluan administrasi di instansi lain</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> Selesai hari ini</span>
                                <span><i class="fas fa-file-alt"></i> Butuh KK + KTP</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>

                        <!-- Surat Keterangan Domisili -->
                        <div class="surat-card" onclick="openSuratForm('domisili')">
                            <div class="card-icon" style="color: #2ecc71;">
                                <i class="fas fa-house-user"></i>
                            </div>
                            <h3>Surat Domisili</h3>
                            <p>Bukti alamat tinggal resmi</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> 1 hari kerja</span>
                                <span><i class="fas fa-file-alt"></i> + Surat RT/RW</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>

                        <!-- Surat Keterangan Usaha -->
                        <div class="surat-card" onclick="openSuratForm('usaha')">
                            <div class="card-icon" style="color: #e74c3c;">
                                <i class="fas fa-store"></i>
                            </div>
                            <h3>Surat Keterangan Usaha</h3>
                            <p>Legalitas usaha mikro/kecil</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> 2 hari kerja</span>
                                <span><i class="fas fa-file-alt"></i> + Foto usaha</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>

                        <!-- Surat Keterangan Tidak Mampu -->
                        <div class="surat-card" onclick="openSuratForm('tidak-mampu')">
                            <div class="card-icon" style="color: #f39c12;">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <h3>Surat Keterangan Tidak Mampu</h3>
                            <p>Untuk pengajuan bantuan sosial</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> 3 hari kerja</span>
                                <span><i class="fas fa-file-alt"></i> + Survey petugas</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>

                        <!-- Surat Keterangan Meninggal -->
                        <div class="surat-card" onclick="openSuratForm('meninggal')">
                            <div class="card-icon" style="color: #555;">
                               <i class="fas fa-skull-crossbones"></i>

                            </div>
                            <h3>Surat Keterangan Meninggal</h3>
                            <p>Administrasi legal untuk keperluan kematian</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> Proses prioritas</span>
                                <span><i class="fas fa-file-alt"></i> + Akta kematian</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>
                        <!-- Surat Rekomendasi Nikah -->
                        <div class="surat-card" onclick="openSuratForm('rekomendasi-nikah')">
                            <div class="card-icon" style="color: #e84393;">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3>Rekomendasi Nikah</h3>
                            <p>Persyaratan administrasi pernikahan</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> 2 hari kerja</span>
                                <span><i class="fas fa-file-alt"></i> + Akta kelahiran</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>

                        <!-- Surat Rekomendasi Bantuan -->
                        <div class="surat-card" onclick="openSuratForm('rekomendasi-bantuan')">
                            <div class="card-icon" style="color: #00b894;">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h3>Rekomendasi Bantuan</h3>
                            <p>Pengajuan program bantuan sosial</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> 3-5 hari kerja</span>
                                <span><i class="fas fa-file-alt"></i> + Data keluarga</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>

                        <!-- Surat Rekomendasi Kegiatan -->
                        <div class="surat-card" onclick="openSuratForm('rekomendasi-kegiatan')">
                            <div class="card-icon" style="color: #0984e3;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h3>Rekomendasi Kegiatan</h3>
                            <p>Izin penyelenggaraan event</p>
                            <div class="card-meta">
                                <span><i class="fas fa-clock"></i> Min. 7 hari sebelumnya</span>
                                <span><i class="fas fa-file-alt"></i> + Proposal kegiatan</span>
                            </div>
                            <button class="btn-ajukan">Ajukan Sekarang</button>
                        </div>
                    </div>

                    <div class="panduan-pengajuan">
                        <h3><i class="fas fa-info-circle"></i> Panduan Pengajuan</h3>
                        <ol>
                            <li>Pilih jenis surat yang dibutuhkan</li>
                            <li>Siapkan dokumen persyaratan (digital)</li>
                            <li>Isi formulir pengajuan secara lengkap</li>
                            <li>Submit dan tunggu verifikasi petugas</li>
                            <li>Ambil surat di kantor atau download versi digital</li>
                        </ol>
                        <div class="catatan-penting">
                            <i class="fas fa-exclamation-triangle"></i> Pastikan data yang diisi valid. Pengajuan palsu akan dikenai sanksi sesuai peraturan daerah.
                        </div>
                    </div>
                </div>
                <div class="panduan-section">
                    <h2><i class="fas fa-info-circle"></i> Cara Mengajukan Surat</h2>
                    <ol class="panduan-list">
                        <li>Pilih jenis surat yang ingin diajukan</li>
                        <li>Isi formulir pengajuan dengan data yang valid</li>
                        <li>Upload dokumen pendukung yang diperlukan</li>
                        <li>Submit pengajuan dan tunggu verifikasi</li>
                        <li>Cetak surat setelah status disetujui</li>
                    </ol>
                </div>
        </div>
        </section>
        <!-- PROFILE SECTION -->
        <div id="profile-section" class="content-section">
    <div class="content-header">
        <div class="header-content-wrapper">
            <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-text">
                <h2><i class="fas fa-user-edit"></i> Edit Profil</h2>
                <p class="header-subtitle">Kelola informasi pribadi Anda</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Informasi Pribadi</h3>
        </div>
        <form id="profileForm" action="update_profile.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="profile-grid">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" 
                           value="<?php echo htmlspecialchars($user['nama_lengkap'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Username</label>
                    <input type="text" class="form-control" name="username" 
                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> NIK</label>
                    <input type="text" class="form-control" name="nik" 
                           value="<?php echo htmlspecialchars($user['nik'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                    <textarea class="form-control" name="alamat" rows="3" required><?php 
                        echo htmlspecialchars($user['alamat'] ?? ''); 
                    ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <button type="button" class="btn btn-danger" onclick="showChangePassword()">
                    <i class="fas fa-key"></i> Ubah Password
                </button>
            </div>
        </form>
    </div>

    <!-- CHANGE PASSWORD CARD -->
    <div class="card" id="changePasswordCard" style="display: none;">
        <div class="card-header">
            <h3><i class="fas fa-key"></i> Ubah Password</h3>
        </div>
        <form id="passwordForm" action="update_password.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password Lama</label>
                <input type="password" class="form-control" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password Baru</label>
                <input type="password" class="form-control" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Konfirmasi Password Baru</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Ubah Password
                </button>
                <button type="button" class="btn btn-secondary" onclick="hideChangePassword()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showChangePassword() {
    document.getElementById('changePasswordCard').style.display = 'block';
}

function hideChangePassword() {
    document.getElementById('changePasswordCard').style.display = 'none';
}

// Form submission handling
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // AJAX submission would go here
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // AJAX password change would go here
});
</script>

<style>
.content-section {
    padding: 20px;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.2rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.card-header {
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #eee;
}

.btn {
    padding: 0.6rem 1.2rem;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    font-weight: 500;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}
</style>
    </div>
    </div>

    <!-- LOADING OVERLAY -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="loading" style="display: block;"></div>
    </div>

    <!-- DETAIL MODAL -->
    <div id="detailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 15px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 1rem;">
                <h3><i class="fas fa-file-alt"></i> Detail Pengajuan Surat</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #e74c3c;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // GLOBAL VARIABLES
        let currentSection = 'dashboard';
        let selectedLetterType = null;

        // SECTION MANAGEMENT
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionName + '-section').classList.add('active');

            // Update navigation
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector(`[data-section="${sectionName}"]`).classList.add('active');

            currentSection = sectionName;

            // Close mobile sidebar
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('active');
            }
        }

        // SIDEBAR TOGGLE
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // CLOSE SIDEBAR WHEN CLICKING OUTSIDE (MOBILE)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // LETTER TYPE SELECTION
        function selectLetterType(element, type) {
            // Remove previous selection
            document.querySelectorAll('.letter-type').forEach(el => {
                el.classList.remove('selected');
            });

            // Add selection to clicked element
            element.classList.add('selected');
            selectedLetterType = type;

            // Show form
            document.getElementById('letterForm').style.display = 'block';

            // Scroll to form
            document.getElementById('letterForm').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // FORM SUBMISSIONS
        document.getElementById('letterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedLetterType) {
                showToast('Silakan pilih jenis surat terlebih dahulu', 'warning');
                return;
            }

            showLoading();

            // Simulate API call
            setTimeout(() => {
                hideLoading();
                showToast('Pengajuan surat berhasil dikirim!', 'success');

                // Reset form
                this.reset();
                document.querySelectorAll('.letter-type').forEach(el => {
                    el.classList.remove('selected');
                });
                this.style.display = 'none';
                selectedLetterType = null;

                // Switch to status section
                showSection('status-surat');
            }, 2000);
        });

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();

            showLoading();

            setTimeout(() => {
                hideLoading();
                showToast('Profil berhasil diperbarui!', 'success');
            }, 1500);
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const newPassword = e.target.querySelector('input[type="password"]:nth-of-type(2)').value;
            const confirmPassword = e.target.querySelector('input[type="password"]:nth-of-type(3)').value;

            if (newPassword !== confirmPassword) {
                showToast('Konfirmasi password tidak cocok!', 'error');
                return;
            }

            showLoading();

            setTimeout(() => {
                hideLoading();
                showToast('Password berhasil diubah!', 'success');
                this.reset();
                hideChangePassword();
            }, 1500);
        });

        // MODAL FUNCTIONS
        function showDetail(id) {
            const details = {
                1: {
                    jenis: 'Surat Keterangan Domisili',
                    nama: 'Ahmad Budi',
                    nik: '1234567890123456',
                    keperluan: 'Untuk keperluan beasiswa',
                    tanggal: '15 Januari 2025',
                    status: 'Menunggu',
                    keterangan: 'Surat sedang dalam antrian verifikasi.'
                },
                2: {
                    jenis: 'Surat Keterangan Usaha',
                    nama: 'Ahmad Budi',
                    nik: '1234567890123456',
                    keperluan: 'Untuk KUR Bank',
                    tanggal: '12 Januari 2025',
                    status: 'Proses',
                    keterangan: 'Sedang diverifikasi oleh petugas desa.'
                },
                3: {
                    jenis: 'Surat Pengantar KTP',
                    nama: 'Ahmad Budi',
                    nik: '1234567890123456',
                    keperluan: 'KTP Hilang',
                    tanggal: '10 Januari 2025',
                    status: 'Selesai',
                    keterangan: 'Surat telah selesai dan dapat diambil.'
                }
            };

            const detail = details[id];
            if (!detail) return;

            const statusClass = {
                'Menunggu': 'badge-warning',
                'Proses': 'badge-primary',
                'Selesai': 'badge-success',
                'Ditolak': 'badge-danger'
            };

            document.getElementById('modalContent').innerHTML = `
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">${detail.jenis}</h4>
                    <div style="display: grid; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; padding: 0.8rem; background: #f8f9fa; border-radius: 8px;">
                            <strong>Nama Pemohon:</strong>
                            <span>${detail.nama}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.8rem; background: #f8f9fa; border-radius: 8px;">
                            <strong>NIK:</strong>
                            <span>${detail.nik}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.8rem; background: #f8f9fa; border-radius: 8px;">
                            <strong>Keperluan:</strong>
                            <span>${detail.keperluan}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.8rem; background: #f8f9fa; border-radius: 8px;">
                            <strong>Tanggal Pengajuan:</strong>
                            <span>${detail.tanggal}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.8rem; background: #f8f9fa; border-radius: 8px;">
                            <strong>Status:</strong>
                            <span class="badge ${statusClass[detail.status]}">${detail.status}</span>
                        </div>
                        <div style="padding: 0.8rem; background: #f8f9fa; border-radius: 8px;">
                            <strong>Keterangan:</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #555;">${detail.keterangan}</p>
                        </div>
                    </div>
                </div>
                ${detail.status === 'Selesai' ? `
                <div style="text-align: center; border-top: 1px solid #eee; padding-top: 1rem;">
                    <button class="btn btn-success" onclick="downloadLetter(${id})">
                        <i class="fas fa-download"></i> Download Surat
                    </button>
                </div>
                ` : ''}
            `;

            document.getElementById('detailModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        function downloadLetter(id) {
            showToast('Download dimulai...', 'success');
            closeModal();
        }

        // PASSWORD CHANGE FUNCTIONS
        function showChangePassword() {
            document.getElementById('changePasswordCard').style.display = 'block';
            document.getElementById('changePasswordCard').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function hideChangePassword() {
            document.getElementById('changePasswordCard').style.display = 'none';
            document.getElementById('passwordForm').reset();
        }

        // LOADING FUNCTIONS
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        // TOAST NOTIFICATION SYSTEM
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                color: white;
                font-weight: bold;
                z-index: 10001;
                animation: slideIn 0.3s ease;
                max-width: 350px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            `;

            switch (type) {
                case 'success':
                    toast.style.background = 'linear-gradient(135deg, #27ae60, #219a52)';
                    break;
                case 'error':
                    toast.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                    break;
                case 'warning':
                    toast.style.background = 'linear-gradient(135deg, #f39c12, #e67e22)';
                    break;
                default:
                    toast.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
            }

            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }

        // LOGOUT CONFIRMATION
        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                showLoading();
                setTimeout(() => {
                    hideLoading();
                    showToast('Logout berhasil. Sampai jumpa!', 'success');
                    // In real app, redirect to login page
                    setTimeout(() => {
                        alert('Redirect ke halaman login...');
                    }, 1000);
                }, 1000);
            }
        }

        // ADD SLIDE ANIMATIONS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // KEYBOARD SHORTCUTS
        document.addEventListener('keydown', function(e) {
            // Ctrl + 1-5 for quick navigation
            if (e.ctrlKey) {
                switch (e.key) {
                    case '1':
                        e.preventDefault();
                        showSection('dashboard');
                        break;
                    case '2':
                        e.preventDefault();
                        showSection('ajukan-surat');
                        break;
                    case '3':
                        e.preventDefault();
                        showSection('status-surat');
                        break;
                    case '4':
                        e.preventDefault();
                        showSection('riwayat-surat');
                        break;
                    case '5':
                        e.preventDefault();
                        showSection('profile');
                        break;
                }
            }

            // Escape to close modal/sidebar
            if (e.key === 'Escape') {
                closeModal();
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar').classList.remove('active');
                }
            }
        });

        // RESPONSIVE HANDLING
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('active');
            }
        });

        // INITIALIZE
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Selamat datang di Dashboard Masyarakat!', 'success');

            // Add loading states to all buttons
            document.querySelectorAll('.btn').forEach(btn => {
                if (!btn.onclick && btn.type !== 'submit') {
                    btn.addEventListener('click', function() {
                        showLoading();
                        setTimeout(hideLoading, 1000);
                    });
                }
            });
        });

        // AUTO REFRESH SIMULATION (in real app, this would fetch from server)
        setInterval(function() {
            // Simulate checking for updates
            if (Math.random() < 0.1) { // 10% chance
                showToast('Ada update status surat baru!', 'info');
            }
        }, 30000); // Every 30 seconds
    </script>
</body>




<!-- RIWAYAT SURAT SECTION -->

<script>
    // MOBILE SIDEBAR TOGGLE
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    // CLOSE SIDEBAR WHEN CLICKING OUTSIDE (MOBILE)
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.querySelector('.mobile-menu-btn');

        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // AUTO-REFRESH NOTIFICATIONS
    function checkNotifications() {
        fetch('dashboard.php?action=get_notifications')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // MARK NOTIFICATIONS AS READ WHEN USER CLICKS
    document.querySelector('.user-info').addEventListener('click', function() {
        fetch('dashboard.php?action=mark_notified', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('notificationBadge').style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // LOADING OVERLAY FUNCTIONS
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // AUTO-REFRESH DATA EVERY 30 SECONDS
    setInterval(function() {
        checkNotifications();
    }, 30000);

    // SMOOTH SCROLL FOR ANCHOR LINKS
</script>
</body>

</html>