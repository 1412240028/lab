<?php
require_once "_guard.php";
require_once "../koneksi.php";

$nama = $_SESSION['nama'] ?? 'Dosen';

$jadwal = mysqli_query($conn, "
    SELECT 
        peminjaman.*,
        users.nama AS nama_user,
        users.email,
        laboratorium.nama_lab
    FROM peminjaman
    LEFT JOIN users ON peminjaman.id_user = users.id_user
    LEFT JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.status = 'disetujui'
    ORDER BY peminjaman.tanggal_pinjam DESC, peminjaman.jam_mulai ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Dosen - E-Lab Smart System</title>
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
                <h1>Jadwal Laboratorium</h1>
                <p class="dosen-header-subtitle">
                    Pantau daftar penggunaan laboratorium yang sudah disetujui dan siap berjalan.
                </p>
            </div>

            <div class="dosen-avatar">
                <?= strtoupper(substr($nama, 0, 1)); ?>
            </div>
        </header>

        <main class="app-body dosen-body">

            <section class="schedule-hero-box">
                <div>
                    <span class="schedule-kicker">Jadwal Aktif</span>
                    <h2>Daftar Penggunaan Lab</h2>
                    <p>
                        Data jadwal berasal dari pengajuan peminjaman mahasiswa yang sudah disetujui.
                    </p>
                </div>

                <div class="schedule-hero-icon">
                    📅
                </div>
            </section>

            <section class="lecturer-schedule-list">

                <?php if ($jadwal && mysqli_num_rows($jadwal) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($jadwal)): ?>
                        <article class="lecturer-schedule-card">
                            <div class="lecturer-schedule-top">
                                <div>
                                    <span class="schedule-label">Laboratorium</span>

                                    <h2 class="lecturer-schedule-title">
                                        <?= htmlspecialchars($row['nama_lab'] ?? 'Laboratorium'); ?>
                                    </h2>

                                    <div class="schedule-detail-grid">
                                        <div class="schedule-detail-item">
                                            <span>Mahasiswa</span>
                                            <strong><?= htmlspecialchars($row['nama_user'] ?? '-'); ?></strong>
                                        </div>

                                        <div class="schedule-detail-item">
                                            <span>Email</span>
                                            <strong><?= htmlspecialchars($row['email'] ?? '-'); ?></strong>
                                        </div>

                                        <div class="schedule-detail-item">
                                            <span>Tanggal</span>
                                            <strong><?= htmlspecialchars($row['tanggal_pinjam'] ?? '-'); ?></strong>
                                        </div>

                                        <div class="schedule-detail-item">
                                            <span>Jam</span>
                                            <strong>
                                                <?= htmlspecialchars($row['jam_mulai'] ?? '-'); ?>
                                                -
                                                <?= htmlspecialchars($row['jam_selesai'] ?? '-'); ?>
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="schedule-purpose">
                                        <span>Keperluan</span>
                                        <p><?= htmlspecialchars($row['keperluan'] ?? '-'); ?></p>
                                    </div>
                                </div>

                                <span class="lecturer-schedule-chip">
                                    Disetujui
                                </span>
                            </div>
                        </article>
                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="lecturer-empty-state">
                        <div class="empty-icon">📭</div>
                        <h2>Belum Ada Jadwal</h2>
                        <p>
                            Belum ada peminjaman laboratorium yang disetujui.
                        </p>
                    </div>

                <?php endif; ?>

                <?php require_once "_nav.php"; ?>

            </section>

        </main>

        

    </div>
</div>

</body>
</html>