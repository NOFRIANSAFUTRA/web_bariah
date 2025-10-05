<?php
include 'db.php';
session_start();

// Ambil ID pengajuan dari parameter
if (!isset($_GET['id'])) {
    die("ID pengajuan tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data pengajuan dari database
$query = "SELECT p.*, u.nama_lengkap, u.nik, u.alamat 
          FROM pengajuan_surat p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = $id AND p.status = 'diterima'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("Data surat tidak ditemukan atau belum diterima.");
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Pengantar</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 50px;
        }
        .kop {
            text-align: center;
            margin-bottom: 30px;
        }
        .kop h2, .kop h3 {
            margin: 0;
        }
        .isi {
            margin-top: 20px;
            line-height: 1.8;
        }
        .ttd {
            margin-top: 50px;
            width: 100%;
            text-align: right;
        }
    </style>
</head>
<body onload="window.print()">

<div class="kop">
    <h2>PEMERINTAH KECAMATAN SIMPANG KIRI</h2>
    <h3>SURAT PENGANTAR</h3>
    <hr>
    <p>Nomor: <?= sprintf("SP-%04d/%s", $data['id'], date("Y")) ?></p>
</div>

<div class="isi">
    <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
    <table>
        <tr><td>Nama</td><td>:</td><td><?= $data['nama_lengkap'] ?></td></tr>
        <tr><td>NIK</td><td>:</td><td><?= $data['nik'] ?></td></tr>
        <tr><td>Alamat</td><td>:</td><td><?= $data['alamat'] ?></td></tr>
    </table>

    <p>
        Dengan ini diberikan <b>surat pengantar</b> untuk keperluan:  
        <i><?= $data['keperluan'] ?></i>.
    </p>

    <p>
        Demikian surat pengantar ini dibuat untuk dapat digunakan sebagaimana mestinya.
    </p>
</div>

<div class="ttd">
    <p>Simpang Kiri, <?= date("d F Y", strtotime($data['tanggal_selesai'])) ?></p>
    <p><b>Kepala Desa / Lurah</b></p>
    <br><br><br>
    <p><u>________________________</u></p>
</div>

</body>
</html>
