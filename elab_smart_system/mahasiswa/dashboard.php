<?php
session_start();
include '../koneksi.php';

// Cek session
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$lab = mysqli_query($conn, "
    SELECT * FROM laboratorium
    WHERE status='tersedia'
");

$riwayat = mysqli_prepare($conn, "
    SELECT peminjaman.*, laboratorium.nama_lab
    FROM peminjaman
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    WHERE peminjaman.id_user = ?
    ORDER BY peminjaman.tanggal_pinjam DESC
");
mysqli_stmt_bind_param($riwayat, "i", $_SESSION['id_user']);
mysqli_stmt_execute($riwayat);
$riwayat = mysqli_stmt_get_result($riwayat);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Mahasiswa</title>
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

        .card-box {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .btn-ungu {
            background: #4b2ea7;
            color: white;
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

        .p-4 {
            padding-bottom: 80px !important;
        }
    </style>
</head>

<body>

    <div class="mobile">

        <div class="header">
            <h3>Dashboard Mahasiswa</h3>
            <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></p>
            <a href="../logout.php" style="font-size:12px; color:#ddd; text-decoration:none;">
                Logout
            </a>
        </div>

        <div class="p-4">

            <div class="card-box">
                <h5>Ajukan Peminjaman Laboratorium</h5>
                <form method="POST" action="simpan.php">

                    <div class="mb-3">
                        <label>Laboratorium</label>
                        <select name="id_lab" class="form-control" required>
                            <option value="">Pilih Lab</option>
                            <?php while ($d = mysqli_fetch_assoc($lab)) { ?>
                                <option value="<?= $d['id_lab'] ?>">
                                    <?= htmlspecialchars($d['nama_lab']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Keperluan</label>
                        <textarea name="keperluan" class="form-control" required></textarea>
                    </div>

                    <button class="btn btn-ungu w-100">Ajukan Peminjaman</button>

                </form>
            </div>

            <div class="card-box">
                <h5>Riwayat Peminjaman</h5>

                <?php if (mysqli_num_rows($riwayat) == 0) { ?>
                    <p class="text-secondary">Belum ada riwayat peminjaman.</p>
                <?php } ?>

                <?php while ($r = mysqli_fetch_assoc($riwayat)) { ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <b><?= htmlspecialchars($r['nama_lab']) ?></b>
                            <p class="mb-0 text-secondary" style="font-size:13px;">
                                <?= $r['tanggal_pinjam'] ?> • <?= $r['jam_mulai'] ?>-<?= $r['jam_selesai'] ?>
                            </p>
                        </div>
                        <div>
                            <span class="badge-<?= $r['status'] ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>
            </div>

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