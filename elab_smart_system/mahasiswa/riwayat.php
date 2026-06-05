<?php
session_start();
require_once "_guard.php";
require_once "../koneksi.php";

// Cek session
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$stmt = mysqli_prepare($conn, "
    SELECT peminjaman.*, laboratorium.nama_lab
    FROM peminjaman
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.id_user = ?
    ORDER BY peminjaman.tanggal_pinjam DESC
");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmt);
$riwayat = mysqli_stmt_get_result($stmt);

$idUser = (int) $_SESSION['id_user'];

$stmtTotal = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user=?");
mysqli_stmt_bind_param($stmtTotal, "i", $idUser);
mysqli_stmt_execute($stmtTotal);
$totalRiwayat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];

$stmtMenunggu = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user=? AND status='menunggu'");
mysqli_stmt_bind_param($stmtMenunggu, "i", $idUser);
mysqli_stmt_execute($stmtMenunggu);
$totalMenunggu = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMenunggu))['total'];

$stmtSetuju = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user=? AND status='disetujui'");
mysqli_stmt_bind_param($stmtSetuju, "i", $idUser);
mysqli_stmt_execute($stmtSetuju);
$totalSetuju = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSetuju))['total'];

$stmtDitolak = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user=? AND status='ditolak'");
mysqli_stmt_bind_param($stmtDitolak, "i", $idUser);
mysqli_stmt_execute($stmtDitolak);
$totalDitolak = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDitolak))['total'];

$namaMahasiswa = $_SESSION['nama'];
$inisial = strtoupper(substr($namaMahasiswa, 0, 2));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Riwayat Peminjaman - E-Lab Smart System</title>
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
                        <h1 class="app-title">Riwayat Peminjaman</h1>
                        <p class="app-subtitle">
                            <?= htmlspecialchars($namaMahasiswa) ?> • Data pengajuan laboratorium
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
                <div class="section-label">Ringkasan Riwayat</div>

                <div class="stat-grid">
                    <div class="stat-box">
                        <div class="stat-number primary"><?= $totalRiwayat ?></div>
                        <div class="stat-text">Total pengajuan</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number warning"><?= $totalMenunggu ?></div>
                        <div class="stat-text">Menunggu</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number success"><?= $totalSetuju ?></div>
                        <div class="stat-text">Disetujui</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number purple"><?= $totalDitolak ?></div>
                        <div class="stat-text">Ditolak</div>
                    </div>
                </div>

                <div class="history-page-info mt-4">
                    <h2>Catatan Peminjaman</h2>
                    <p>
                        Semua pengajuan yang pernah kamu kirim akan muncul di sini. Surat peminjaman hanya bisa dicetak jika pengajuan sudah disetujui admin.
                    </p>
                </div>

                <div class="section-label">Daftar Riwayat</div>

                <?php if (mysqli_num_rows($riwayat) == 0) { ?>
                    <div class="empty-state">
                        Belum ada riwayat peminjaman. Ajukan peminjaman pertama dari halaman Beranda.
                    </div>
                <?php } ?>

                <div class="history-page-list">
                    <?php while ($r = mysqli_fetch_assoc($riwayat)) { ?>
                        <div class="history-page-card">
                            <div class="history-page-top">
                                <div>
                                    <h2 class="history-page-title">
                                        <?= htmlspecialchars($r['nama_lab']) ?>
                                    </h2>

                                    <p class="history-page-meta">
                                        📅 <?= htmlspecialchars($r['tanggal_pinjam']) ?><br>
                                        🕐 <?= htmlspecialchars($r['jam_mulai']) ?> - <?= htmlspecialchars($r['jam_selesai']) ?><br>
                                        📝 <?= htmlspecialchars($r['keperluan']) ?>
                                    </p>

                                    <?php if ($r['status'] == 'disetujui') { ?>
                                        <a href="cetak_surat.php?id=<?= htmlspecialchars($r['id_peminjaman']) ?>" class="print-letter-btn">
                                            Cetak Surat
                                        </a>
                                    <?php } ?>
                                </div>

                                <div class="history-status-wrap">
                                    <span class="badge-loan <?= htmlspecialchars($r['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($r['status'])) ?>
                                    </span>

                                    <span class="history-date-chip">
                                        #<?= htmlspecialchars($r['id_peminjaman']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            </div>

            <?php require_once "_nav.php"; ?>

        </section>
    </main>

</body>

</html>