<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa'){
    header("Location: ../login.php");
    exit;
}

// Ambil semua notifikasi milik user
$stmt = mysqli_prepare($conn,"
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

// Tandai semua sudah dibaca
$markRead = mysqli_prepare($conn,"
    UPDATE peminjaman SET dibaca=1
    WHERE id_user=? AND dibaca=0
");
mysqli_stmt_bind_param($markRead, "i", $_SESSION['id_user']);
mysqli_stmt_execute($markRead);

// Hitung notif belum dibaca (untuk badge)
$stmtCount = mysqli_prepare($conn,"
    SELECT COUNT(*) as total FROM peminjaman
    WHERE id_user=? AND dibaca=0 AND status != 'menunggu'
");
mysqli_stmt_bind_param($stmtCount, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmtCount);
$count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount));
$unread = $count['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#efefef; font-family:Arial; }
        .mobile{ max-width:430px; margin:auto; background:white; min-height:100vh; }
        .header{ background:#4b2ea7; color:white; padding:25px; border-bottom-left-radius:20px; border-bottom-right-radius:20px; }
        .notif-card{ background:white; border:1px solid #eee; border-radius:20px; padding:15px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
        .notif-unread{ border-left:4px solid #4b2ea7; }
        .badge-disetujui{ background:#0f8b63; color:white; padding:4px 10px; border-radius:10px; font-size:12px; }
        .badge-ditolak{ background:#c0392b; color:white; padding:4px 10px; border-radius:10px; font-size:12px; }
        .bottom-nav{ position:fixed; bottom:0; width:100%; max-width:430px; background:white; display:flex; justify-content:space-around; padding:15px 0; border-top:1px solid #eee; }
        .nav-item{ color:#999; font-size:14px; text-align:center; text-decoration:none; }
        .active-nav{ color:#4b2ea7; font-weight:bold; }
        .badge-count{ background:#ee5253; color:white; border-radius:50%; padding:2px 6px; font-size:10px; }
    </style>
</head>
<body>

<div class="mobile">

    <div class="header">
        <h3>Notifikasi</h3>
        <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></p>
    </div>

    <div class="p-4" style="padding-bottom:80px!important;">

        <?php if(mysqli_num_rows($notif) == 0){ ?>
            <p class="text-secondary">Belum ada notifikasi.</p>
        <?php } ?>

        <?php while($n = mysqli_fetch_assoc($notif)){ ?>
        <div class="notif-card <?= $n['dibaca'] == 0 ? 'notif-unread' : '' ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <?php if($n['status'] == 'disetujui'){ ?>
                        <p class="mb-1">✅ Peminjaman <b><?= htmlspecialchars($n['nama_lab']) ?></b> disetujui</p>
                    <?php } else { ?>
                        <p class="mb-1">❌ Peminjaman <b><?= htmlspecialchars($n['nama_lab']) ?></b> ditolak</p>
                    <?php } ?>
                    <p class="text-secondary mb-0" style="font-size:13px;">
                        📅 <?= $n['tanggal_pinjam'] ?> • 🕐 <?= $n['jam_mulai'] ?>-<?= $n['jam_selesai'] ?>
                    </p>
                </div>
                <span class="badge-<?= $n['status'] ?>"><?= ucfirst($n['status']) ?></span>
            </div>
        </div>
        <?php } ?>

    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">Beranda</a>
        <a href="riwayat.php" class="nav-item">Riwayat</a>
        <a href="notifikasi.php" class="nav-item active-nav">
            Notifikasi
            <?php if($unread > 0){ ?>
                <span class="badge-count"><?= $unread ?></span>
            <?php } ?>
        </a>
        <a href="../logout.php" class="nav-item">Logout</a>
    </div>

</div>

</body>
</html>