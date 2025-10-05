<?php
session_start();
require_once 'config.php'; // koneksi database

$error = '';
$success = '';

// ===================== Tambah Admin =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');

    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = "Semua field harus diisi!";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username '$username' sudah ada!";
        } else {
            mysqli_stmt_close($stmt);

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role = 'admin';
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $role, $nama_lengkap);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Admin '$username' berhasil dibuat!";
            } else {
                $error = "Gagal membuat admin: " . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// ===================== Hapus Admin =====================
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role = 'admin'");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Admin berhasil dihapus!";
    } else {
        $error = "Gagal hapus admin: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// ===================== Ambil Semua Admin =====================
$admins = mysqli_query($conn, "SELECT id, username, nama_lengkap FROM users WHERE role = 'admin'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Admin</title>
</head>
<body>
    <h2>Tambah Admin Baru</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="tambah_admin" value="1">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Nama Lengkap:</label><br>
        <input type="text" name="nama_lengkap" required><br><br>

        <button type="submit">Tambah Admin</button>
    </form>

    <hr>

    <h2>Daftar Admin</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Nama Lengkap</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($admins)): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
            <td>
                <a href="?hapus=<?= $row['id']; ?>" 
                   onclick="return confirm('Yakin mau hapus admin ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
