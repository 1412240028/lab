<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
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

if($cari != ''){
    $where .= " AND (users.nama LIKE ? OR laboratorium.nama_lab LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $types .= "ss";
}

if($filter_status != ''){
    $where .= " AND peminjaman.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if($filter_tanggal != ''){
    $where .= " AND peminjaman.tanggal_pinjam = ?";
    $params[] = $filter_tanggal;
    $types .= "s";
}

$stmt = mysqli_prepare($conn,"
    SELECT peminjaman.*, users.nama, laboratorium.nama_lab
    FROM peminjaman
    JOIN users ON peminjaman.id_user = users.id_user
    JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
    $where
    ORDER BY peminjaman.tanggal_pinjam DESC
");

if(count($params) > 0){
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#efefef; font-family:Arial; }
        .mobile{ max-width:430px; margin:auto; background:white; min-height:100vh; }
        .header{ background:#4b2ea7; color:white; padding:25px; border-bottom-left-radius:20px; border-bottom-right-radius:20px; }
        .card-box{ background:white; padding:20px; border-radius:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
        .btn-ungu{ background:#4b2ea7; color:white; }
        .laporan-card{ background:white; border:1px solid #eee; border-radius:15px; padding:15px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
        .badge-menunggu{ background:#f39c12; color:white; padding:4px 10px; border-radius:10px; font-size:12px; }
        .badge-disetujui{ background:#0f8b63; color:white; padding:4px 10px; border-radius:10px; font-size:12px; }
        .badge-ditolak{ background:#c0392b; color:white; padding:4px 10px; border-radius:10px; font-size:12px; }
        .bottom-nav{ position:fixed; bottom:0; width:100%; max-width:430px; background:white; display:flex; justify-content:space-around; padding:15px 0; border-top:1px solid #eee; }
        .nav-item{ color:#999; font-size:14px; text-align:center; text-decoration:none; }
        .active-nav{ color:#4b2ea7; font-weight:bold; }
        .p-4{ padding-bottom:80px!important; }
    </style>
</head>
<body>

<div class="mobile">

    <div class="header">
        <h3>Laporan Peminjaman</h3>
        <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></p>
    </div>

    <div class="p-4">

        <!-- Form Filter -->
        <div class="card-box">
            <form method="GET">

                <div class="mb-3">
                    <input type="text" name="cari" class="form-control"
                        placeholder="Cari nama / lab..."
                        value="<?= htmlspecialchars($cari) ?>">
                </div>

                <div class="mb-3">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="disetujui" <?= $filter_status == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>

                <div class="mb-3">
                    <input type="date" name="tanggal" class="form-control"
                        value="<?= $filter_tanggal ?>">
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-ungu w-100">Filter</button>
                    <a href="laporan.php" class="btn btn-secondary w-100">Reset</a>
                </div>

                <div class="d-flex gap-2 mt-2">
                    <a href="export_excel.php" class="btn btn-success w-100">Export Excel</a>
                    <a href="export_pdf.php" class="btn btn-danger w-100">Export PDF</a>
                </div>

            </form>
        </div>

        <!-- Hasil -->
        <?php if(mysqli_num_rows($data) == 0){ ?>
            <p class="text-secondary">Tidak ada data ditemukan.</p>
        <?php } ?>

        <?php while($d = mysqli_fetch_assoc($data)){ ?>
        <div class="laporan-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <b><?= htmlspecialchars($d['nama']) ?></b>
                    <p class="mb-1 text-secondary" style="font-size:13px;">
                        <?= htmlspecialchars($d['nama_lab']) ?>
                    </p>
                    <p class="mb-0 text-secondary" style="font-size:13px;">
                        📅 <?= $d['tanggal_pinjam'] ?> • 🕐 <?= $d['jam_mulai'] ?>-<?= $d['jam_selesai'] ?>
                    </p>
                </div>
                <span class="badge-<?= $d['status'] ?>"><?= ucfirst($d['status']) ?></span>
            </div>
        </div>
        <?php } ?>

    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">Beranda</a>
        <a href="jadwal.php" class="nav-item">Jadwal</a>
        <a href="laporan.php" class="nav-item active-nav">Laporan</a>
        <a href="kelola.php" class="nav-item">Kelola</a>
        <a href="kelola_user.php" class="nav-item">User</a>
        <a href="../logout.php" class="nav-item">Logout</a>
    </div>

</div>

</body>
</html>