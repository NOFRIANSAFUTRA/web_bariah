<?php
$host = "localhost";      // ganti jika bukan localhost
$user = "root";           // sesuaikan dengan user database kamu
$pass = "";               // password MySQL
$dbname = "db_kecamatan"; // nama database

$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = htmlspecialchars($_POST["nama"]);
    $layanan = htmlspecialchars($_POST["layanan"]);
    $pesan = htmlspecialchars($_POST["pesan"]);

    $stmt = $conn->prepare("INSERT INTO pengajuan_layanan (nama, layanan, pesan) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $layanan, $pesan);

    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Formulir Terkirim</title>
            <style>
                body { font-family: Arial; background: #f0f0f0; text-align: center; padding: 40px; }
                .box { background: #fff; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                a { text-decoration: none; color: white; background: #007bff; padding: 10px 20px; border-radius: 5px; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='box'>
                <h2>Terima kasih, $nama!</h2>
                <p>Pengajuan untuk <strong>$layanan</strong> telah disimpan dan menunggu verifikasi.</p>
                <a href='index.html'>Kembali ke Beranda</a>
            </div>
        </body>
        </html>";
    } else {
        echo "Terjadi kesalahan saat menyimpan data: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.html");
    exit();
}
?>
