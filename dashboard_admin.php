<?php
// ========================
// KONEKSI DATABASE & AUTH
// ========================
session_start();

// Koneksi database
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$database = 'kecamatan_simpang_kiri';

$conn = mysqli_connect($host, $username_db, $password_db, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fungsi helper
function getRoleName($role)
{
    $roles = [
        'admin' => 'Administrator',
        'staff' => 'Staff Kecamatan',
        'masyarakat' => 'Masyarakat'
    ];
    return $roles[$role] ?? $role;
}

function getStatusBadgeClass($status)
{
    switch (strtolower($status)) {
        case 'selesai':
        case 'disetujui':
        case 'diterima':
            return 'status-approved';
        case 'proses':
        case 'dalam review':
        case 'diproses':
            return 'status-pending';
        case 'menunggu':
        case 'pending':
            return 'status-pending';
        case 'ditolak':
            return 'status-rejected';
        default:
            return 'status-pending';
    }
}

// ========================
// PROSES AKSI AJAX
// ========================
if (isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'update_status':
            $request_id = intval($_POST['request_id']);
            $new_status = mysqli_real_escape_string($conn, $_POST['status']);

            $query = "UPDATE pengajuan_surat SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $new_status, $request_id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status']);
            }
            exit;

        case 'delete_request':
            $request_id = intval($_POST['request_id']);

            $query = "DELETE FROM pengajuan_surat WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $request_id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Permohonan berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus permohonan']);
            }
            exit;
    }
}

// ========================
// QUERY DATA STATISTIK
// ========================
// Query statistik pengguna
$stats_query = "
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admin,
        SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) as total_staff,
        SUM(CASE WHEN role = 'masyarakat' THEN 1 ELSE 0 END) as total_masyarakat
    FROM users
";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Query statistik permohonan
$request_stats_query = "
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'diterima' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM pengajuan_surat
";
$request_stats_result = mysqli_query($conn, $request_stats_query);
$request_stats = mysqli_fetch_assoc($request_stats_result);

// Fallback data jika tabel kosong
if (empty($stats)) {
    $stats = [
        'total_users' => 0,
        'total_admin' => 0,
        'total_staff' => 0,
        'total_masyarakat' => 0
    ];
}

if (empty($request_stats)) {
    $request_stats = [
        'total_requests' => 0,
        'completed' => 0,
        'in_progress' => 0,
        'pending' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kecamatan Simpang Kiri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c5aa0;
            --secondary-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #343a40;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR STYLES */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary-color), #1e3a5f);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-right: 3px solid #ffd700;
        }

        .sidebar-nav a i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        /* MAIN CONTENT STYLES */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background-color: #f5f6fa;
        }

        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
        }

        .user-info strong {
            color: var(--dark-color);
            font-size: 1rem;
        }

        .badge {
            padding: 0.4em 0.8em;
            font-size: 0.75rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .badge-primary {
            background-color: var(--primary-color);
            color: white;
        }

        /* STATISTICS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h4 {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        /* CARD STYLES */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 2rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 2rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 2rem;
        }

        /* BUTTON STYLES */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e3a5f;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        /* TABLE STYLES */
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .custom-table thead {
            background: linear-gradient(135deg, var(--primary-color), #1e3a5f);
            color: white;
        }

        .custom-table th,
        .custom-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .custom-table th {
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* STATUS BADGES */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* FILTER STYLES */
        .filter-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            min-width: 150px;
        }

        /* ALERT STYLES */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: none;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h4 {
            margin-bottom: 0.5rem;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        /* SECTION VISIBILITY */
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        /* HAMBURGER BUTTON STYLES */
        .hamburger-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 992px) {
            .hamburger-btn {
                display: flex;
            }

            .sidebar {
                width: 280px;
                height: 100vh;
                position: fixed;
                left: -280px;
                transition: left 0.3s ease;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                margin-top: 2rem;
            }

            /* Overlay ketika sidebar aktif */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1rem;
            }

            .filter-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .custom-table {
                font-size: 0.85rem;
            }

            .custom-table th,
            .custom-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <div class="sidebar">

            <button class="hamburger-btn d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            <div class="sidebar-header">
                <h3><i class="fas fa-building"></i> Kecamatan Simpang Kiri</h3>
                <p>Admin Panel</p>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" class="active" onclick="showSection('dashboard')">
                            <i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#" onclick="showSection('requests')">
                            <i class="fas fa-file-alt"></i> Kelola Surat</a></li>
                    <li><a href="#" onclick="showSection('reports')">
                            <i class="fas fa-chart-bar"></i> Laporan</a></li>
                    <li><a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="admin-header">
                <h2 id="page-title"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h2>
                <div class="user-info">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%232c5aa0'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-size='16' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" alt="User Avatar">
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Administrator'); ?></strong>
                        <div class="badge badge-primary"><?php echo getRoleName($_SESSION['role'] ?? 'admin'); ?></div>
                    </div>
                </div>
            </div>

            <!-- ALERT CONTAINER -->
            <div id="alert-container"></div>

            <!-- DASHBOARD SECTION -->
            <div id="dashboard-section" class="section active">
                <div class="stats-grid">
                    <div class="stat-card" style="border-left-color: #3498db;">
                        <h4>Total Pengguna</h4>
                        <h2 style="color: #3498db;"><?php echo number_format($stats['total_users']); ?></h2>
                        <p><i class="fas fa-users me-2"></i>Pengguna terdaftar</p>
                    </div>
                    <div class="stat-card" style="border-left-color: #28a745;">
                        <h4>Administrator</h4>
                        <h2 style="color: #28a745;"><?php echo number_format($stats['total_admin']); ?></h2>
                        <p><i class="fas fa-user-shield me-2"></i>Admin sistem</p>
                    </div>
                    <div class="stat-card" style="border-left-color: #ffc107;">
                        <h4>Masyarakat</h4>
                        <h2 style="color: #ffc107;"><?php echo number_format($stats['total_masyarakat']); ?></h2>
                        <p><i class="fas fa-user-friends me-2"></i>Pengguna masyarakat</p>
                    </div>
                    <div class="stat-card" style="border-left-color: #17a2b8;">
                        <h4>Total Permohonan</h4>
                        <h2 style="color: #17a2b8;"><?php echo number_format($request_stats['total_requests']); ?></h2>
                        <p><i class="fas fa-file-alt me-2"></i>Semua permohonan</p>
                    </div>
                </div>

                <!-- STATISTIK PERMOHONAN -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie me-2"></i>Statistik Permohonan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <h4 class="text-success"><?php echo $request_stats['completed']; ?></h4>
                                <p>Disetujui</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-primary"><?php echo $request_stats['in_progress']; ?></h4>
                                <p>Diproses</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-warning"><?php echo $request_stats['pending']; ?></h4>
                                <p>Menunggu</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-info"><?php echo $request_stats['total_requests']; ?></h4>
                                <p>Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KELOLA SURAT SECTION -->
          <div id="requests-section" class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><i class="fas fa-file-alt me-2"></i>Kelola Permohonan Surat</h3>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
        <div class="card-body">
            <?php
            // Filter status
            $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
            $status_condition = "";

            if ($status_filter != 'all') {
                $status_condition = "WHERE p.status = '" . $conn->real_escape_string($status_filter) . "'";
            }

        // Query data pengajuan
$query = "SELECT p.*, u.nama_lengkap, u.no_hp 
          FROM pengajuan_surat p 
          JOIN users u ON p.user_id = u.id 
          $status_condition
          ORDER BY p.tanggal_pengajuan DESC";
$result = $conn->query($query);

            // Filter status dropdown
            echo "<div class='filter-container mb-3'>
                <label for='status-filter' class='form-label'>Filter Status:</label>
                <select id='status-filter' class='filter-select' onchange='filterStatus(this.value)'>
                    <option value='all'" . ($status_filter == 'all' ? ' selected' : '') . ">Semua Status</option>
                    <option value='diproses'" . ($status_filter == 'diproses' ? ' selected' : '') . ">Diproses</option>
                    <option value='diterima'" . ($status_filter == 'diterima' ? ' selected' : '') . ">Diterima</option>
                    <option value='ditolak'" . ($status_filter == 'ditolak' ? ' selected' : '') . ">Ditolak</option>
                </select>
            </div>";

            if ($result && $result->num_rows > 0) {
                echo "<div class='table-container'>
                        <table class='custom-table'>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pemohon</th>
                                    <th>Jenis Surat</th>
                                    <th>Keperluan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Surat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>";

                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    // Tentukan nama surat berdasarkan jenis
                    $nama_surat = '';
                    switch ($row['jenis_surat']) {
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
                            $nama_surat = ucfirst($row['jenis_surat']);
                    }

                    echo "<tr id='request-row-{$row['id']}'>
                        <td>{$no}</td>
                        <td>" . htmlspecialchars($row['nama_lengkap']) . "</td>
                        <td>" . htmlspecialchars($nama_surat) . "</td>
                        <td>" . htmlspecialchars($row['keperluan']) . "</td>
                        <td>
                            <select onchange=\"updateStatus({$row['id']}, this.value)\" class=\"form-select form-select-sm\">
                                <option value=\"diproses\" " . ($row['status'] == 'diproses' ? 'selected' : '') . ">Diproses</option>
                                <option value=\"diterima\" " . ($row['status'] == 'diterima' ? 'selected' : '') . ">Diterima</option>
                                <option value=\"ditolak\" " . ($row['status'] == 'ditolak' ? 'selected' : '') . ">Ditolak</option>
                            </select>
                        </td>
                        <td>" . date('d/m/Y', strtotime($row['tanggal_pengajuan'])) . "</td>
                        <td>";

                    // Kolom Surat - Tampilkan tombol download jika status diterima
                    if ($row['status'] == 'diterima') {
                        echo "<a href='generate_surat.php?id={$row['id']}' 
                                target='_blank' 
                                class='btn btn-sm btn-success'>
                                <i class='fas fa-download'></i> Download Surat
                              </a>";
                    } elseif (!empty($row['file_surat'])) {
                        echo "<a href='uploads/{$row['file_surat']}' 
                                target='_blank' 
                                class='btn btn-sm btn-info'>
                                <i class='fas fa-download'></i> Unduh File
                              </a>";
                    } else {
                        echo "<span class='text-muted'>
                                <i class='fas fa-clock'></i> Belum tersedia
                              </span>";
                    }

                    echo "</td>
                        <td>
                            <div class='btn-group' role='group'>
                                <button class='btn btn-sm btn-primary' 
                                        onclick='viewDetails({$row['id']})' 
                                        title='Lihat Detail'>
                                    <i class='fas fa-eye'></i>
                                </button>";

                    // Tombol download tambahan untuk admin (jika surat sudah diterima)
                if ($row['status'] == 'diterima') {
    echo '<a href="generate_surat.php?id=' . $row['id'] . '" 
            target="_blank" 
            class="btn btn-sm btn-success" 
            title="Download Surat">
            <i class="fas fa-file-pdf"></i>
          </a>';
}

// Tombol hapus
echo '<button class="btn btn-sm btn-danger" 
        onclick="deleteRequest(' . $row['id'] . ')" 
        title="Hapus">
        <i class="fas fa-trash"></i>
      </button>';

// Kirim WhatsApp kalau ada nomor HP
if (!empty($row['no_hp'])) {
    // Ubah 08xxx -> 628xxx agar sesuai format WhatsApp
    $no_hp = preg_replace('/^0/', '62', $row['no_hp']); 
    
    // Pesan WA
    $pesan = "Halo {$row['nama_lengkap']}, pengajuan surat Anda (ID: {$row['id']}) telah DITERIMA. Surat sudah dapat diunduh atau diambil langsung di kantor desa.";
    
    echo '<a href="https://wa.me/' . $no_hp . '?text=' . urlencode($pesan) . '" 
            target="_blank" 
            class="btn btn-sm btn-success" 
            title="Kirim WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>';
} else {
    echo '<span class="text-muted">No HP tidak tersedia</span>';
}

echo '</div></td></tr>';
$no++;
                }

                echo "</tbody></table></div>";
            } else {
                echo "<div class='empty-state text-center'>
                        <i class='fas fa-inbox fa-2x mb-2'></i>
                        <h4>Belum ada pengajuan surat</h4>
                        <p>Tidak ada permohonan surat yang ditemukan</p>
                      </div>";
            }
            ?>
        </div>
    </div>
</div>

<style>
/* Tambahan CSS untuk styling tombol */
.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.custom-table td {
    vertical-align: middle;
}

.text-muted {
    font-style: italic;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 2px;
    }
}
</style>



            <!-- LAPORAN SECTION -->
            <div id="reports-section" class="section">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar me-2"></i>Laporan Sistem</h3>
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i> Generate Laporan
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Laporan Bulanan</h5>
                                <p>Generate laporan permohonan surat bulanan</p>
                                <button class="btn btn-outline-primary">Download PDF</button>
                            </div>
                            <div class="col-md-6">
                                <h5>Laporan Tahunan</h5>
                                <p>Generate laporan permohonan surat tahunan</p>
                                <button class="btn btn-outline-primary">Download Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Preview Dokumen -->
        <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="documentModalLabel">Preview Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="documentPreview">
                        <p class="text-center">Memuat dokumen...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fungsi untuk menampilkan section
        function showSection(sectionName) {
            // Sembunyikan semua section
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });

            // Tampilkan section yang dipilih
            document.getElementById(sectionName + '-section').classList.add('active');

            // Update title
            const titles = {
                'dashboard': '<i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin',
                'requests': '<i class="fas fa-file-alt me-2"></i>Kelola Surat',
                'reports': '<i class="fas fa-chart-bar me-2"></i>Laporan Sistem'
            };
            document.getElementById('page-title').innerHTML = titles[sectionName];

            // Update active nav
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');

            // Simpan state
            localStorage.setItem('activeSection', sectionName);
        }

        // Fungsi untuk menampilkan alert
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}`;

            alertContainer.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Fungsi untuk update status
        function updateStatus(requestId, status) {
            if (confirm(`Apakah Anda yakin ingin ${status === 'diterima' ? 'menerima' : 'menolak'} permohonan ini?`)) {
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_status&request_id=${requestId}&status=${status}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(data.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Terjadi kesalahan sistem', 'danger');
                    });
            }
        }

        // Fungsi untuk menghapus request
        function deleteRequest(requestId) {
            if (confirm('Apakah Anda yakin ingin menghapus permohonan ini? Tindakan ini tidak dapat dibatalkan.')) {
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_request&request_id=${requestId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(data.message, 'success');
                            document.getElementById('request-row-' + requestId).remove();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Terjadi kesalahan sistem', 'danger');
                    });
            }
        }

        // Fungsi untuk filter status
        function filterStatus(status) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('status', status);
            window.location.href = currentUrl.toString();
        }

        // Fungsi untuk melihat detail

        // Load active section saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const activeSection = localStorage.getItem('activeSection');
            if (activeSection && activeSection !== 'dashboard') {
                showSection(activeSection);
            }
        });
    </script>

    <script>
        // Fungsi untuk toggle sidebar di mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');

            // Simpan state sidebar
            if (sidebar.classList.contains('active')) {
                localStorage.setItem('sidebarState', 'open');
            } else {
                localStorage.setItem('sidebarState', 'closed');
            }
        }

        // Tutup sidebar ketika klik overlay
        function setupOverlayClick() {
            const overlay = document.querySelector('.sidebar-overlay');
            overlay.addEventListener('click', toggleSidebar);
        }

        // Load sidebar state saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Tambahkan overlay element jika belum ada
            if (!document.querySelector('.sidebar-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
            }

            // Setup overlay click
            setupOverlayClick();

            // Setup hamburger button
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            if (hamburgerBtn) {
                hamburgerBtn.addEventListener('click', toggleSidebar);
            }

            // Tutup sidebar ketika link di klik (di mobile)
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        toggleSidebar();
                    }
                });
            });

            // Load active section
            const activeSection = localStorage.getItem('activeSection');
            if (activeSection && activeSection !== 'dashboard') {
                showSection(activeSection);
            }
        });
    </script>

    <script>
        function viewDetails(id) {
            // isi modal dengan loading text dulu
            document.getElementById("documentPreview").innerHTML = "<p class='text-center'>Memuat dokumen...</p>";

            // load isi dokumen via AJAX
            fetch("view_document.php?id=" + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("documentPreview").innerHTML = data;
                })
                .catch(err => {
                    document.getElementById("documentPreview").innerHTML = "<p class='text-danger'>Gagal memuat dokumen.</p>";
                });

            // tampilkan modal
            var modal = new bootstrap.Modal(document.getElementById('documentModal'));
            modal.show();
        }
    </script>

</body>

</html>