<?php
require 'vendor/autoload.php'; // load dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

// koneksi database
$conn = new mysqli("localhost", "root", "", "kecamatan_simpang_kiri"); 
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ambil ID dari URL (aman pakai prepared statement)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT p.*, u.nama_lengkap 
                        FROM pengajuan_surat p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data tidak ditemukan!");
}

$data = $result->fetch_assoc();

// amankan output dari DB
$nama = htmlspecialchars($data['nama_lengkap']);
$jenis_surat = htmlspecialchars($data['jenis_surat']);
$keperluan = htmlspecialchars($data['keperluan']);

// =====================
// isi surat (lebih profesional)
// =====================
$html = "
<html>
<head>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; }
        .kop-surat { text-align: center; margin-bottom: 20px; }
        .kop-surat h2, .kop-surat h3 { margin: 0; }
        hr { border: 1px solid #000; margin: 10px 0; }
        .content { margin-top: 20px; }
        table { margin: 10px 0; }
        table td { padding: 4px 8px; vertical-align: top; }
        .ttd { margin-top: 60px; text-align: right; }
    </style>
</head>
<body>
    <div class='kop-surat'>
        <h2>PEMERINTAH KECAMATAN SIMPANG KIRI</h2>
        <h3>SURAT KETERANGAN</h3>
        <hr>
    </div>

    <div class='content'>
        <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
        <table>
            <tr>
                <td><b>Nama</b></td>
                <td>: {$nama}</td>
            </tr>
            <tr>
                <td><b>Jenis Surat</b></td>
                <td>: {$jenis_surat}</td>
            </tr>
            <tr>
                <td><b>Keperluan</b></td>
                <td>: {$keperluan}</td>
            </tr>
        </table>
        <p>
            Dengan ini dinyatakan bahwa permohonan surat tersebut telah 
            <b>DITERIMA</b> dan sah dikeluarkan oleh pihak Kecamatan Simpang Kiri.
        </p>
    </div>

    <div class='ttd'>
        <p>Simpang Kiri, " . date('d-m-Y') . "</p>
        <p><b>Pihak Berwenang</b></p>
        <br><br><br>
        <p>________________________</p>
    </div>
</body>
</html>
";

// set dompdf options
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Times New Roman');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); 
$dompdf->render();

// nama file lebih aman
$filename = "surat_" . str_replace(" ", "_", strtolower($nama)) . ".pdf";

// download otomatis
$dompdf->stream($filename, array("Attachment" => true));
?>
