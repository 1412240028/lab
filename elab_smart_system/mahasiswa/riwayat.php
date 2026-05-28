<?php
session_start();
include '../koneksi.php';

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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Riwayat Peminjaman</title>
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

        .riwayat-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .badge-menunggu {
            background: #f39c12;
            color: white;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 12px;
        }

        .badge-disetujui {
            background: #0f8b63;
            color: white;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 12px;
        }

        .badge-ditolak {
            background: #c0392b;
            color: white;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 12px;
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
            text-decoration: none;
        }

        .active-nav {
            color: #4b2ea7;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="mobile">

        <div class="header">
            <h3>Riwayat Peminjaman</h3>
            <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></p>
        </div>

        <div class="p-4" style="padding-bottom:80px!important;">

            <?php if (mysqli_num_rows($riwayat) == 0) { ?>
                <p class="text-secondary">Belum ada riwayat peminjaman.</p>
            <?php } ?>

            <?php while ($r = mysqli_fetch_assoc($riwayat)) { ?>
                <div class="riwayat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($r['nama_lab']) ?></h6>
                            <p class="text-secondary mb-1" style="font-size:13px;">
                                📅 <?= $r['tanggal_pinjam'] ?>
                            </p>
                            <p class="text-secondary mb-1" style="font-size:13px;">
                                🕐 <?= $r['jam_mulai'] ?> - <?= $r['jam_selesai'] ?>
                            </p>
                            <p class="text-secondary mb-0" style="font-size:13px;">
                                📝 <?= htmlspecialchars($r['keperluan']) ?>
                            </p>
                        </div>
                        <div>
                            <span class="badge-<?= $r['status'] ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-item active-nav">Beranda</a>
            <a href="riwayat.php" class="nav-item">Riwayat</a>
            <a href="notifikasi.php" class="nav-item">Notifikasi</a>
            <a href="../logout.php" class="nav-item">Logout</a>
        </div>

    </div>

</body>

</html>