<?php
session_start();
require_once "_guard.php";
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$filter_id_lab = isset($_GET['id_lab']) ? (int) $_GET['id_lab'] : 0;

$semuaLab = mysqli_query($conn, "SELECT * FROM laboratorium ORDER BY nama_lab ASC");

if ($filter_id_lab > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM laboratorium WHERE id_lab = ?");
    mysqli_stmt_bind_param($stmt, "i", $filter_id_lab);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($conn, "SELECT * FROM laboratorium");
}

$totalLab = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium"));
$labTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tersedia'"));
$labTidakTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tidak tersedia'"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Daftar Laboratorium - E-Lab Smart System</title>
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
                    <h1 class="app-title">Status Laboratorium</h1>
                    <p class="app-subtitle">
                        <?= htmlspecialchars($_SESSION['nama']) ?> • Monitoring status dan ketersediaan lab
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

                <!-- TAMBAHAN: Filter by lab -->
                <div class="section-label">Filter Laboratorium</div>

                <div class="report-filter-panel mb-3">
                    <form method="GET">
                        <div class="input-group-modern">
                            <label>Pilih Laboratorium</label>
                            <select name="id_lab" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Laboratorium</option>
                                <?php while ($l = mysqli_fetch_assoc($semuaLab)): ?>
                                    <option value="<?= htmlspecialchars($l['id_lab']) ?>"
                                        <?= $filter_id_lab == $l['id_lab'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($l['nama_lab']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <?php if ($filter_id_lab > 0): ?>
                            <a href="jadwal.php" class="btn btn-neutral mt-2" style="min-height:44px; display:flex; align-items:center; justify-content:center; border-radius:14px; font-weight:900; font-size:13px; text-decoration:none;">
                                Tampilkan Semua Lab
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="section-label">
                    <?= $filter_id_lab > 0 ? 'Detail Laboratorium' : 'Daftar Laboratorium' ?>
                </div>

                <?php if (mysqli_num_rows($data) == 0): ?>
                    <div class="empty-state">
                        Tidak ada data laboratorium ditemukan.
                    </div>
                <?php endif; ?>

                <?php while ($d = mysqli_fetch_assoc($data)): ?>
                    <?php $isAvailable = $d['status'] == 'tersedia'; ?>

                    <div class="lab-card-modern">
                        <div class="lab-card-top">
                            <div>
                                <h2 class="lab-card-title">
                                    <?php if ($isAvailable): ?>
                                        <span class="status-dot green"></span>
                                    <?php else: ?>
                                        <span class="status-dot red"></span>
                                    <?php endif; ?>

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

                        <?php
                        $stmtJadwal = mysqli_prepare($conn, "
                            SELECT peminjaman.*, users.nama AS nama_user
                            FROM peminjaman
                            JOIN users ON peminjaman.id_user = users.id_user
                            WHERE peminjaman.id_lab = ?
                            AND peminjaman.status = 'disetujui'
                            ORDER BY peminjaman.tanggal_pinjam ASC, peminjaman.jam_mulai ASC
                        ");
                        mysqli_stmt_bind_param($stmtJadwal, "i", $d['id_lab']);
                        mysqli_stmt_execute($stmtJadwal);
                        $jadwalLab = mysqli_stmt_get_result($stmtJadwal);
                        ?>

                        <?php if (mysqli_num_rows($jadwalLab) > 0): ?>
                            <div class="mt-3">
                                <div style="font-size:12px; font-weight:900; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px;">
                                    Jadwal Peminjaman
                                </div>

                                <?php while ($j = mysqli_fetch_assoc($jadwalLab)): ?>
                                    <div style="background:#f8fafc; border:1px solid #eef2f7; border-radius:16px; padding:12px 14px; margin-bottom:10px;">
                                        <div style="font-size:14px; font-weight:900; color:var(--text-main); margin-bottom:4px;">
                                            <?= htmlspecialchars($j['nama_user']) ?>
                                        </div>
                                        <div style="font-size:13px; color:var(--text-muted); font-weight:600;">
                                            📅 <?= htmlspecialchars($j['tanggal_pinjam']) ?>
                                            &nbsp;•&nbsp;
                                            🕐 <?= htmlspecialchars($j['jam_mulai']) ?> - <?= htmlspecialchars($j['jam_selesai']) ?>
                                        </div>
                                        <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                                            📝 <?= htmlspecialchars($j['keperluan']) ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div style="margin-top:12px; font-size:13px; color:var(--text-muted); font-weight:600;">
                                Belum ada jadwal peminjaman untuk lab ini.
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>

            </div>

            <?php require_once "_nav.php"; ?>

        </section>
    </main>

</body>

</html>