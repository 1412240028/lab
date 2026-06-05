<?php
require_once "_guard.php";
require_once "../koneksi.php";

$nama = $_SESSION['nama'] ?? 'Dosen';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPeminjaman = isset($_POST['id_peminjaman']) ? (int) $_POST['id_peminjaman'] : 0;
    $aksi = isset($_POST['aksi']) ? trim($_POST['aksi']) : '';

    if ($idPeminjaman > 0 && in_array($aksi, ['disetujui', 'ditolak'], true)) {
        $stmt = mysqli_prepare($conn, "
            UPDATE peminjaman
            SET status = ?, dibaca = 0
            WHERE id_peminjaman = ?
            AND status = 'menunggu'
        ");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $aksi, $idPeminjaman);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Status peminjaman berhasil diperbarui.";
                $messageType = 'success';
            } else {
                $message = "Gagal memperbarui status peminjaman.";
                $messageType = 'danger';
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = "Query update gagal disiapkan.";
            $messageType = 'danger';
        }
    } else {
        $message = "Aksi tidak valid.";
        $messageType = 'danger';
    }
}

$peminjaman = mysqli_query($conn, "
    SELECT
        peminjaman.*,
        users.nama AS nama_user,
        users.email,
        laboratorium.nama_lab
    FROM peminjaman
    LEFT JOIN users ON peminjaman.id_user = users.id_user
    LEFT JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.status = 'menunggu'
    ORDER BY peminjaman.tanggal_pinjam DESC, peminjaman.jam_mulai ASC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Verifikasi Dosen - E-Lab Smart System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="dosen-page">

    <div class="app-shell">
        <div class="app-container dosen-container">

            <header class="app-header lecturer">
                <div class="app-header-content d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="app-title">Dashboard Dosen</h1>
                        <p class="app-subtitle">
                            <?= htmlspecialchars($nama); ?> • Panel akademik laboratorium
                        </p>
                        <a href="../logout.php" class="app-logout">Keluar dari sistem</a>
                    </div>

                    <div class="profile-circle lecturer">
                        <?= strtoupper(substr($nama, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="app-body dosen-body">

                <section class="verification-info-box">
                    <div>
                        <span class="verification-kicker">Approval Center</span>
                        <h2>Pengajuan Menunggu</h2>
                        <p>
                            Pastikan detail tanggal, jam, laboratorium, dan keperluan sudah sesuai sebelum mengambil
                            keputusan.
                        </p>
                    </div>
                </section>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType); ?> mb-3">
                        <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <section class="verification-list">

                    <?php if ($peminjaman && mysqli_num_rows($peminjaman) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($peminjaman)): ?>
                            <article class="verification-card">
                                <div class="verification-card-top">
                                    <div>
                                        <span class="verification-label">Mahasiswa</span>

                                        <h2 class="verification-name">
                                            <?= htmlspecialchars($row['nama_user'] ?? 'Mahasiswa'); ?>
                                        </h2>

                                        <div class="verification-detail-grid">
                                            <div class="verification-detail-item">
                                                <span>Email</span>
                                                <strong><?= htmlspecialchars($row['email'] ?? '-'); ?></strong>
                                            </div>

                                            <div class="verification-detail-item">
                                                <span>Laboratorium</span>
                                                <strong><?= htmlspecialchars($row['nama_lab'] ?? '-'); ?></strong>
                                            </div>

                                            <div class="verification-detail-item">
                                                <span>Tanggal</span>
                                                <strong><?= htmlspecialchars($row['tanggal_pinjam'] ?? '-'); ?></strong>
                                            </div>

                                            <div class="verification-detail-item">
                                                <span>Jam</span>
                                                <strong>
                                                    <?= htmlspecialchars($row['jam_mulai'] ?? '-'); ?>
                                                    -
                                                    <?= htmlspecialchars($row['jam_selesai'] ?? '-'); ?>
                                                </strong>
                                            </div>
                                        </div>

                                        <div class="verification-purpose">
                                            <span>Keperluan</span>
                                            <p><?= htmlspecialchars($row['keperluan'] ?? '-'); ?></p>
                                        </div>
                                    </div>

                                    <span class="badge-status menunggu">
                                        Menunggu
                                    </span>
                                </div>

                                <form method="POST" class="verification-action-row">
                                    <input type="hidden" name="id_peminjaman"
                                        value="<?= htmlspecialchars($row['id_peminjaman']); ?>">

                                    <button type="submit" name="aksi" value="disetujui" class="btn-elab btn-approve">
                                        Setujui
                                    </button>

                                    <button type="submit" name="aksi" value="ditolak" class="btn-elab btn-reject">
                                        Tolak
                                    </button>
                                </form>
                            </article>
                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="verification-empty-state">
                            <div class="empty-icon">🎉</div>
                            <h2>Tidak Ada Pengajuan</h2>
                            <p>
                                Semua pengajuan sudah diproses. Aman, dashboard nggak lagi teriak minta approval.
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