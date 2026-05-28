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
<html>

<head>
    <title>E-Lab Smart System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #efefef;
            font-family: Arial;
        }

        .mobile {
            max-width: 430px;
            margin: auto;
            background: white;
            min-height: 100vh;
        }

        .header {
            background: #4b2ea7;
            color: white;
            padding: 25px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .profile {
            width: 55px;
            height: 55px;
            background: white;
            color: #4b2ea7;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .stat-card {
            background: #f8f8f8;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 35px;
            font-weight: bold;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        .request-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .role-badge {
            background: #f2f2f2;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 12px;
            color: #666;
        }

        .btn-tolak {
            border: 1px solid #c0392b;
            color: #c0392b;
            border-radius: 12px;
            padding: 10px;
            width: 48%;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-setuju {
            background: #0f8b63;
            color: white;
            border-radius: 12px;
            padding: 10px;
            width: 48%;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
        }

        .lab-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .dot-green {
            width: 15px;
            height: 15px;
            background: #10ac84;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }

        .dot-red {
            width: 15px;
            height: 15px;
            background: #ee5253;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            max-width: 430px;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }

        .nav-item {
            color: #999;
            font-size: 14px;
            text-align: center;
        }

        .active-nav {
            color: #4b2ea7;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="mobile">

        <div class="header d-flex justify-content-between align-items-center">
            <div>
                <h3>Panel Admin</h3>
                <p class="mb-0"><?= htmlspecialchars($namaAdmin) ?> • Admin Laboratorium</p>
                <a href="../logout.php" style="font-size:12px; color:#ddd; text-decoration:none;">
                    Logout
                </a>
            </div>
            <div class="profile"><?= $inisial ?></div>
        </div>

        <div class="p-4">

            <div class="section-title">STATISTIK HARI INI</div>

            <div class="row">
                <div class="col-6">
                    <div class="stat-card">
                        <div class="stat-number text-primary"><?= $total ?></div>
                        Permohonan masuk
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <div class="stat-number text-danger"><?= $review ?></div>
                        Menunggu review
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <div class="stat-number text-success"><?= $setuju ?></div>
                        Disetujui hari ini
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <div class="stat-number text-success"><?= $labaktif ?></div>
                        Lab aktif dipakai
                    </div>
                </div>
            </div>

            <div class="section-title">PERMOHONAN MENUNGGU REVIEW</div>

            <?php if (mysqli_num_rows($peminjaman) == 0) { ?>
                <p class="text-secondary">Tidak ada permohonan menunggu.</p>
            <?php } ?>

            <?php while ($d = mysqli_fetch_assoc($peminjaman)) { ?>
                <div class="request-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5><?= htmlspecialchars($d['nama']) ?></h5>
                            <p class="text-secondary mb-2">
                                <?= htmlspecialchars($d['nama_lab']) ?> •
                                <?= $d['tanggal_pinjam'] ?>,
                                <?= $d['jam_mulai'] ?>-<?= $d['jam_selesai'] ?>
                            </p>
                        </div>
                        <div>
                            <span class="role-badge"><?= ucfirst($d['role']) ?></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="proses.php?id=<?= $d['id_peminjaman'] ?>&status=ditolak" class="btn-tolak">Tolak</a>
                        <a href="proses.php?id=<?= $d['id_peminjaman'] ?>&status=disetujui" class="btn-setuju">Setujui</a>
                    </div>
                </div>
            <?php } ?>

            <div class="section-title">STATUS LABORATORIUM</div>

            <?php
            $lab = mysqli_query($conn, "SELECT * FROM laboratorium");
            while ($l = mysqli_fetch_assoc($lab)) {
                ?>
                <div class="lab-item">
                    <div>
                        <?php if ($l['status'] == 'tersedia') { ?>
                            <span class="dot-green"></span>
                        <?php } else { ?>
                            <span class="dot-red"></span>
                        <?php } ?>
                        <?= htmlspecialchars($l['nama_lab']) ?>
                    </div>
                    <div class="text-secondary"><?= $l['kapasitas'] ?> kursi</div>
                </div>
            <?php } ?>

        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-item active-nav" style="text-decoration:none;">Beranda</a>
            <a href="jadwal.php" class="nav-item" style="text-decoration:none;">Jadwal</a>
            <a href="laporan.php" class="nav-item" style="text-decoration:none;">Laporan</a>
            <a href="kelola.php" class="nav-item" style="text-decoration:none;">Kelola</a>
        </div>

    </div>

</body>

</html>