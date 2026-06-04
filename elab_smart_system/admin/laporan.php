<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil filter dari GET
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

// Build query dinamis
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($cari != '') {
    $where .= " AND (users.nama LIKE ? OR laboratorium.nama_lab LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $types .= "ss";
}

if ($filter_status != '') {
    $where .= " AND peminjaman.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if ($filter_tanggal != '') {
    $where .= " AND peminjaman.tanggal_pinjam = ?";
    $params[] = $filter_tanggal;
    $types .= "s";
}

$stmt = mysqli_prepare($conn, "
    SELECT peminjaman.*, users.nama, laboratorium.nama_lab
    FROM peminjaman
    JOIN users ON peminjaman.id_user = users.id_user
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    $where
    ORDER BY peminjaman.tanggal_pinjam DESC
");

if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);

// Statistik ringkas laporan
$totalLaporan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman"));
$totalMenunggu = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='menunggu'"));
$totalDisetujui = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='disetujui'"));
$totalDitolak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='ditolak'"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Laporan Peminjaman - E-Lab Smart System</title>
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
                    <h1 class="app-title">Laporan Peminjaman</h1>
                    <p class="app-subtitle">
                        <?= htmlspecialchars($_SESSION['nama']) ?> • Monitoring data peminjaman laboratorium
                    </p>
                    <a href="../logout.php" class="app-logout">Keluar dari sistem</a>
                </div>
            </header>

            <div class="app-body">

                <!-- Statistik -->
                <div class="section-label">Ringkasan Laporan</div>

                <div class="stat-grid">
                    <div class="stat-box admin">
                        <div class="stat-number purple"><?= $totalLaporan ?></div>
                        <div class="stat-text">Total data</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number warning"><?= $totalMenunggu ?></div>
                        <div class="stat-text">Menunggu</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number success"><?= $totalDisetujui ?></div>
                        <div class="stat-text">Disetujui</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number primary"><?= $totalDitolak ?></div>
                        <div class="stat-text">Ditolak</div>
                    </div>
                </div>

                <div class="report-page-grid mt-4">

                    <!-- Filter -->
                    <div>
                        <div class="report-summary">
                            <h2>Filter Data</h2>
                            <p>
                                Gunakan pencarian, status, atau tanggal untuk menemukan laporan peminjaman secara cepat.
                            </p>
                        </div>

                        <div class="report-filter-panel">
                            <form method="GET">

                                <div class="filter-grid">

                                    <div class="input-group-modern">
                                        <label>Cari Nama / Lab</label>
                                        <input
                                            type="text"
                                            name="cari"
                                            class="form-control"
                                            placeholder="Contoh: Dhoni / Lab Komputer"
                                            value="<?= htmlspecialchars($cari) ?>">
                                    </div>

                                    <div class="input-group-modern">
                                        <label>Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">Semua Status</option>
                                            <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>
                                                Menunggu
                                            </option>
                                            <option value="disetujui" <?= $filter_status == 'disetujui' ? 'selected' : '' ?>>
                                                Disetujui
                                            </option>
                                            <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>
                                                Ditolak
                                            </option>
                                        </select>
                                    </div>

                                    <div class="input-group-modern">
                                        <label>Tanggal</label>
                                        <input
                                            type="date"
                                            name="tanggal"
                                            class="form-control"
                                            value="<?= htmlspecialchars($filter_tanggal) ?>">
                                    </div>

                                </div>

                                <div class="report-actions">
                                    <button class="btn-filter">
                                        Filter
                                    </button>

                                    <a href="laporan.php" class="btn-reset">
                                        Reset
                                    </a>
                                </div>

                                <div class="export-actions">
                                    <a href="export_excel.php" class="btn-export-excel">
                                        Export Excel
                                    </a>

                                    <a href="export_pdf.php" class="btn-export-pdf">
                                        Export PDF
                                    </a>
                                </div>

                            </form>
                        </div>
                    </div>

                    <!-- Hasil -->
                    <div>
                        <div class="section-label mt-0">Hasil Laporan</div>

                        <?php if (mysqli_num_rows($data) == 0) { ?>
                            <div class="empty-state">
                                Tidak ada data ditemukan. Coba ubah filter pencarian.
                            </div>
                        <?php } ?>

                        <div class="report-list">
                            <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                                <div class="report-card-modern">
                                    <div class="report-card-top">
                                        <div>
                                            <h3 class="report-user">
                                                <?= htmlspecialchars($d['nama']) ?>
                                            </h3>

                                            <p class="report-lab">
                                                <?= htmlspecialchars($d['nama_lab']) ?>
                                            </p>

                                            <p class="report-meta">
                                                📅 <?= htmlspecialchars($d['tanggal_pinjam']) ?><br>
                                                🕐 <?= htmlspecialchars($d['jam_mulai']) ?> - <?= htmlspecialchars($d['jam_selesai']) ?><br>
                                                📝 <?= htmlspecialchars($d['keperluan']) ?>
                                            </p>
                                        </div>

                                        <span class="badge-report <?= htmlspecialchars($d['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($d['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Bottom Nav -->
            <nav class="bottom-nav-modern">
                <a href="dashboard.php">Beranda</a>
                <a href="jadwal.php">Jadwal</a>
                <a href="laporan.php" class="active">Laporan</a>
                <a href="kelola.php">Kelola</a>
                <a href="kelola_user.php">User</a>
                <a href="../logout.php">Logout</a>
            </nav>

        </section>
    </main>

</body>

</html>