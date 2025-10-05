<?php
require_once 'config.php';

// Inisialisasi variabel pesan
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);

    // Validasi input
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($no_hp)) {
        $error = "Username, password, nama lengkap, dan nomor HP wajib diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif (!empty($nik) && !preg_match('/^[0-9]{16}$/', $nik)) {
        $error = "NIK harus terdiri dari 16 digit angka!";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $error = "Nomor HP harus terdiri dari 10–15 digit angka!";
    } else {
        // Cek ketersediaan username
        $check_query = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert data ke database dengan prepared statement
            $insert_query = "INSERT INTO users (username, password, role, nama_lengkap, nik, alamat, no_hp) 
                           VALUES (?, ?, 'masyarakat', ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "ssssss", $username, $hashed_password, $nama_lengkap, $nik, $alamat, $no_hp);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Pendaftaran berhasil! Anda akan dialihkan ke halaman login.";
                header("refresh:2;url=login.php");
            } else {
                $error = "Pendaftaran gagal: " . mysqli_error($conn);
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
    <title>Register - Kecamatan Simpang Kiri</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Main Container */
        .form-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Header Form */
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #7f8c8d;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Button Styles */
        .btn {
            display: block;
            width: 100%;
            padding: 0.8rem;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Footer Form */
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .form-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .form-container {
                margin: 2rem auto;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <main>
        <div class="form-container">
            <div class="form-header">
                <h2>Pendaftaran Akun Masyarakat</h2>
                <p>Isi formulir berikut untuk membuat akun baru</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control"
                        value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="nik">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" id="nik" name="nik" class="form-control"
                        value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>"
                        maxlength="16" pattern="[0-9]{16}" title="16 digit angka">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" class="form-control"><?php
                                                                                echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '';
                                                                                ?></textarea>
                </div>
                <div class="form-group">
                    <label for="no_hp">Nomor HP (WhatsApp)</label>
                    <input type="text" id="no_hp" name="no_hp" class="form-control"
                        value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>"
                        required pattern="[0-9]{10,15}" title="Nomor HP hanya angka, 10–15 digit">
                </div>


                <button type="submit" class="btn">Daftar</button>
            </form>

            <div class="form-footer">
                Sudah punya akun? <a href="login.php">Login disini</a>
            </div>
        </div>
    </main>


</body>

</html>