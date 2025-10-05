: <?php
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

    // Jika user tidak ditemukan
    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit();
    }

    // Set variabel untuk tampilan
    $namaLengkap = htmlspecialchars($user['nama_lengkap'] ?? '');
    $username = htmlspecialchars($user['username'] ?? '');
    $inisial = !empty($namaLengkap) ? strtoupper(substr($namaLengkap, 0, 2)) : 'US';


    ?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Masyarakat - Kecamatan Simpang Kiri</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary-color: #2c3e50;
            --primary-light: #3d566e;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --sidebar-width: 280px;
            --header-height: 80px;
            --transition-speed: 0.3s;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body {
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        i {
            margin-right: 10px;
        }

        /* Main Layout */
        .user-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            background-color: var(--primary-light);
        }

        .sidebar-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-header p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .sidebar-nav {
            padding: 15px 0;
        }

        .sidebar-nav ul li {
            margin: 5px 0;
        }

        .sidebar-nav ul li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
            font-size: 0.95rem;
            border-left: 4px solid transparent;
        }

        .sidebar-nav ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--accent-color);
        }

        .sidebar-nav ul li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--accent-color);
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) ease;
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        /* Content Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: white;
            box-shadow: var(--box-shadow);
            height: var(--header-height);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content-wrapper {
            display: flex;
            align-items: center;
        }

        .header-text {
            margin-left: 15px;
        }

        .page-title {
            font-size: 1.5rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }

        .header-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: var(--dark-color);
            padding: 5px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .mobile-menu-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-details strong {
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .user-username {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Content Sections */
        .content-section {
            padding: 25px 30px;
            display: none;
        }

        .content-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 18px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
        }

        .card-header h3 {
            font-size: 1.2rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }

        .card-body {
            padding: 25px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding: 20px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #219653;
            transform: translateY(-2px);
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        /* Surat Cards */
        .surat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin: 25px 0;
        }

        .surat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border-top: 4px solid var(--accent-color);
        }

        .surat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .surat-card h3 {
            font-size: 1.2rem;
            margin: 15px 0;
            color: var(--dark-color);
        }

        .surat-card p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .card-icon {
            font-size: 2.2rem;
            color: var(--accent-color);
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 20px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }

        .card-meta span {
            display: flex;
            align-items: center;
        }

        .btn-ajukan {
            width: 100%;
            padding: 12px;
            background: var(--accent-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .btn-ajukan:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        /* Surat Info Card */
        .surat-info-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            border-left: 5px solid var(--accent-color);
        }

        .info-card-icon {
            font-size: 3rem;
            color: var(--accent-color);
            margin-right: 25px;
            min-width: 60px;
            text-align: center;
        }

        .info-card-content h3 {
            margin-bottom: 15px;
            color: var(--dark-color);
            font-size: 1.3rem;
        }

        .info-card-content p {
            color: #6c757d;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #6c757d;
            gap: 8px;
        }

        /* Panduan Section */
        .panduan-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-top: 30px;
            box-shadow: var(--box-shadow);
        }

        .panduan-section h2 {
            margin-bottom: 20px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            font-size: 1.3rem;
        }

        .panduan-list {
            padding-left: 25px;
        }

        .panduan-list li {
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .catatan-penting {
            background: #fff8e1;
            padding: 15px;
            border-left: 4px solid var(--warning-color);
            margin-top: 20px;
            font-size: 0.9rem;
            color: #5a4a42;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .catatan-penting i {
            color: var(--warning-color);
            font-size: 1.2rem;
        }

        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.95rem;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
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

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert i {
            font-size: 1.5rem;
            margin-right: 15px;
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            :root {
                --sidebar-width: 250px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
                width: var(--sidebar-width);
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                padding: 15px;
            }

            .header-content-wrapper {
                width: 100%;
                justify-content: space-between;
            }

            .user-info {
                margin-top: 15px;
                width: 100%;
                justify-content: flex-end;
            }

            .surat-info-card {
                flex-direction: column;
                text-align: center;
            }

            .info-card-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .content-section {
                padding: 20px 15px;
            }
        }

        @media (max-width: 576px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .surat-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
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


                    <li>
                        <a href="#" id="logoutBtn" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>

                    <script>
                        document.getElementById('logoutBtn').addEventListener('click', function(e) {
                            e.preventDefault(); // Supaya link tidak langsung dijalankan

                            Swal.fire({
                                title: 'Yakin ingin logout?',
                                text: "Kamu akan keluar dari akun ini.",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Ya, logout!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'logout.php';
                                }
                            });
                        });
                    </script>

                </ul>
            </nav>
        </div>

        <!-- Tambahkan di atas </body> -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- DASHBOARD SECTION -->
            <div id="dashboard-section" class="content-section active">
                <div class="content-header">
                    <div class="header-content-wrapper">
                        <button class="mobile-menu-btn" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="header-text">
                            <h2 class="page-title"><i class="fas fa-tachometer-alt"></i> Dashboard Masyarakat</h2>
                            <p class="header-subtitle">Selamat datang di sistem pelayanan digital Kecamatan Simpang Kiri</p>
                        </div>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar" title="<?= $namaLengkap ?>">
                            <?= $inisial ?>
                        </div>
                        <div class="user-details">
                            <strong><?= $namaLengkap ?></strong>
                            <div class="user-username">@<?= $username ?></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Pengumuman!</strong> Pelayanan surat online kini tersedia 24 jam. Untuk pengambilan surat fisik, kantor buka Senin-Jumat pukul 08.00-16.00 WIB.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-number">5</div>
                        <div class="stat-label">Total Pengajuan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">3</div>
                        <div class="stat-label">Disetujui</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number">1</div>
                        <div class="stat-label">Proses</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-number">1</div>
                        <div class="stat-label">Ditolak</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bullhorn"></i> Pengumuman Terbaru</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Perubahan Jam Layanan!</strong> Mulai 1 Januari 2024, pelayanan offline di kantor kecamatan akan dimulai pukul 07.30 WIB.
                            </div>
                        </div>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Fitur Baru!</strong> Kini Anda bisa melacak status pengajuan surat secara real-time melalui menu Status Surat.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AJUKAN SURAT SECTION -->
            <section id="ajukan-surat-section" class="content-section">
                <div class="content-header">
                    <div class="header-content-wrapper">
                        <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="header-text">
                            <h2 class="page-title"><i class="fas fa-plus-circle"></i> Ajukan Surat</h2>
                            <p class="header-subtitle">Layanan pengajuan surat resmi Kecamatan Simpang Kiri</p>
                        </div>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar" title="<?= $namaLengkap ?>">
                            <?= $inisial ?>
                        </div>
                        <div class="user-details">
                            <strong><?= $namaLengkap ?></strong>
                            <div class="user-username">@<?= $username ?></div>
                        </div>
                    </div>
                </div>

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
                        <div class="card-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h3>Surat Pengantar</h3>
                        <p>Untuk keperluan administrasi di instansi lain seperti pengurusan dokumen kependudukan, perizinan, atau keperluan lainnya</p>
                        <div class="card-meta">
                            <span><i class="fas fa-clock"></i> Selesai hari ini</span>
                            <span><i class="fas fa-file-alt"></i> Butuh KK + KTP</span>
                        </div>
                        <form action="form_pengajuan.php" method="get">
                            <input type="hidden" name="jenis" value="pengantar">
                            <button type="submit" class="btn-ajukan">Ajukan Sekarang</button>
                        </form>
                    </div>

                    <!-- Surat Keterangan Domisili -->
                    <div class="surat-card" onclick="openSuratForm('domisili')">
                        <div class="card-icon">
                            <i class="fas fa-house-user"></i>
                        </div>
                        <h3>Surat Domisili</h3>
                        <p>Bukti alamat tinggal resmi untuk keperluan administrasi, sekolah, pekerjaan, atau pembukaan rekening bank</p>
                        <div class="card-meta">
                            <span><i class="fas fa-clock"></i> 1 hari kerja</span>
                            <span><i class="fas fa-file-alt"></i> + Surat RT/RW</span>
                        </div>
                        <form action="form_pengajuan2.php" method="get">
                            <input type="hidden" name="jenis" value="domisili">
                            <button type="submit" class="btn-ajukan">Ajukan Sekarang</button>
                        </form>
                    </div>


                    <!-- Surat Keterangan Usaha -->
                    <div class="surat-card" onclick="openSuratForm('usaha')">
                        <div class="card-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h3>Surat Keterangan Usaha</h3>
                        <p>Legalitas usaha mikro/kecil untuk pengajuan pinjaman, perizinan, atau keperluan administrasi lainnya</p>
                        <div class="card-meta">
                            <span><i class="fas fa-clock"></i> 2 hari kerja</span>
                            <span><i class="fas fa-file-alt"></i> + Foto usaha</span>
                        </div>
                        <form action="form_pengajuan3.php" method="get">
                            <input type="hidden" name="jenis" value="usaha">
                            <button type="submit" class="btn-ajukan">Ajukan Sekarang</button>
                        </form>
                    </div>

                    <!-- Surat Keterangan Tidak Mampu -->
                    <div class="surat-card" onclick="openSuratForm('tidak-mampu')">
                        <div class="card-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h3>Surat Keterangan Tidak Mampu</h3>
                        <p>Untuk pengajuan bantuan sosial, beasiswa, atau program pemerintah lainnya bagi keluarga kurang mampu</p>
                        <div class="card-meta">
                            <span><i class="fas fa-clock"></i> 3 hari kerja</span>
                            <span><i class="fas fa-file-alt"></i> + Survey petugas</span>
                        </div>
                        <form action="form_tidak_mampu.php" method="get">
                            <input type="hidden" name="jenis" value="usaha">
                            <button type="submit" class="btn-ajukan">Ajukan Sekarang</button>
                        </form>

                    </div>

                    <!-- Surat Keterangan Meninggal -->
                    <div class="surat-card" onclick="openSuratForm('meninggal')">
                        <div class="card-icon">
                            <i class="fas fa-skull-crossbones"></i>
                        </div>
                        <h3>Surat Keterangan Meninggal</h3>
                        <p>Administrasi legal untuk keperluan kematian seperti pembuatan akta kematian, klaim asuransi, atau warisan</p>
                        <div class="card-meta">
                            <span><i class="fas fa-clock"></i> Proses prioritas</span>
                            <span><i class="fas fa-file-alt"></i> + Akta kematian</span>
                        </div>
                        <button class="btn-ajukan">Ajukan Sekarang</button>
                    </div>

                    <!-- Surat Rekomendasi Nikah -->
                    <div class="surat-card" onclick="openSuratForm('rekomendasi-nikah')">
                        <div class="card-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>Rekomendasi Nikah</h3>
                        <p>Persyaratan administrasi pernikahan untuk pengurusan di KUA atau Catatan Sipil</p>
                        <div class="card-meta">
                            <span><i class="fas fa-clock"></i> 2 hari kerja</span>
                            <span><i class="fas fa-file-alt"></i> + Akta kelahiran</span>
                        </div>
                        <button class="btn-ajukan">Ajukan Sekarang</button>
                    </div>
                </div>

                <div class="panduan-section">
                    <h2><i class="fas fa-info-circle"></i> Panduan Pengajuan Surat</h2>
                    <ol class="panduan-list">
                        <li>Pilih jenis surat yang ingin diajukan dari daftar di atas</li>
                        <li>Isi formulir pengajuan dengan data yang valid dan lengkap</li>
                        <li>Upload dokumen pendukung yang diperlukan (format PDF/JPG/PNG)</li>
                        <li>Submit pengajuan dan tunggu verifikasi dari petugas</li>
                        <li>Pantau status pengajuan melalui menu Status Surat</li>
                        <li>Setelah disetujui, surat dapat diambil di kantor atau didownload versi digital</li>
                    </ol>
                    <div class="catatan-penting">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Catatan Penting:</strong> Pastikan data yang diisi valid dan dokumen pendukung lengkap. Pengajuan dengan data palsu atau tidak lengkap akan ditolak dan dapat dikenai sanksi sesuai peraturan daerah.
                        </div>
                    </div>
                </div>
            </section>

            <!-- STATUS SURAT SECTION -->
            <section id="status-surat-section" class="content-section">
                <div class="content-header">
                    <div class="header-content-wrapper">
                        <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="header-text">
                            <h2 class="page-title"><i class="fas fa-list-alt"></i> Status Surat</h2>
                            <p class="header-subtitle">Lacak status pengajuan surat Anda</p>
                        </div>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar" title="<?= $namaLengkap ?>">
                            <?= $inisial ?>
                        </div>
                        <div class="user-details">
                            <strong><?= $namaLengkap ?></strong>
                            <div class="user-username">@<?= $username ?></div>
                        </div>
                    </div>
                </div>

                <div class="card">

                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                Berikut adalah daftar pengajuan surat yang sedang dalam proses. Anda bisa melacak status terbaru di sini.
                            </div>
                        </div>

                        <?php
                        // Ambil data pengajuan surat dari database
                        $query = "SELECT * FROM pengajuan_surat WHERE user_id = ? ORDER BY tanggal_pengajuan DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id); // Pastikan $user_id sudah terdefinisi
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                        ?>
                            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                                <thead>
                                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                        <th style="padding: 12px 15px; text-align: left;">Jenis Surat</th>
                                        <th style="padding: 12px 15px; text-align: left;">Tanggal Pengajuan</th>
                                        <th style="padding: 12px 15px; text-align: left;">Status</th>
                                        <th style="padding: 12px 15px; text-align: left;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
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

                                        // Tentukan style status
                                        $status_style = '';
                                        $status_icon = '';
                                        if ($row['status'] == 'diterima') {
                                            $status_style = 'background-color: #d4edda; color: #155724;';
                                            $status_icon = 'fa-check-circle';
                                        } elseif ($row['status'] == 'diproses') {
                                            $status_style = 'background-color: #fff3cd; color: #856404;';
                                            $status_icon = 'fa-clock';
                                        } elseif ($row['status'] == 'ditolak') {
                                            $status_style = 'background-color: #f8d7da; color: #721c24;';
                                            $status_icon = 'fa-times-circle';
                                        }

                                        // Format tanggal
                                        $tanggal = date('d M Y', strtotime($row['tanggal_pengajuan']));
                                    ?>
                                        <tr style="border-bottom: 1px solid #dee2e6;">
                                            <td style="padding: 12px 15px;"><?= htmlspecialchars($nama_surat) ?></td>
                                            <td style="padding: 12px 15px;"><?= $tanggal ?></td>

                                            <!-- Kolom Status -->
                                            <td style="padding: 12px 15px;">
                                                <span style="<?= $status_style ?> padding: 5px 10px; border-radius: 4px; font-size: 0.85rem;">
                                                    <i class="fas <?= $status_icon ?>"></i>
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>

                                            <!-- Kolom Aksi -->
                                            <td style="padding: 12px 15px;">
                                                <?php if ($row['status'] == 'diterima'): ?>
                                                    <!-- Download surat -->
                                                    <a href="generate_surat.php?id=<?= $row['id'] ?>"
                                                        class="btn btn-primary"
                                                        style="padding: 5px 10px; font-size: 0.85rem;" target="_blank">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>

                                                <?php elseif ($row['status'] == 'ditolak'): ?>
                                                    <!-- Detail alasan penolakan -->
                                                    <button class="btn btn-danger"
                                                        style="padding: 5px 10px; font-size: 0.85rem;"
                                                        onclick="alert('Alasan penolakan: <?= htmlspecialchars($row['catatan'] ?? 'Tidak ada catatan') ?>')">
                                                        <i class="fas fa-info-circle"></i> Detail
                                                    </button>

                                                <?php else: ?>
                                                    <!-- Masih diproses → tidak ada aksi -->
                                                    <span style="font-size: 0.85rem; color: #6c757d;">
                                                        <i class="fas fa-hourglass-half"></i> Menunggu
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Belum ada pengajuan surat. Silakan ajukan surat melalui menu "Ajukan Surat".
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </section>

            <!-- RIWAYAT SURAT SECTION -->


            <!-- PROFILE SECTION -->

        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Show selected section and hide others
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(`${sectionId}-section`).classList.add('active');

            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('active');
                }
            });
        }

        // Open surat form (placeholder function)


        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a hash in the URL and show corresponding section
            if (window.location.hash) {
                const sectionId = window.location.hash.substring(1);
                if (document.getElementById(`${sectionId}-section`)) {
                    showSection(sectionId);
                }
            }
        });
    </script>
    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(`${sectionId}-section`).classList.add('active');

            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('active');
                }
            });

            // 🔑 Tutup sidebar otomatis (khusus mobile)
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.remove('active');
        }
    </script>
</body>

</html>