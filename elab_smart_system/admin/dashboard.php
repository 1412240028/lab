<?php
session_start();
include '../koneksi.php';

// Cek session
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Statistik
$total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman"));
$review = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='menunggu'"));
$setuju = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='disetujui'"));
$labaktif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tersedia'"));

// Data grafik peminjaman per bulan
$grafik = mysqli_query($conn, "
    SELECT 
        MONTH(tanggal_pinjam) as bulan,
        COUNT(*) as total
    FROM peminjaman
    WHERE YEAR(tanggal_pinjam) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal_pinjam)
    ORDER BY bulan ASC
");

$bulan_label = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
$data_grafik = array_fill(0, 12, 0);

while ($g = mysqli_fetch_assoc($grafik)) {
    $data_grafik[(int) $g['bulan'] - 1] = (int) $g['total'];
}

// Hanya ambil yang menunggu
$peminjaman = mysqli_query($conn, "
    SELECT peminjaman.*, users.nama, users.role, laboratorium.nama_lab
    FROM peminjaman
    JOIN users ON peminjaman.id_user = users.id_user
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.status = 'menunggu'
");

// Inisial nama dari session
$namaAdmin = $_SESSION['nama'];
$inisial = strtoupper(substr($namaAdmin, 0, 2));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Dashboard Admin - E-Lab Smart System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="../assets/css/elab-ui.css">
</head>

<body>

    <main class="app-shell">
        <section class="app-container">

            <!-- Header -->
            <header class="app-header admin">
                <div class="app-header-content d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="app-title">Panel Admin</h1>
                        <p class="app-subtitle">
                            <?= htmlspecialchars($namaAdmin) ?> • Admin Laboratorium
                        </p>
                        <a href="../logout.php" class="app-logout">Keluar dari sistem</a>
                    </div>

                    <div class="profile-circle">
                        <?= htmlspecialchars($inisial) ?>
                    </div>
                </div>
            </header>

            <div class="app-body">

                <!-- Statistik -->
                <div class="section-label">Statistik Sistem</div>

                <div class="stat-grid">
                    <div class="stat-box admin">
                        <div class="stat-number purple"><?= $total ?></div>
                        <div class="stat-text">Total permohonan</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number warning"><?= $review ?></div>
                        <div class="stat-text">Menunggu review</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number success"><?= $setuju ?></div>
                        <div class="stat-text">Disetujui</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number primary"><?= $labaktif ?></div>
                        <div class="stat-text">Lab tersedia</div>
                    </div>
                </div>

                <div class="desktop-grid mt-4">

                    <div>
                        <!-- Grafik -->
                        <div class="section-label">Peminjaman Per Bulan</div>

                        <div class="panel-card chart-card">
                            <canvas id="grafikPeminjaman" height="160"></canvas>
                        </div>

                        <script>
                            const ctx = document.getElementById('grafikPeminjaman').getContext('2d');

                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode($bulan_label) ?>,
                                    datasets: [{
                                        label: 'Jumlah Peminjaman',
                                        data: <?= json_encode($data_grafik) ?>,
                                        backgroundColor: '#40318f',
                                        borderRadius: 10,
                                        maxBarThickness: 36
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: {
                                                display: false
                                            },
                                            ticks: {
                                                color: '#64748b',
                                                font: {
                                                    family: 'Inter',
                                                    weight: '700'
                                                }
                                            }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 1,
                                                color: '#64748b',
                                                font: {
                                                    family: 'Inter',
                                                    weight: '700'
                                                }
                                            },
                                            grid: {
                                                color: 'rgba(148, 163, 184, 0.18)'
                                            }
                                        }
                                    }
                                }
                            });
                        </script>

                        <!-- Permohonan -->
                        <div class="section-label">Permohonan Menunggu Review</div>

                        <?php if (mysqli_num_rows($peminjaman) == 0) { ?>
                            <div class="empty-state">
                                Tidak ada permohonan yang menunggu review. Aman, admin bisa napas dulu.
                            </div>
                        <?php } ?>

                        <?php while ($d = mysqli_fetch_assoc($peminjaman)) { ?>
                            <div class="request-card-modern">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h2 class="request-name"><?= htmlspecialchars($d['nama']) ?></h2>
                                        <p class="request-meta">
                                            <?= htmlspecialchars($d['nama_lab']) ?><br>
                                            <?= htmlspecialchars($d['tanggal_pinjam']) ?>,
                                            <?= htmlspecialchars($d['jam_mulai']) ?> - <?= htmlspecialchars($d['jam_selesai']) ?>
                                        </p>
                                    </div>

                                    <span class="role-badge-modern">
                                        <?= htmlspecialchars(ucfirst($d['role'])) ?>
                                    </span>
                                </div>

                                <div class="action-row">
                                    <a
                                        href="proses.php?id=<?= $d['id_peminjaman'] ?>&status=ditolak"
                                        class="btn-action btn-reject"
                                        onclick="return confirm('Tolak permohonan ini?')">
                                        Tolak
                                    </a>

                                    <a
                                        href="proses.php?id=<?= $d['id_peminjaman'] ?>&status=disetujui"
                                        class="btn-action btn-approve"
                                        onclick="return confirm('Setujui permohonan ini?')">
                                        Setujui
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div>
                        <!-- Status Lab -->
                        <div class="section-label">Status Laboratorium</div>

                        <div class="panel-card">
                            <?php
                            $lab = mysqli_query($conn, "SELECT * FROM laboratorium");
                            if (mysqli_num_rows($lab) == 0) {
                                echo '<div class="empty-state">Belum ada data laboratorium.</div>';
                            }

                            while ($l = mysqli_fetch_assoc($lab)) {
                            ?>
                                <div class="lab-status-item">
                                    <div class="lab-name">
                                        <?php if ($l['status'] == 'tersedia') { ?>
                                            <span class="status-dot green"></span>
                                        <?php } else { ?>
                                            <span class="status-dot red"></span>
                                        <?php } ?>

                                        <?= htmlspecialchars($l['nama_lab']) ?>
                                    </div>

                                    <div class="lab-capacity">
                                        <?= htmlspecialchars($l['kapasitas']) ?> kursi
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Bottom Nav -->
            <nav class="bottom-nav-modern">
                <a href="dashboard.php" class="active">Beranda</a>
                <a href="jadwal.php">Jadwal</a>
                <a href="laporan.php">Laporan</a>
                <a href="kelola.php">Kelola</a>
                <a href="kelola_user.php">User</a>
                <a href="../logout.php">Logout</a>
            </nav>

        </section>
    </main>

</body>

</html>