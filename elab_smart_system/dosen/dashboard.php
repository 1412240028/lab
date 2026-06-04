<?php
require_once "_guard.php";
require_once "../koneksi.php";

$nama = $_SESSION['nama'] ?? 'Dosen';

$totalJadwal = 0;
$totalMenunggu = 0;
$totalDisetujui = 0;

$queryJadwal = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE status = 'disetujui'
");
if ($queryJadwal) {
    $row = mysqli_fetch_assoc($queryJadwal);
    $totalJadwal = $row['total'] ?? 0;
}

$queryMenunggu = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE status = 'menunggu'
");
if ($queryMenunggu) {
    $row = mysqli_fetch_assoc($queryMenunggu);
    $totalMenunggu = $row['total'] ?? 0;
}

$queryDisetujui = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE status = 'disetujui'
");
if ($queryDisetujui) {
    $row = mysqli_fetch_assoc($queryDisetujui);
    $totalDisetujui = $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dosen - E-Lab Smart System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="dosen-page">

<div class="app-shell">
    <div class="app-container dosen-container">

        <header class="dosen-header">
            <div>
                <p class="dosen-header-label">Panel Dosen</p>
                <h1>Dashboard Dosen</h1>
                <p class="dosen-header-subtitle">
                    Selamat datang, <?= htmlspecialchars($nama); ?>. Pantau jadwal lab dan verifikasi peminjaman mahasiswa.
                </p>
            </div>

            <div class="dosen-avatar">
                <?= strtoupper(substr($nama, 0, 1)); ?>
            </div>
        </header>

        <main class="app-body dosen-body">

            <section class="lecturer-hero-card">
                <h2>Kontrol Akademik Laboratorium</h2>
                <p>
                    Kelola aktivitas akademik laboratorium dengan lebih rapi melalui jadwal penggunaan dan verifikasi peminjaman.
                </p>
            </section>

            <section class="dosen-stat-grid">
                <div class="dosen-stat-card">
                    <span>Total Jadwal</span>
                    <strong><?= htmlspecialchars($totalJadwal); ?></strong>
                </div>

                <div class="dosen-stat-card">
                    <span>Menunggu Verifikasi</span>
                    <strong><?= htmlspecialchars($totalMenunggu); ?></strong>
                </div>

                <div class="dosen-stat-card">
                    <span>Disetujui</span>
                    <strong><?= htmlspecialchars($totalDisetujui); ?></strong>
                </div>
            </section>

            <section class="lecturer-layout">
                <div class="lecturer-quick-panel">
                    <h2>Ringkasan Tugas</h2>
                    <p>
                        Fokus utama dosen adalah mengecek jadwal lab dan memverifikasi pengajuan peminjaman mahasiswa.
                    </p>
                </div>

                <div class="lecturer-quick-panel">
                    <h2>Akses Cepat</h2>

                    <div class="lecturer-action-row">
                        <a href="jadwal.php" class="btn-elab btn-primary-elab">Lihat Jadwal</a>
                        <a href="verifikasi.php" class="btn-elab btn-purple-elab">Verifikasi</a>
                    </div>
                </div>
            </section>

        </main>

        <?php require_once "_nav.php"; ?>

    </div>
</div>

</body>
</html>