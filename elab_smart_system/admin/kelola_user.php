<?php
session_start();
require_once "_guard.php";
require_once "../koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role, nama ASC");
$error = isset($_GET['error']) ? $_GET['error'] : '';

$totalUser = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$totalAdmin = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='admin'"));
$totalMahasiswa = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='mahasiswa'"));
$totalDosen = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='dosen'"));

function getInitial($name)
{
    return strtoupper(substr($name, 0, 2));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Kelola User - E-Lab Smart System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- E-Lab UI -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <script>
        document.querySelector('select[name="role"]').addEventListener('change', function () {
            document.getElementById('field-nim').style.display = this.value === 'mahasiswa' ? 'block' : 'none';
            document.getElementById('field-nip').style.display = this.value === 'dosen' ? 'block' : 'none';
        });
    </script>
</head>

<body>

    <main class="app-shell">
        <section class="app-container">

            <!-- Header -->
            <header class="app-header admin">
                <div class="app-header-content">
                    <h1 class="app-title">Kelola User</h1>
                    <p class="app-subtitle">
                        <?= htmlspecialchars($_SESSION['nama']) ?> • Manajemen akun pengguna sistem
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

                <!-- Statistik -->
                <div class="section-label">Ringkasan User</div>

                <div class="stat-grid">
                    <div class="stat-box admin">
                        <div class="stat-number purple"><?= $totalUser ?></div>
                        <div class="stat-text">Total user</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number warning"><?= $totalAdmin ?></div>
                        <div class="stat-text">Admin</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number primary"><?= $totalMahasiswa ?></div>
                        <div class="stat-text">Mahasiswa</div>
                    </div>

                    <div class="stat-box admin">
                        <div class="stat-number success"><?= $totalDosen ?></div>
                        <div class="stat-text">Dosen</div>
                    </div>
                </div>

                <div class="user-layout mt-4">

                    <!-- Form Tambah User -->
                    <div>
                        <div class="user-info-box">
                            <h2>Tambah Pengguna</h2>
                            <p>
                                Buat akun baru untuk mahasiswa, dosen, atau admin agar bisa mengakses E-Lab Smart
                                System.
                            </p>
                        </div>

                        <div class="user-form-panel">
                            <h2 class="panel-title">Form Tambah User</h2>
                            <p class="panel-desc">
                                Pastikan email belum terdaftar agar proses penambahan user berhasil.
                            </p>

                            <form method="POST" action="kelola_user_proses.php">

                                <input type="hidden" name="aksi" value="tambah">

                                <div class="input-group-modern">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control"
                                        placeholder="Masukkan nama pengguna" required>
                                </div>

                                <div class="input-group-modern">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="Masukkan email pengguna" required>
                                </div>

                                <div class="input-group-modern" id="field-nim" style="display:none;">
                                    <label>NIM</label>
                                    <input type="text" name="nim" class="form-control"
                                        placeholder="Masukkan NIM mahasiswa">
                                </div>

                                <div class="input-group-modern" id="field-nip" style="display:none;">
                                    <label>NIP</label>
                                    <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP dosen">
                                </div>
                                <div class="input-group-modern">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Buat password awal" required>
                                </div>

                                <div class="input-group-modern">
                                    <label>Role</label>
                                    <select name="role" class="form-select" required>
                                        <option value="mahasiswa">Mahasiswa</option>
                                        <option value="dosen">Dosen</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>

                                <button class="btn btn-admin-primary w-100">
                                    Tambah User
                                </button>

                            </form>
                        </div>
                    </div>

                    <!-- List User -->
                    <div>
                        <div class="section-label mt-0">Daftar User</div>

                        <?php if (mysqli_num_rows($users) == 0) { ?>
                            <div class="empty-state">
                                Belum ada data user yang tersedia.
                            </div>
                        <?php } ?>

                        <div class="user-list">
                            <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                                <?php
                                $role = strtolower($u['role']);
                                $roleClass = in_array($role, ['admin', 'mahasiswa', 'dosen']) ? $role : 'mahasiswa';
                                ?>

                                <div class="user-card-modern">
                                    <div class="user-card-top">
                                        <div class="user-identity">
                                            <div class="user-avatar-mini">
                                                <?= htmlspecialchars(getInitial($u['nama'])) ?>
                                            </div>

                                            <div>
                                                <h3 class="user-name">
                                                    <?= htmlspecialchars($u['nama']) ?>
                                                </h3>

                                                <p class="user-email">
                                                    <?= htmlspecialchars($u['email']) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <span class="role-pill <?= htmlspecialchars($roleClass) ?>">
                                            <?= htmlspecialchars(ucfirst($u['role'])) ?>
                                        </span>
                                    </div>

                                    <div class="user-actions">
                                        <?php if ($u['id_user'] != $_SESSION['id_user']) { ?>
                                            <form method="POST" action="kelola_user_proses.php"
                                                onsubmit="return confirm('Hapus user ini? Aksi ini tidak bisa dibatalkan.')">

                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id_user"
                                                    value="<?= htmlspecialchars($u['id_user']) ?>">

                                                <button class="btn-user-delete">
                                                    Hapus User
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <span class="current-user-label">
                                                Akun kamu
                                            </span>
                                        <?php } ?>
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