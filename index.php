<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Layanan Digital Kecamatan Simpang Kiri, Kota Subulussalam">
    <meta name="keywords" content="kecamatan, simpang kiri, subulussalam, aceh, pelayanan digital">
    <title>Kecamatan Simpang Kiri - Kota Subulussalam</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Variables for better maintainability */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --background-primary: #ffffff;
            --background-secondary: #f8f9fa;
            --background-tertiary: #ecf0f1;
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.12);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --max-width: 1200px;
        }

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.7;
            color: var(--text-primary);
            background-color: var(--background-secondary);
            font-size: 16px;
        }

        /* Utility Classes */
        .container {
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 0 20px;
        }

        .text-center {
            text-align: center;
        }

        .mb-1 {
            margin-bottom: 0.5rem;
        }

        .mb-2 {
            margin-bottom: 1rem;
        }

        .mb-3 {
            margin-bottom: 1.5rem;
        }

        .mb-4 {
            margin-bottom: 2rem;
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
        }

        h2 {
            font-size: clamp(1.5rem, 3vw, 2rem);
        }

        h3 {
            font-size: clamp(1.25rem, 2.5vw, 1.5rem);
        }

        p {
            margin-bottom: 1rem;
            color: var(--text-secondary);
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: var(--shadow-medium);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1001;
        }

        .logo__image {
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo__text h1 {
            font-size: 1.4rem;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .logo__text p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0;
            color: #bdc3c7;
        }

        /* Navigation */
        .nav {
            display: flex;
            align-items: center;
        }

        .nav__list {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav__link {
            padding: 0.5rem 0;
            font-weight: 500;
            position: relative;
            transition: var(--transition);
            display: block;
        }

        .nav__link:hover {
            color: var(--secondary-color);
        }

        .nav__link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary-color);
            transition: var(--transition);
        }

        .nav__link:hover::after {
            width: 100%;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 0.875rem;
        }

        .btn--primary {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
        }

        .btn--primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn--large {
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        /* Mobile Menu Toggle */
        .nav__toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1001;
            background: none;
            border: none;
            padding: 5px;
        }

        .nav__toggle span {
            width: 28px;
            height: 3px;
            background: white;
            border-radius: 3px;
            transition: var(--transition);
            display: block;
        }

        .nav__toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .nav__toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .nav__toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.4),
                    rgba(0, 0, 0, 0.4)),
                url('images/halaman_utama.jpg');
            /* ganti dengan path gambar kamu */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

            color: white;
            padding: 6rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path d="M0,50 Q250,0 500,50 T1000,50 L1000,100 L0,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
            background-position: bottom;
        }

        .hero__content {
            position: relative;
            z-index: 2;
        }

        .hero__title {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #ecf0f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero__subtitle {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            color: #ecf0f1;
        }

        /* Section Styles */
        .section {
            padding: 5rem 0;
        }

        .section--white {
            background-color: var(--background-primary);
        }

        .section--gray {
            background-color: var(--background-secondary);
        }

        .section__title {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-primary);
            position: relative;
            padding-bottom: 20px;
        }

        .section__title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .section__subtitle {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            color: var(--text-secondary);
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: var(--background-primary);
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
        }

        .service-card__icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .service-card__title {
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .service-card__description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .service-note {
            margin-top: 3rem;
            padding: 1.5rem;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: var(--border-radius);
        }

        .service-note p {
            margin: 0;
            color: #856404;
        }

        /* Info Section */
        .info-grid {
            display: grid;
            gap: 2rem;
        }

        .info-card {
            background: var(--background-primary);
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            border-left: 4px solid var(--secondary-color);
        }

        .info-card__title {
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-card__icon {
            width: 32px;
            height: 32px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* About Section */
        .about__content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .about__text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-secondary);
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2c3e50 100%);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section__title {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .footer-section__list {
            list-style: none;
        }

        .footer-section__item {
            margin-bottom: 0.75rem;
        }

        .footer-section__link:hover {
            color: var(--secondary-color);
        }

        .footer__bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #bdc3c7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                position: relative;
            }

            .nav__toggle {
                display: flex;
                position: absolute;
                right: 20px;
                top: 50%;
                transform: translateY(-50%);
            }

            .nav {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
                transition: var(--transition);
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
                padding-top: 80px;
            }

            .nav.active {
                right: 0;
            }

            .nav__list {
                flex-direction: column;
                width: 100%;
                padding: 0 2rem;
                gap: 0;
            }

            .nav__list li {
                width: 100%;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav__link {
                padding: 1rem 0;
                width: 100%;
            }

            .nav__link::after {
                display: none;
            }

            .nav__list .btn {
                margin-top: 1rem;
                width: 100%;
            }

            .logo__text h1 {
                font-size: 1.1rem;
            }

            .logo__text p {
                font-size: 0.75rem;
            }

            .logo__image {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .hero {
                padding: 4rem 0;
            }

            .section {
                padding: 3rem 0;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .service-card {
                padding: 2rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .hero {
                padding: 3rem 0;
            }

            .service-card {
                padding: 1.5rem;
            }

            .info-card {
                padding: 2rem;
            }

            .logo__text h1 {
                font-size: 0.95rem;
            }

            .logo__text p {
                font-size: 0.7rem;
            }

            .logo__image {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Overlay for mobile menu */
        .nav-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: var(--transition);
        }

        .nav-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus states */
        .btn:focus,
        .nav__link:focus,
        .nav__toggle:focus {
            outline: 2px solid var(--secondary-color);
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <div class="nav-overlay"></div>

    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo__image">SK</div>
                    <div class="logo__text">
                        <h1>Kecamatan Simpang Kiri</h1>
                        <p>Kota Subulussalam, Provinsi Aceh</p>
                    </div>
                </div>

                <button class="nav__toggle" aria-label="Toggle navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="nav">
                    <ul class="nav__list">
                        <li><a href="#beranda" class="nav__link">Beranda</a></li>
                        <li><a href="#layanan-surat" class="nav__link">Layanan</a></li>
                        <li><a href="#informasi" class="nav__link">Informasi</a></li>
                        <li><a href="#tentang" class="nav__link">Tentang Kami</a></li>
                        <li><a href="login.php" class="btn btn--primary">Login</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section id="beranda" class="hero">
            <div class="container">
                <div class="hero__content">
                    <h2 class="hero__title">Portal Layanan Digital Kecamatan Simpang Kiri</h2>
                    <p class="hero__subtitle">Pelayanan administrasi terpadu untuk 14 kampung dengan 35.345 jiwa penduduk. Mudah, cepat, dan terpercaya.</p>
                    <a href="login.php" class="btn btn--primary btn--large">Masuk ke Sistem</a>
                </div>
            </div>
        </section>

        <section id="layanan-surat" class="section section--white">
            <div class="container">
                <h2 class="section__title">Layanan Surat Menyurat</h2>
                <p class="section__subtitle">Pelayanan pengajuan surat resmi Kecamatan Simpang Kiri secara digital</p>

                <div class="services-grid">
                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">📨</div>
                        <h3 class="service-card__title">Surat Pengantar</h3>
                        <p class="service-card__description">Untuk berbagai keperluan administrasi yang membutuhkan pengantar dari pihak kecamatan.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">🏠</div>
                        <h3 class="service-card__title">Surat Keterangan Domisili</h3>
                        <p class="service-card__description">Bukti legal tempat tinggal untuk keperluan administrasi dan legalitas.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">💼</div>
                        <h3 class="service-card__title">Surat Keterangan Usaha (SKU)</h3>
                        <p class="service-card__description">Pengakuan legal terhadap usaha yang dijalankan di wilayah kecamatan.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">🤲</div>
                        <h3 class="service-card__title">Surat Keterangan Kurang Mampu</h3>
                        <p class="service-card__description">Untuk keperluan mendapatkan bantuan sosial atau keringanan biaya.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">⚰️</div>
                        <h3 class="service-card__title">Surat Keterangan Meninggal Dunia</h3>
                        <p class="service-card__description">Dokumen resmi untuk keperluan administrasi terkait kematian.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">💍</div>
                        <h3 class="service-card__title">Surat Rekomendasi Nikah</h3>
                        <p class="service-card__description">Persyaratan administrasi untuk proses pernikahan.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">🆘</div>
                        <h3 class="service-card__title">Surat Rekomendasi Bantuan</h3>
                        <p class="service-card__description">Untuk pengajuan bantuan sosial atau program pemerintah.</p>
                    </div>

                    <div class="service-card fade-in-up">
                        <div class="service-card__icon">🎪</div>
                        <h3 class="service-card__title">Surat Rekomendasi Kegiatan</h3>
                        <p class="service-card__description">Persetujuan kegiatan keramaian atau event di wilayah kecamatan.</p>
                    </div>
                </div>

                <div class="service-note">
                    <p><strong>Catatan:</strong> Layanan ini hanya untuk surat-surat kebutuhan masyarakat. Surat internal antar-instansi tidak termasuk dalam layanan ini.</p>
                </div>
            </div>
        </section>

        <section id="informasi" class="section section--gray">
            <div class="container">
                <h2 class="section__title">Informasi Terkini</h2>
                <div class="info-grid">
                    <div class="info-card">
                        <h3 class="info-card__title">
                            <span class="info-card__icon">💻</span>
                            Digitalisasi Pelayanan
                        </h3>
                        <p>Kecamatan Simpang Kiri kini menyediakan layanan digital untuk memudahkan masyarakat dalam mengurus administrasi tanpa harus datang ke kantor kecamatan. Sistem ini tersedia 24/7 untuk kemudahan akses.</p>
                    </div>
                    <div class="info-card">
                        <h3 class="info-card__title">
                            <span class="info-card__icon">🕒</span>
                            Jam Operasional
                        </h3>
                        <div>
                            <strong>Senin - Kamis:</strong> 08.00 - 16.00 WIB<br>
                            <strong>Jumat:</strong> 08.00 - 17.00 WIB<br>
                            <strong>Sabtu - Minggu:</strong> Tutup<br>
                            <em>*Layanan digital tersedia 24 jam</em>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tentang" class="section section--white">
            <div class="container">
                <h2 class="section__title">Tentang Kecamatan Simpang Kiri</h2>
                <div class="about__content">
                    <p class="about__text">Kecamatan Simpang Kiri merupakan salah satu kecamatan di Kota Subulussalam, Provinsi Aceh, yang terdiri dari 14 kampung dengan jumlah penduduk mencapai 35.345 jiwa. Kami berkomitmen untuk memberikan pelayanan administrasi terbaik bagi masyarakat dengan mengedepankan transparansi, akuntabilitas, dan inovasi.</p>
                    <p class="about__text">Dengan sistem digital ini, kami berharap dapat meningkatkan efisiensi pelayanan dan memudahkan masyarakat dalam mengakses layanan administrasi kapan saja dan di mana saja. Visi kami adalah menjadi kecamatan yang unggul dalam pelayanan publik berbasis teknologi untuk kesejahteraan masyarakat.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3 class="footer-section__title">Kontak Kami</h3>
                    <div>
                        <p>📍 Jl. Kecamatan No. 1, Simpang Kiri<br>
                            Kota Subulussalam, Aceh 24882</p>
                        <p>📧 kec.simpangkiri@subulussalamkota.go.id</p>
                        <p>📞 (0627) 12345</p>
                        <p>📱 WhatsApp: 0812-3456-7890</p>
                    </div>
                </div>
                <div class="footer-section">
                    <h3 class="footer-section__title">Tautan Cepat</h3>
                    <ul class="footer-section__list">
                        <li class="footer-section__item"><a href="#beranda" class="footer-section__link">Beranda</a></li>
                        <li class="footer-section__item"><a href="#layanan-surat" class="footer-section__link">Layanan</a></li>
                        <li class="footer-section__item"><a href="#informasi" class="footer-section__link">Informasi</a></li>
                        <li class="footer-section__item"><a href="#tentang" class="footer-section__link">Tentang Kami</a></li>
                        <li class="footer-section__item"><a href="login.php" class="footer-section__link">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="footer-section__title">Layanan Digital</h3>
                    <ul class="footer-section__list">
                        <li class="footer-section__item">✓ Pembuatan KTP Online</li>
                        <li class="footer-section__item">✓ Pengajuan KK Digital</li>
                        <li class="footer-section__item">✓ Akta Kelahiran Online</li>
                        <li class="footer-section__item">✓ Surat Menyurat Digital</li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <p>&copy; 2025 Kecamatan Simpang Kiri. Semua hak dilindungi undang-undang.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.querySelector('.nav__toggle');
            const nav = document.querySelector('.nav');
            const overlay = document.querySelector('.nav-overlay');
            const navLinks = document.querySelectorAll('.nav__link');
            const body = document.body;

            // Toggle menu
            if (toggle) {
                toggle.addEventListener('click', function() {
                    toggle.classList.toggle('active');
                    nav.classList.toggle('active');
                    overlay.classList.toggle('active');
                    body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
                });
            }

            // Close menu when clicking overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    toggle.classList.remove('active');
                    nav.classList.remove('active');
                    overlay.classList.remove('active');
                    body.style.overflow = '';
                });
            }

            // Close menu when clicking nav links
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        toggle.classList.remove('active');
                        nav.classList.remove('active');
                        overlay.classList.remove('active');
                        body.style.overflow = '';
                    }
                });
            });

            // Close menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    toggle.classList.remove('active');
                    nav.classList.remove('active');
                    overlay.classList.remove('active');
                    body.style.overflow = '';
                }
            });

            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const headerOffset = 80;
                        const elementPosition = target.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add fade-in animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.service-card, .info-card').forEach(card => {
                observer.observe(card);
            });

            // Active header on scroll
            let lastScroll = 0;
            const header = document.querySelector('.header');

            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 100) {
                    header.style.padding = '0.5rem 0';
                } else {
                    header.style.padding = '1rem 0';
                }

                lastScroll = currentScroll;
            });
        });
    </script>
</body>

</html