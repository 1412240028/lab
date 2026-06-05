<?php
session_start();
require_once("_guard.php");
require_once "../koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// Ambil data user
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id_user=?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$inisial = strtoupper(substr($user['nama'], 0, 2));

$idUser = (int) $_SESSION['id_user'];

$stmtTotal = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=?
");
mysqli_stmt_bind_param($stmtTotal, "i", $idUser);
mysqli_stmt_execute($stmtTotal);
$totalPeminjaman = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];

$stmtSetuju = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=? AND status='disetujui'
");
mysqli_stmt_bind_param($stmtSetuju, "i", $idUser);
mysqli_stmt_execute($stmtSetuju);
$totalDisetujui = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSetuju))['total'];

$stmtMenunggu = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=? AND status='menunggu'
");
mysqli_stmt_bind_param($stmtMenunggu, "i", $idUser);
mysqli_stmt_execute($stmtMenunggu);
$totalMenunggu = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMenunggu))['total'];

$stmtDitolak = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=? AND status='ditolak'
");
mysqli_stmt_bind_param($stmtDitolak, "i", $idUser);
mysqli_stmt_execute($stmtDitolak);
$totalDitolak = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDitolak))['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Profil - E-Lab Smart System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body>

    <main class="app-shell">
        <section class="app-container">

            <!-- Header -->
            <header class="app-header student profile-header-card">
                <div class="app-header-content">
                    <div class="profile-avatar-large">
                        <?= htmlspecialchars($inisial) ?>
                    </div>

                    <h1 class="profile-name-title">
                        <?= htmlspecialchars($user['nama']) ?>
                    </h1>

                    <p class="profile-email-subtitle">
                        <?= htmlspecialchars($user['email']) ?><br>
                        <?= htmlspecialchars(ucfirst($user['role'])) ?> • E-Lab Smart System
                    </p>

                    <a href="../logout.php" class="app-logout justify-content-center">
                        Keluar dari sistem
                    </a>
                </div>
            </header>

            <div class="app-body">

                <!-- Ringkasan -->
                <div class="section-label">Ringkasan Akun</div>

                <div class="stat-grid">
                    <div class="stat-box">
                        <div class="stat-number primary"><?= htmlspecialchars($totalPeminjaman) ?></div>
                        <div class="stat-text">Total pengajuan</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number success"><?= htmlspecialchars($totalDisetujui) ?></div>
                        <div class="stat-text">Disetujui</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number warning"><?= htmlspecialchars($totalMenunggu) ?></div>
                        <div class="stat-text">Menunggu</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number purple"><?= htmlspecialchars($totalDitolak) ?></div>
                        <div class="stat-text">Ditolak</div>
                    </div>
                </div>

                <div class="profile-layout mt-4">

                    <!-- Detail Akun -->
                    <div>
                        <div class="profile-info-box">
                            <h2>Informasi Profil</h2>
                            <p>
                                Data akun digunakan untuk identitas peminjam di E-Lab Smart System. Ubah nama atau password jika diperlukan.
                            </p>
                        </div>

                        <div class="profile-detail-card mt-3">
                            <h2 class="panel-title">Detail Akun</h2>
                            <p class="panel-desc">
                                Ringkasan informasi akun yang sedang aktif.
                            </p>

                            <div class="profile-detail-list">
                                <div class="profile-detail-item">
                                    <span class="profile-detail-label">Nama</span>
                                    <span class="profile-detail-value">
                                        <?= htmlspecialchars($user['nama']) ?>
                                    </span>
                                </div>

                                <div class="profile-detail-item">
                                    <span class="profile-detail-label">Email</span>
                                    <span class="profile-detail-value">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </span>
                                </div>

                                <div class="profile-detail-item">
                                    <span class="profile-detail-label">Role</span>
                                    <span class="profile-detail-value">
                                        <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                    </span>
                                </div>

                                <div class="profile-detail-item">
                                    <span class="profile-detail-label">ID User</span>
                                    <span class="profile-detail-value">
                                        #<?= htmlspecialchars($user['id_user']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit Profil -->
                    <div class="profile-form-panel">
                        <h2 class="panel-title">Edit Profil</h2>
                        <p class="panel-desc">
                            Masukkan password lama untuk menyimpan perubahan profil.
                        </p>

                        <div class="password-note">
                            Password baru boleh dikosongkan kalau kamu hanya ingin mengganti nama.
                        </div>

                        <form method="POST" action="profil_proses.php">

                            <div class="input-group-modern">
                                <label>Nama Lengkap</label>
                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user['nama']) ?>"
                                    required>
                            </div>

                            <div class="input-group-modern">
                                <label>Password Lama</label>
                                <input
                                    type="password"
                                    name="password_lama"
                                    class="form-control"
                                    placeholder="Masukkan password lama"
                                    required>
                            </div>

                            <div class="input-group-modern">
                                <label>Password Baru</label>
                                <input
                                    type="password"
                                    name="password_baru"
                                    class="form-control"
                                    placeholder="Kosongkan jika tidak ingin ganti">
                            </div>

                            <button class="btn profile-save-btn w-100">
                                Simpan Perubahan
                            </button>

                        </form>
                    </div>

                </div>

            </div>

            <?php require_once "_nav.php"; ?>
 
        </section>
    </main>
   
</body>

</html>