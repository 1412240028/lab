<?php
require_once "_guard.php";
require_once "../koneksi.php";

$aksi = isset($_POST['aksi']) ? trim($_POST['aksi']) : '';
$allowedAksi = ['tambah', 'hapus'];

if (!in_array($aksi, $allowedAksi, true)) {
    header("Location: kelola_user.php?success=Pengguna+berhasil+ditambahkan");
    exit;
}

$allowedRoles = ['admin', 'mahasiswa', 'dosen'];

if ($aksi === 'tambah') {
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $plainPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if ($nama === '' || $email === '' || $plainPassword === '' || $role === '') {
        header("Location: kelola_user.php?error=Semua+field+wajib+diisi");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: kelola_user.php?error=Format+email+tidak+valid");
        exit;
    }

    if (strlen($plainPassword) < 6) {
        header("Location: kelola_user.php?error=Password+minimal+6+karakter");
        exit;
    }

    if (!in_array($role, $allowedRoles, true)) {
        header("Location: kelola_user.php?error=Role+tidak+valid");
        exit;
    }

    $cek = mysqli_prepare($conn, "
        SELECT id_user
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    if (!$cek) {
        header("Location: kelola_user.php?error=Gagal+menyiapkan+validasi+email");
        exit;
    }

    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    $hasil = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil) > 0) {
        header("Location: kelola_user.php?error=Email+sudah+terdaftar");
        exit;
    }

    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO users(nama, email, password, role)
        VALUES(?, ?, ?, ?)
    ");

    if (!$stmt) {
        header("Location: kelola_user.php?error=Gagal+menyiapkan+data+pengguna");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hashedPassword, $role);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: kelola_user.php?error=Gagal+menambahkan+pengguna");
        exit;
    }

    header("Location: kelola_user.php?success=Pengguna+berhasil+ditambahkan");
    exit;
}

if ($aksi === 'hapus') {
    $id_user = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;

    if ($id_user <= 0) {
        header("Location: kelola_user.php?error=User+tidak+valid");
        exit;
    }

    if ($id_user === (int) $_SESSION['id_user']) {
        header("Location: kelola_user.php?error=Tidak+bisa+hapus+akun+sendiri");
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        DELETE FROM users
        WHERE id_user = ?
    ");

    if (!$stmt) {
        header("Location: kelola_user.php?error=Gagal+menyiapkan+hapus+pengguna");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $id_user);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: kelola_user.php?error=Gagal+menghapus+pengguna");
        exit;
    }

    header("Location: kelola_user.php?success=Pengguna+berhasil+dihapus");
    exit;
}
?>