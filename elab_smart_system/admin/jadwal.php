<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM laboratorium");
$totalLab = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium"));
$labTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tersedia'"));
$labTidakTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tidak tersedia'"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Jadwal Laboratorium - E-Lab Smart System</title>
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
            <header class="app-header admin">
                <div class="app-header-content">
                    <h1 class="app-title">Jadwal Laboratorium</h1>
                    <p class="app-subtitle">
                        <?= htmlspecialchars($_SESSION['nama']) ?> • Admin Laboratorium
                    </p>
                    <a href="../logout.php" class="app-logout">Keluar dari sistem</a>
                </div>
            </header>

            <div class="app-body">

                <!-- Summary -->
                <div class="lab-summary-box">
                    <h2 class="lab-summary-title">Monitoring Status Lab</h2>
                    <p class="lab-summary-text">
                        Pantau kondisi laboratorium berdasarkan status ketersediaan, lokasi, dan kapasitas ruang.
                    </p>
                </div>

                <!-- Statistik Mini -->
                <div class="stat-grid">
                    <div class="stat-box admin">
                        <div class="stat-number purple"><?= $totalLab ?></div>
                        <div class="stat-text">Total lab</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number success"><?= $labTersedia ?></div>
                        <div class="stat-text">Tersedia</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number warning"><?= $labTidakTersedia ?></div>
                        <div class="stat-text">Tidak tersedia</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number primary"><?= $labTersedia ?></div>
                        <div class="stat-text">Siap dipakai</div>
                    </div>
                </div>

                <div class="section-label">Daftar Jadwal Lab</div>

                <?php if (mysqli_num_rows($data) == 0) { ?>
                    <div class="empty-state">
                        Tidak ada data laboratorium. Tambahkan lab dulu dari menu Kelola.
                    </div>
                <?php } ?>

                <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                    <?php
                    $isAvailable = $d['status'] == 'tersedia';
                    ?>

                    <div class="lab-card-modern">
                        <div class="lab-card-top">
                            <div>
                                <h2 class="lab-card-title">
                                    <?php if ($isAvailable) { ?>
                                        <span class="status-dot green"></span>
                                    <?php } else { ?>
                                        <span class="status-dot red"></span>
                                    <?php } ?>

                                    <?= htmlspecialchars($d['nama_lab']) ?>
                                </h2>

                                <div class="lab-card-meta">
                                    <div class="meta-line">
                                        📍 <?= htmlspecialchars($d['lokasi']) ?>
                                    </div>

                                    <div class="meta-line">
                                        💺 <?= htmlspecialchars($d['kapasitas']) ?> kursi
                                    </div>
                                </div>
                            </div>

                            <span class="badge-status <?= $isAvailable ? 'available' : 'unavailable' ?>">
                                <?= $isAvailable ? 'Tersedia' : 'Tidak tersedia' ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>

            </div>

            <!-- Bottom Nav -->
            <nav class="bottom-nav-modern">
                <a href="dashboard.php">Beranda</a>
                <a href="jadwal.php" class="active">Jadwal</a>
                <a href="laporan.php">Laporan</a>
                <a href="kelola.php">Kelola</a>
                <a href="kelola_user.php">User</a>
                <a href="../logout.php">Logout</a>
            </nav>

        </section>
    </main>

</body>

</html>