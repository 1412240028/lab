<?php
session_start();
require_once "_guard.php";
require_once "../koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM laboratorium ORDER BY nama_lab ASC");

// Ambil data lab untuk edit jika ada id_edit
$editData = null;

if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];

    $q = mysqli_prepare($conn, "SELECT * FROM laboratorium WHERE id_lab=?");
    mysqli_stmt_bind_param($q, "i", $id_edit);
    mysqli_stmt_execute($q);

    $editData = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
}

$totalLab = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium"));
$labTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tersedia'"));
$labTidakTersedia = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laboratorium WHERE status='tidak tersedia'"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Kelola Laboratorium - E-Lab Smart System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body>

    <main class="app-shell">
        <section class="app-container">

            <!-- Header -->
            <header class="app-header admin">
                <div class="app-header-content">
                    <h1 class="app-title">Kelola Laboratorium</h1>
                    <p class="app-subtitle">
                        <?= htmlspecialchars($_SESSION['nama']) ?> • Tambah, edit, dan hapus data lab
                    </p>
                    <a href="../logout.php" class="app-logout">Keluar dari sistem</a>
                </div>
            </header>

            <div class="app-body">

                <?php if (isset($_GET['success'])) { ?>
                    <div class="alert alert-success mb-3">
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php } ?>

                <?php if (isset($_GET['error'])) { ?>
                    <div class="alert alert-danger mb-3">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php } ?>
                
                <!-- Statistik Mini -->
                <div class="section-label">Ringkasan Data Lab</div>

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
                        <div class="stat-number primary"><?= $totalLab ?></div>
                        <div class="stat-text">Data tercatat</div>
                    </div>
                </div>

                <div class="manage-layout mt-4">

                    <!-- Form Tambah / Edit -->
                    <div class="form-panel">
                        <h2 class="panel-title">
                            <?= $editData ? 'Edit Laboratorium' : 'Tambah Laboratorium' ?>
                        </h2>

                        <p class="panel-desc">
                            <?= $editData
                                ? 'Perbarui detail laboratorium yang sudah terdaftar di sistem.'
                                : 'Tambahkan data laboratorium baru agar bisa digunakan dalam pengajuan peminjaman.' ?>
                        </p>

                        <?php if ($editData) { ?>
                            <div class="edit-mode-banner">
                                Mode edit aktif untuk:
                                <strong><?= htmlspecialchars($editData['nama_lab']) ?></strong>
                            </div>
                        <?php } ?>

                        <form method="POST" action="kelola_proses.php">

                            <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">

                            <?php if ($editData) { ?>
                                <input type="hidden" name="id_lab" value="<?= htmlspecialchars($editData['id_lab']) ?>">
                            <?php } ?>

                            <div class="input-group-modern">
                                <label>Nama Laboratorium</label>
                                <input type="text" name="nama_lab" class="form-control"
                                    placeholder="Contoh: Lab Komputer 1"
                                    value="<?= $editData ? htmlspecialchars($editData['nama_lab']) : '' ?>" required>
                            </div>

                            <div class="input-group-modern">
                                <label>Kapasitas</label>
                                <input type="number" name="kapasitas" class="form-control" placeholder="Contoh: 30"
                                    min="1" value="<?= $editData ? htmlspecialchars($editData['kapasitas']) : '' ?>"
                                    required>
                            </div>

                            <div class="input-group-modern">
                                <label>Lokasi</label>
                                <input type="text" name="lokasi" class="form-control"
                                    placeholder="Contoh: Gedung A Lantai 2"
                                    value="<?= $editData ? htmlspecialchars($editData['lokasi']) : '' ?>" required>
                            </div>

                            <div class="input-group-modern">
                                <label>Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="tersedia" <?= ($editData && $editData['status'] == 'tersedia') ? 'selected' : '' ?>>
                                        Tersedia
                                    </option>
                                    <option value="tidak tersedia" <?= ($editData && $editData['status'] == 'tidak tersedia') ? 'selected' : '' ?>>
                                        Tidak Tersedia
                                    </option>
                                </select>
                            </div>

                            <button class="btn btn-admin-primary w-100">
                                <?= $editData ? 'Simpan Perubahan' : 'Tambah Lab' ?>
                            </button>

                            <?php if ($editData) { ?>
                                <a href="kelola.php" class="btn btn-neutral w-100 mt-2">
                                    Batal Edit
                                </a>
                            <?php } ?>

                        </form>
                    </div>

                    <!-- List Lab -->
                    <div>
                        <div class="section-label mt-0">Daftar Laboratorium</div>

                        <?php if (mysqli_num_rows($data) == 0) { ?>
                            <div class="empty-state">
                                Belum ada data laboratorium. Tambahkan lab pertama dari form di samping.
                            </div>
                        <?php } ?>

                        <div class="lab-list-modern">
                            <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                                <?php $isAvailable = $d['status'] == 'tersedia'; ?>

                                <div class="manage-lab-card">
                                    <div class="manage-lab-head">
                                        <div>
                                            <h3 class="manage-lab-name">
                                                <?php if ($isAvailable) { ?>
                                                    <span class="status-dot green"></span>
                                                <?php } else { ?>
                                                    <span class="status-dot red"></span>
                                                <?php } ?>

                                                <?= htmlspecialchars($d['nama_lab']) ?>
                                            </h3>

                                            <p class="manage-lab-meta">
                                                💺 <?= htmlspecialchars($d['kapasitas']) ?> kursi<br>
                                                📍 <?= htmlspecialchars($d['lokasi']) ?>
                                            </p>
                                        </div>

                                        <span class="badge-status <?= $isAvailable ? 'available' : 'unavailable' ?>">
                                            <?= $isAvailable ? 'Tersedia' : 'Tidak tersedia' ?>
                                        </span>
                                    </div>

                                    <div class="manage-action-row">
                                        <a href="kelola.php?edit=<?= htmlspecialchars($d['id_lab']) ?>"
                                            class="btn-mini-edit">
                                            Edit
                                        </a>

                                        <form method="POST" action="kelola_proses.php"
                                            onsubmit="return confirm('Hapus laboratorium ini? Data yang terhubung bisa ikut terdampak.')">

                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_lab"
                                                value="<?= htmlspecialchars($d['id_lab']) ?>">

                                            <button class="btn-mini-delete">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>

            </div>

            <?php require_once "_nav.php"; ?>

        </section>
    </main>

</body>

</html>