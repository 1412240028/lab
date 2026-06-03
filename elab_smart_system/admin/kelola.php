<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($conn,"SELECT * FROM laboratorium");

// Ambil data lab untuk edit jika ada id_edit
$editData = null;
if(isset($_GET['edit'])){
    $id_edit = (int)$_GET['edit'];
    $q = mysqli_prepare($conn,"SELECT * FROM laboratorium WHERE id_lab=?");
    mysqli_stmt_bind_param($q, "i", $id_edit);
    mysqli_stmt_execute($q);
    $editData = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Lab</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#efefef; font-family:Arial; }
        .mobile{ max-width:430px; margin:auto; background:white; min-height:100vh; }
        .header{ background:#4b2ea7; color:white; padding:25px; border-bottom-left-radius:20px; border-bottom-right-radius:20px; }
        .card-box{ background:white; padding:20px; border-radius:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
        .btn-ungu{ background:#4b2ea7; color:white; }
        .bottom-nav{ position:fixed; bottom:0; width:100%; max-width:430px; background:white; display:flex; justify-content:space-around; padding:15px 0; border-top:1px solid #eee; }
        .nav-item{ color:#999; font-size:14px; text-align:center; text-decoration:none; }
        .active-nav{ color:#4b2ea7; font-weight:bold; }
        .p-4{ padding-bottom:80px!important; }
    </style>
</head>
<body>

<div class="mobile">

    <div class="header">
        <h3>Kelola Laboratorium</h3>
        <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></p>
    </div>

    <div class="p-4">

        <!-- Form Tambah / Edit -->
        <div class="card-box">
            <h5><?= $editData ? 'Edit' : 'Tambah' ?> Laboratorium</h5>
            <form method="POST" action="kelola_proses.php">

                <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
                <?php if($editData){ ?>
                    <input type="hidden" name="id_lab" value="<?= $editData['id_lab'] ?>">
                <?php } ?>

                <div class="mb-3">
                    <label>Nama Lab</label>
                    <input type="text" name="nama_lab" class="form-control"
                        value="<?= $editData ? htmlspecialchars($editData['nama_lab']) : '' ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label>Kapasitas</label>
                    <input type="number" name="kapasitas" class="form-control"
                        value="<?= $editData ? $editData['kapasitas'] : '' ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control"
                        value="<?= $editData ? htmlspecialchars($editData['lokasi']) : '' ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="tersedia" <?= ($editData && $editData['status'] == 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                        <option value="tidak tersedia" <?= ($editData && $editData['status'] == 'tidak tersedia') ? 'selected' : '' ?>>Tidak Tersedia</option>
                    </select>
                </div>

                <button class="btn btn-ungu w-100">
                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Lab' ?>
                </button>

                <?php if($editData){ ?>
                    <a href="kelola.php" class="btn btn-secondary w-100 mt-2">Batal</a>
                <?php } ?>

            </form>
        </div>

        <!-- List Lab -->
        <div class="card-box">
            <h5>Daftar Laboratorium</h5>
            <?php while($d = mysqli_fetch_assoc($data)){ ?>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <b><?= htmlspecialchars($d['nama_lab']) ?></b>
                    <p class="mb-0 text-secondary" style="font-size:13px;">
                        <?= $d['kapasitas'] ?> kursi • <?= htmlspecialchars($d['lokasi']) ?>
                    </p>
                    <p class="mb-0 text-secondary" style="font-size:13px;">
                        <?= ucfirst($d['status']) ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="kelola.php?edit=<?= $d['id_lab'] ?>"
                        class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="kelola_proses.php"
                        onsubmit="return confirm('Hapus lab ini?')">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id_lab" value="<?= $d['id_lab'] ?>">
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
            <?php } ?>
        </div>

    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">Beranda</a>
        <a href="jadwal.php" class="nav-item">Jadwal</a>
        <a href="laporan.php" class="nav-item">Laporan</a>
        <a href="kelola.php" class="nav-item active-nav">Kelola</a>
        <a href="kelola_user.php" class="nav-item">User</a>
        <a href="../logout.php" class="nav-item">Logout</a>
    </div>

</div>

</body>
</html>