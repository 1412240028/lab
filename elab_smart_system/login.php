<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $plainPassword = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "
        SELECT id_user, nama, email, role, password
        FROM users
        WHERE email=?
        LIMIT 1
    ");

    if (!$stmt) {
        $error = "Gagal menyiapkan query: " . mysqli_error($conn);
    } else {
        if (!mysqli_stmt_bind_param($stmt, "s", $email)) {
            $error = "Gagal bind param: " . mysqli_stmt_error($stmt);
        } else {
            if (!mysqli_stmt_execute($stmt)) {
                $error = "Gagal execute query: " . mysqli_stmt_error($stmt);
            } else {
                $result = mysqli_stmt_get_result($stmt);
                $data = mysqli_fetch_assoc($result);

                if ($data) {
                    $storedHash = $data['password'];
                    $isValid = password_verify($plainPassword, $storedHash);

                    if (!$isValid) {
                        $isValid = hash_equals(md5($plainPassword), $storedHash);

                        if ($isValid) {
                            $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
                            $upd = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id_user=?");

                            if ($upd) {
                                mysqli_stmt_bind_param($upd, "si", $newHash, $data['id_user']);
                                mysqli_stmt_execute($upd);
                            }
                        }
                    }

                    if ($isValid) {
                        session_regenerate_id(true);

                        $_SESSION['id_user'] = $data['id_user'];
                        $_SESSION['nama'] = $data['nama'];
                        $_SESSION['role'] = $data['role'];

                        if ($data['role'] === 'admin') {
                            header("Location: admin/dashboard.php");
                            exit;
                        }

                        if ($data['role'] === 'mahasiswa') {
                            header("Location: mahasiswa/dashboard.php");
                            exit;
                        }

                        $error = "Role tidak valid. Hubungi administrator.";
                    } else {
                        $error = "Email atau password salah";
                    }
                } else {
                    $error = "Email atau password salah";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Login - E-Lab Smart System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="assets/css/elab-ui.css">
</head>

<body>

    <main class="elab-page">
        <section class="elab-phone">

            <div class="auth-hero">
                <div class="auth-logo">
                    <img src="assets/images/E-Lab System Logo.jpg" alt="E-Lab Smart System Logo">
                </div>
                <h1>E-Lab Smart System</h1>
                <p>Sistem manajemen peminjaman laboratorium</p>
            </div>

            <div class="auth-card">

                <div class="auth-tabs">
                    <a href="login.php" class="auth-tab active">Masuk</a>
                    <a href="register.php" class="auth-tab">Daftar</a>
                </div>

                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger mb-3">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php } ?>

                <form method="POST" autocomplete="off">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan email akun" required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password"
                            required>
                    </div>

                    <div class="text-end mb-4">
                        <span style="font-size:13px; color:#6b7280;">Lupa password? Hubungi Admin Lab</span>
                    </div>

                    <button type="submit" name="login" class="btn-elab btn-primary-elab w-100">
                        Masuk ke Sistem →
                    </button>

                    <div class="divider">atau masuk sebagai</div>

                    <div class="role-grid">
                        <div class="role-chip student">Mahasiswa</div>
                        <div class="role-chip lecturer">Dosen</div>
                        <div class="role-chip admin">Admin</div>
                    </div>

                    <p class="auth-helper">
                        Belum punya akun?
                        <a href="register.php">Daftar sekarang</a>
                    </p>

                </form>

            </div>

        </section>
    </main>

</body>

</html>