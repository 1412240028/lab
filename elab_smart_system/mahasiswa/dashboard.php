<?php
session_start();
include '../koneksi.php';

// Cek session
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$lab = mysqli_query($conn, "
    SELECT * FROM laboratorium
    WHERE status='tersedia'
    ORDER BY nama_lab ASC
");

$quickLab = mysqli_query($conn, "
    SELECT * FROM laboratorium
    WHERE status='tersedia'
    ORDER BY nama_lab ASC
    LIMIT 3
");

$riwayat = mysqli_prepare($conn, "
    SELECT peminjaman.*, laboratorium.nama_lab
    FROM peminjaman
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.id_user = ?
    ORDER BY peminjaman.tanggal_pinjam DESC
    LIMIT 5
");
mysqli_stmt_bind_param($riwayat, "i", $_SESSION['id_user']);
mysqli_stmt_execute($riwayat);
$riwayat = mysqli_stmt_get_result($riwayat);

// Statistik mahasiswa
$idUser = (int) $_SESSION['id_user'];

$stmtAktif = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=? AND status='menunggu'
");
mysqli_stmt_bind_param($stmtAktif, "i", $idUser);
mysqli_stmt_execute($stmtAktif);
$totalAktif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAktif))['total'];

$stmtSetuju = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=? AND status='disetujui'
");
mysqli_stmt_bind_param($stmtSetuju, "i", $idUser);
mysqli_stmt_execute($stmtSetuju);
$totalSetuju = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSetuju))['total'];

$stmtRiwayat = mysqli_prepare($conn, "
    SELECT COUNT(*) as total 
    FROM peminjaman 
    WHERE id_user=?
");
mysqli_stmt_bind_param($stmtRiwayat, "i", $idUser);
mysqli_stmt_execute($stmtRiwayat);
$totalRiwayat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtRiwayat))['total'];

$totalLabTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tersedia'"));

$namaMahasiswa = $_SESSION['nama'];
$inisial = strtoupper(substr($namaMahasiswa, 0, 2));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Dashboard Mahasiswa - E-Lab Smart System</title>
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
            <header class="app-header student">
                <div class="app-header-content d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="app-title">Halo, <?= htmlspecialchars($namaMahasiswa) ?></h1>
                        <p class="app-subtitle">
                            Mahasiswa • Teknik Informatika
                        </p>
                        <a href="../logout.php" class="app-logout">Keluar dari sistem</a>
                    </div>

                    <div class="profile-circle student">
                        <?= htmlspecialchars($inisial) ?>
                    </div>
                </div>
            </header>

            <div class="app-body">

                <!-- Ringkasan -->
                <div class="section-label">Ringkasan Saya</div>

                <div class="stat-grid">
                    <div class="stat-box">
                        <div class="stat-number primary"><?= $totalAktif ?></div>
                        <div class="stat-text">Menunggu</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number success"><?= $totalSetuju ?></div>
                        <div class="stat-text">Disetujui</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number purple"><?= $totalRiwayat ?></div>
                        <div class="stat-text">Total pengajuan</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number warning"><?= $totalLabTersedia ?></div>
                        <div class="stat-text">Lab tersedia</div>
                    </div>
                </div>

                <div class="student-layout mt-4">

                    <!-- Form Peminjaman -->
                    <div>
                        <div class="student-hero-card">
                            <h2>Ajukan Peminjaman Lab</h2>
                            <p>
                                Pilih laboratorium, tanggal, jam, dan keperluan. Sistem akan mengecek bentrok jadwal saat pengajuan dikirim.
                            </p>
                        </div>

                        <div class="loan-form-panel">
                            <h2 class="panel-title">Form Peminjaman</h2>
                            <p class="panel-desc">
                                Isi data peminjaman dengan lengkap agar admin bisa melakukan review.
                            </p>

                            <form method="POST" action="simpan.php">

                                <div class="input-group-modern">
                                    <label>Laboratorium</label>
                                    <select name="id_lab" class="form-select" required>
                                        <option value="">Pilih laboratorium</option>
                                        <?php while ($d = mysqli_fetch_assoc($lab)) { ?>
                                            <option value="<?= htmlspecialchars($d['id_lab']) ?>">
                                                <?= htmlspecialchars($d['nama_lab']) ?> • <?= htmlspecialchars($d['kapasitas']) ?> kursi
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="input-group-modern">
                                    <label>Tanggal Pinjam</label>
                                    <input
                                        type="date"
                                        name="tanggal_pinjam"
                                        class="form-control"
                                        required>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group-modern">
                                            <label>Jam Mulai</label>
                                            <input
                                                type="time"
                                                name="jam_mulai"
                                                class="form-control"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="input-group-modern">
                                            <label>Jam Selesai</label>
                                            <input
                                                type="time"
                                                name="jam_selesai"
                                                class="form-control"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <div class="input-group-modern">
                                    <label>Keperluan</label>
                                    <textarea
                                        name="keperluan"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Contoh: Praktikum pemrograman web"
                                        required></textarea>
                                </div>

                                <button class="btn student-cta w-100">
                                    + Ajukan Peminjaman
                                </button>

                            </form>
                        </div>
                    </div>

                    <!-- Sisi Kanan -->
                    <div>

                        <!-- Lab tersedia -->
                        <div class="section-label mt-0">Lab Tersedia</div>

                        <div class="quick-lab-list mb-4">
                            <?php if (mysqli_num_rows($quickLab) == 0) { ?>
                                <div class="empty-state">
                                    Belum ada laboratorium yang tersedia saat ini.
                                </div>
                            <?php } ?>

                            <?php while ($q = mysqli_fetch_assoc($quickLab)) { ?>
                                <div class="quick-lab-item">
                                    <div class="quick-lab-icon"></div>
                                    <div>
                                        <h3><?= htmlspecialchars($q['nama_lab']) ?></h3>
                                        <p>
                                            <?= htmlspecialchars($q['lokasi']) ?> • <?= htmlspecialchars($q['kapasitas']) ?> kursi
                                        </p>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Riwayat -->
                        <div class="section-label">Status Peminjaman Terbaru</div>

                        <?php if (mysqli_num_rows($riwayat) == 0) { ?>
                            <div class="empty-state">
                                Belum ada riwayat peminjaman. Ajukan peminjaman pertama lu di form sebelah.
                            </div>
                        <?php } ?>

                        <div class="student-history-list">
                            <?php while ($r = mysqli_fetch_assoc($riwayat)) { ?>
                                <div class="student-history-card">
                                    <div class="student-history-top">
                                        <div>
                                            <h3 class="history-lab-name">
                                                <?= htmlspecialchars($r['nama_lab']) ?>
                                            </h3>

                                            <p class="history-meta">
                                                📅 <?= htmlspecialchars($r['tanggal_pinjam']) ?><br>
                                                🕐 <?= htmlspecialchars($r['jam_mulai']) ?> - <?= htmlspecialchars($r['jam_selesai']) ?>
                                            </p>
                                        </div>

                                        <span class="badge-loan <?= htmlspecialchars($r['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($r['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Bottom Nav -->
            <nav class="bottom-nav-modern bottom-nav-student">
                <a href="dashboard.php" class="active">Beranda</a>
                <a href="riwayat.php">Riwayat</a>
                <a href="notifikasi.php">Notifikasi</a>
                <a href="profil.php">Profil</a>
                <a href="../logout.php">Logout</a>
            </nav>

        </section>
    </main>

</body>

</html>