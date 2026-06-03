<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($conn,"SELECT * FROM laboratorium");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Lab</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#efefef; font-family:Arial; }
        .mobile{ max-width:430px; margin:auto; background:white; min-height:100vh; }
        .header{ background:#4b2ea7; color:white; padding:25px; border-bottom-left-radius:20px; border-bottom-right-radius:20px; }
        .section-title{ font-size:14px; font-weight:bold; color:#555; margin-top:20px; margin-bottom:15px; }
        .lab-card{ background:white; border:1px solid #eee; border-radius:20px; padding:15px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
        .dot-green{ width:15px; height:15px; background:#10ac84; border-radius:50%; display:inline-block; margin-right:10px; }
        .dot-red{ width:15px; height:15px; background:#ee5253; border-radius:50%; display:inline-block; margin-right:10px; }
        .bottom-nav{ position:fixed; bottom:0; width:100%; max-width:430px; background:white; display:flex; justify-content:space-around; padding:15px 0; border-top:1px solid #eee; }
        .nav-item{ color:#999; font-size:14px; text-align:center; text-decoration:none; }
        .active-nav{ color:#4b2ea7; font-weight:bold; }
        .p-4{ padding-bottom:80px!important; }
    </style>
</head>
<body>

<div class="mobile">

    <div class="header">
        <h3>Jadwal Laboratorium</h3>
        <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?> • Admin Laboratorium</p>
    </div>

    <div class="p-4">

        <div class="section-title">DAFTAR JADWAL LAB</div>

        <?php if(mysqli_num_rows($data) == 0){ ?>
            <p class="text-secondary">Tidak ada data laboratorium.</p>
        <?php } ?>

        <?php while($d = mysqli_fetch_assoc($data)){ ?>
        <div class="lab-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">
                        <?php if($d['status'] == 'tersedia'){ ?>
                            <span class="dot-green"></span>
                        <?php } else { ?>
                            <span class="dot-red"></span>
                        <?php } ?>
                        <?= htmlspecialchars($d['nama_lab']) ?>
                    </h6>
                    <p class="mb-0 text-secondary" style="font-size:13px;">
                        📍 <?= htmlspecialchars($d['lokasi']) ?>
                    </p>
                    <p class="mb-0 text-secondary" style="font-size:13px;">
                        💺 <?= $d['kapasitas'] ?> kursi
                    </p>
                </div>
                <div>
                    <span style="
                        background:<?= $d['status'] == 'tersedia' ? '#e8f8f4' : '#fdecea' ?>;
                        color:<?= $d['status'] == 'tersedia' ? '#0f8b63' : '#c0392b' ?>;
                        padding:5px 10px;
                        border-radius:10px;
                        font-size:12px;
                        font-weight:bold;">
                        <?= ucfirst($d['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">Beranda</a>
        <a href="jadwal.php" class="nav-item active-nav">Jadwal</a>
        <a href="laporan.php" class="nav-item">Laporan</a>
        <a href="kelola.php" class="nav-item">Kelola</a>
        <a href="kelola_user.php" class="nav-item">User</a>
        <a href="../logout.php" class="nav-item">Logout</a>
    </div>

</div>

</body>
</html>