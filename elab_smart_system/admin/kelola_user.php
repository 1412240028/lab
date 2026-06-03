<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

$users = mysqli_query($conn,"SELECT * FROM users ORDER BY role, nama ASC");
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#efefef; font-family:Arial; }
        .mobile{ max-width:430px; margin:auto; background:white; min-height:100vh; }
        .header{ background:#4b2ea7; color:white; padding:25px; border-bottom-left-radius:20px; border-bottom-right-radius:20px; }
        .card-box{ background:white; padding:20px; border-radius:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
        .btn-ungu{ background:#4b2ea7; color:white; }
        .badge-admin{ background:#4b2ea7; color:white; padding:3px 8px; border-radius:8px; font-size:11px; }
        .badge-mahasiswa{ background:#0f8b63; color:white; padding:3px 8px; border-radius:8px; font-size:11px; }
        .bottom-nav{ position:fixed; bottom:0; width:100%; max-width:430px; background:white; display:flex; justify-content:space-around; padding:15px 0; border-top:1px solid #eee; }
        .nav-item{ color:#999; font-size:14px; text-align:center; text-decoration:none; }
        .active-nav{ color:#4b2ea7; font-weight:bold; }
        .p-4{ padding-bottom:80px!important; }
    </style>
</head>
<body>

<div class="mobile">

    <div class="header">
        <h3>Kelola User</h3>
        <p class="mb-0"><?= htmlspecialchars($_SESSION['nama']) ?></p>
    </div>

    <div class="p-4">

        <?php if($error){ ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <!-- Form Tambah User -->
        <div class="card-box">
            <h5>Tambah User</h5>
            <form method="POST" action="kelola_user_proses.php">
                <input type="hidden" name="aksi" value="tambah">

                <div class="mb-3">
                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button class="btn btn-ungu w-100">Tambah User</button>
            </form>
        </div>

        <!-- List User -->
        <div class="card-box">
            <h5>Daftar User</h5>
            <?php while($u = mysqli_fetch_assoc($users)){ ?>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <b><?= htmlspecialchars($u['nama']) ?></b>
                    <p class="mb-0 text-secondary" style="font-size:13px;">
                        <?= htmlspecialchars($u['email']) ?>
                    </p>
                    <span class="badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                </div>
                <div>
                    <?php if($u['id_user'] != $_SESSION['id_user']){ ?>
                    <form method="POST" action="kelola_user_proses.php"
                        onsubmit="return confirm('Hapus user ini?')">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                    <?php } else { ?>
                        <span class="text-secondary" style="font-size:12px;">Kamu</span>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>

    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">Beranda</a>
        <a href="jadwal.php" class="nav-item">Jadwal</a>
        <a href="laporan.php" class="nav-item">Laporan</a>
        <a href="kelola.php" class="nav-item">Kelola</a>
        <a href="kelola_user.php" class="nav-item active-nav">User</a>
        <a href="../logout.php" class="nav-item">Logout</a>
    </div>

</div>

</body>
</html>