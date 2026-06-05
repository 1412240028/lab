<?php
session_start();
require_once("_guard.php");
require_once "../koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// Hitung notifikasi belum dibaca sebelum ditandai read
$stmtCountBefore = mysqli_prepare($conn, "
    SELECT COUNT(*) as total FROM peminjaman
    WHERE id_user=? AND dibaca=0 AND status != 'menunggu'
");
mysqli_stmt_bind_param($stmtCountBefore, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmtCountBefore);
$countBefore = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCountBefore));
$unreadBefore = $countBefore['total'];

// Ambil semua notifikasi milik user
$stmt = mysqli_prepare($conn, "
    SELECT peminjaman.*, laboratorium.nama_lab
    FROM peminjaman
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.id_user = ?
    AND peminjaman.status != 'menunggu'
    ORDER BY peminjaman.tanggal_pinjam DESC
");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmt);
$notif = mysqli_stmt_get_result($stmt);

// Statistik notifikasi
$idUser = (int) $_SESSION['id_user'];

$stmtDisetujui = mysqli_prepare($conn, "
    SELECT COUNT(*) as total FROM peminjaman
    WHERE id_user=? AND status='disetujui'
");
mysqli_stmt_bind_param($stmtDisetujui, "i", $idUser);
mysqli_stmt_execute($stmtDisetujui);
$totalDisetujui = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDisetujui))['total'];

$stmtDitolak = mysqli_prepare($conn, "
    SELECT COUNT(*) as total FROM peminjaman
    WHERE id_user=? AND status='ditolak'
");
mysqli_stmt_bind_param($stmtDitolak, "i", $idUser);
mysqli_stmt_execute($stmtDitolak);
$totalDitolak = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDitolak))['total'];

$totalNotif = $totalDisetujui + $totalDitolak;

// Tandai semua sudah dibaca setelah data diambil
$markRead = mysqli_prepare($conn, "
    UPDATE peminjaman SET dibaca=1
    WHERE id_user=? AND dibaca=0
");
mysqli_stmt_bind_param($markRead, "i", $_SESSION['id_user']);
mysqli_stmt_execute($markRead);

$namaMahasiswa = $_SESSION['nama'];
$inisial = strtoupper(substr($namaMahasiswa, 0, 2));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Notifikasi - E-Lab Smart System</title>
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
                        <h1 class="app-title">Notifikasi</h1>
                        <p class="app-subtitle">
                            <?= htmlspecialchars($namaMahasiswa) ?> • Update status peminjaman
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
                <div class="section-label">Ringkasan Notifikasi</div>

                <div class="stat-grid">
                    <div class="stat-box">
                        <div class="stat-number primary"><?= htmlspecialchars($totalNotif) ?></div>
                        <div class="stat-text">Total notifikasi</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number success"><?= htmlspecialchars($totalDisetujui) ?></div>
                        <div class="stat-text">Disetujui</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number purple"><?= htmlspecialchars($totalDitolak) ?></div>
                        <div class="stat-text">Ditolak</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number warning"><?= htmlspecialchars($unreadBefore) ?></div>
                        <div class="stat-text">Belum dibaca</div>
                    </div>
                </div>

                <div class="notification-info-box mt-4">
                    <h2>Pusat Notifikasi</h2>
                    <p>
                        Semua keputusan admin terkait pengajuan peminjaman akan tampil di sini. Notifikasi otomatis ditandai sudah dibaca setelah halaman ini dibuka.
                    </p>
                </div>

                <div class="section-label">Daftar Notifikasi</div>

                <?php if (mysqli_num_rows($notif) == 0) { ?>
                    <div class="empty-state">
                        Belum ada notifikasi. Nanti kalau pengajuan kamu disetujui atau ditolak, kabarnya muncul di sini.
                    </div>
                <?php } ?>

                <div class="notification-list">
                    <?php while ($n = mysqli_fetch_assoc($notif)) { ?>
                        <?php
                        $isApproved = $n['status'] == 'disetujui';
                        $isUnread = $n['dibaca'] == 0;
                        ?>

                        <div class="notification-card-modern <?= $isUnread ? 'unread' : '' ?>">
                            <div class="notification-top">
                                <div class="notification-main">
                                    <div class="notification-icon <?= $isApproved ? 'approved' : 'rejected' ?>">
                                        <?= $isApproved ? '✓' : '!' ?>
                                    </div>

                                    <div>
                                        <h2 class="notification-title">
                                            Peminjaman <?= htmlspecialchars($n['nama_lab']) ?>
                                            <?= $isApproved ? 'disetujui' : 'ditolak' ?>
                                        </h2>

                                        <p class="notification-meta">
                                            📅 <?= htmlspecialchars($n['tanggal_pinjam']) ?><br>
                                            🕐 <?= htmlspecialchars($n['jam_mulai']) ?> - <?= htmlspecialchars($n['jam_selesai']) ?>
                                        </p>

                                        <?php if ($isUnread) { ?>
                                            <span class="unread-label">Baru</span>
                                        <?php } ?>
                                    </div>
                                </div>

                                <span class="badge-notification <?= htmlspecialchars($n['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($n['status'])) ?>
                                </span>
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