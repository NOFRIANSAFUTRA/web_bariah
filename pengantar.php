<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prosedur Surat Pengantar</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    :root {
      --accent-color: #4361ee;
      --primary-light: #4895ef;
      --secondary-color: #3f37c9;
      --dark-color: #2b2d42;
      --light-color: #f8f9fa;
      --success-color: #4cc9f0;
      --border-radius: 12px;
      --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      padding: 20px;
      color: var(--dark-color);
      line-height: 1.6;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      position: relative;
    }

    .header {
      background: linear-gradient(135deg, var(--accent-color) 0%, var(--secondary-color) 100%);
      color: white;
      padding: 30px 40px;
      position: relative;
      overflow: hidden;
    }

    .header::before {
      content: "";
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100%;
      height: 200%;
      background: rgba(255, 255, 255, 0.1);
      transform: rotate(30deg);
    }

    h1 {
      font-size: 2.2rem;
      margin-bottom: 10px;
      position: relative;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header p {
      opacity: 0.9;
      font-size: 1.1rem;
    }

    .content {
      padding: 40px;
    }

    .info-box {
      background: linear-gradient(135deg, #eef6ff 0%, #e0f0ff 100%);
      border-left: 5px solid var(--accent-color);
      padding: 20px 25px;
      border-radius: var(--border-radius);
      margin-bottom: 30px;
      color: var(--dark-color);
      display: flex;
      align-items: flex-start;
      gap: 15px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .info-box i {
      color: var(--accent-color);
      font-size: 1.2rem;
      margin-top: 2px;
    }

    .steps-container {
      margin-bottom: 40px;
    }

    .step {
      display: flex;
      margin-bottom: 25px;
      position: relative;
    }

    .step-number {
      background: var(--accent-color);
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-right: 20px;
      flex-shrink: 0;
      box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
      position: relative;
      z-index: 2;
    }

    .step-content {
      flex: 1;
      padding-bottom: 10px;
    }

    .step-content h3 {
      margin-bottom: 8px;
      color: var(--dark-color);
    }

    .step-content p {
      color: #555;
    }

    .step:not(:last-child)::after {
      content: "";
      position: absolute;
      top: 40px;
      left: 20px;
      width: 2px;
      height: calc(100% - 20px);
      background: #e0e0e0;
      z-index: 1;
    }

    .action-buttons {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-top: 30px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 14px 25px;
      border-radius: var(--border-radius);
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
      cursor: pointer;
      border: none;
      font-size: 1rem;
    }

    .btn-primary {
      background: var(--accent-color);
      color: white;
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .btn-primary:hover {
      background: var(--secondary-color);
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }

    .btn-secondary {
      background: transparent;
      color: var(--accent-color);
      border: 2px solid var(--accent-color);
    }

    .btn-secondary:hover {
      background: rgba(67, 97, 238, 0.05);
      transform: translateY(-3px);
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 30px;
      color: #6c757d;
      text-decoration: none;
      font-weight: 500;
      transition: var(--transition);
    }

    .back-link:hover {
      color: var(--accent-color);
      transform: translateX(-5px);
    }

    @media (max-width: 768px) {
      .container {
        margin: 20px auto;
      }

      .header,
      .content {
        padding: 25px;
      }

      h1 {
        font-size: 1.8rem;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>
        <i class="fas fa-envelope-open-text"></i> Prosedur Pengajuan Surat Pengantar
      </h1>
      <p>Ikuti langkah-langkah berikut untuk mengajukan surat pengantar dengan mudah</p>
    </div>

    <div class="content">
      <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <div>
          <strong>Informasi Penting:</strong> Pastikan Anda sudah memenuhi persyaratan yang berlaku sebelum mengajukan surat pengantar. Proses ini memakan waktu 1-3 hari kerja.
        </div>
      </div>

      <div class="steps-container">
        <div class="step">
          <div class="step-number">1</div>
          <div class="step-content">
            <h3>Buka Menu Surat Pengantar</h3>
            <p>Akses menu <strong>Surat Pengantar</strong> di halaman utama sistem atau melalui navigasi utama.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">2</div>
          <div class="step-content">
            <h3>Isi Data Pribadi dan Tujuan</h3>
            <p>Lengkapi formulir dengan data pribadi yang akurat dan tujuan surat sesuai kebutuhan Anda.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">3</div>
          <div class="step-content">
            <h3>Unggah Dokumen Pendukung</h3>
            <p>Jika diperlukan, unggah dokumen pendukung seperti KTP, KK, atau dokumen relevan lainnya.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">4</div>
          <div class="step-content">
            <h3>Periksa Kembali Data</h3>
            <p>Pastikan semua data yang telah diisi sudah benar dan tidak ada kesalahan sebelum melanjutkan.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">5</div>
          <div class="step-content">
            <h3>Ajukan Surat</h3>
            <p>Klik tombol <strong>Ajukan Surat</strong> untuk mengirim permohonan Anda ke pihak administrasi.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">6</div>
          <div class="step-content">
            <h3>Tunggu Konfirmasi</h3>
            <p>Pantau status pengajuan Anda melalui sistem. Anda akan mendapat notifikasi saat surat siap diambil.</p>
          </div>
        </div>
      </div>

      <div class="action-buttons">
        <a href="form_pengajuan.php" class="btn btn-primary">
          <i class="fas fa-paper-plane"></i>
          <span>Ajukan Surat Sekarang</span>
        </a>

        <a href="#" class="btn btn-secondary">
          <i class="fas fa-download"></i>
          <span>Download Panduan</span>
        </a>
      </div>

      <a href="index.html" class="back-link">
        <i class="fas fa-arrow-left"></i>
        <span>Kembali ke Halaman Utama</span>
      </a>
    </div>
  </div>
</body>

</html>