<?php
session_start();

if (isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if (isset($_POST['daftar'])) {
    $nama      = trim($_POST['nama']);
    $nim       = trim($_POST['nim']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi']);

    if ($password !== $konfirmasi) {
        $error = "Password dan konfirmasi tidak cocok";
    } else {
        $cek = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? OR nim=?");
        mysqli_stmt_bind_param($cek, "ss", $email, $nim);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            $error = "Email atau NIM sudah terdaftar";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "
                INSERT INTO users(nama, nim, email, password, role)
                VALUES(?, ?, ?, ?, 'mahasiswa')
            ");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $nim, $email, $hash);
            mysqli_stmt_execute($stmt);

            $success = "Registrasi berhasil! Silakan login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Registrasi - E-Lab Smart System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>

    <main class="elab-page">
        <section class="elab-phone">

            <div class="auth-hero">
                <div class="auth-logo">
                    <img src="assets/images/E-Lab System Logo.jpg" alt="E-Lab Smart System Logo">
                </div>
                <h1>Buat Akun Baru</h1>
                <p>Daftar sebagai mahasiswa untuk mengajukan peminjaman lab</p>
            </div>

            <div class="auth-card">

                <div class="auth-tabs">
                    <a href="login.php" class="auth-tab">Masuk</a>
                    <a href="register.php" class="auth-tab active">Daftar</a>
                </div>

                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger mb-3">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php } ?>

                <?php if (isset($success)) { ?>
                    <div class="alert alert-success mb-3">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php } ?>

                <form method="POST" autocomplete="off">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required
                            value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" name="nim" class="form-control" placeholder="Masukkan NIM kamu" required
                            value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan email aktif"
                            required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Buat password"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password"
                            required>
                    </div>

                    <button type="submit" name="daftar" class="btn-elab btn-primary-elab w-100">
                        Daftar Akun →
                    </button>

                    <p class="auth-helper">
                        Sudah punya akun?
                        <a href="login.php">Masuk sekarang</a>
                    </p>

                </form>

            </div>

        </section>
    </main>

</body>

</html>