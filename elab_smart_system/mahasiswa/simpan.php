<?php
require_once "_guard.php";
require_once "../koneksi.php";

$id_user = (int) $_SESSION['id_user'];
$id_lab = isset($_POST['id_lab']) ? (int) $_POST['id_lab'] : 0;
$tanggal_pinjam = isset($_POST['tanggal_pinjam']) ? trim($_POST['tanggal_pinjam']) : '';
$jam_mulai = isset($_POST['jam_mulai']) ? trim($_POST['jam_mulai']) : '';
$jam_selesai = isset($_POST['jam_selesai']) ? trim($_POST['jam_selesai']) : '';
$keperluan = isset($_POST['keperluan']) ? trim($_POST['keperluan']) : '';

if ($id_lab == 0 || $tanggal_pinjam == '' || $jam_mulai == '' || $jam_selesai == '' || $keperluan == '') {
    header("Location: dashboard.php?error=Semua+field+harus+diisi");
    exit;
}

if ($jam_mulai >= $jam_selesai) {
    header("Location: dashboard.php?error=Jam+mulai+harus+lebih+awal+dari+jam+selesai");
    exit;
}

$cekBentrok = mysqli_prepare($conn, "
    SELECT id_peminjaman
    FROM peminjaman
    WHERE id_lab = ?
    AND tanggal_pinjam = ?
    AND status = 'disetujui'
    AND jam_mulai < ?
    AND jam_selesai > ?
    LIMIT 1
");

if (!$cekBentrok) {
    header("Location: dashboard.php?error=Gagal menyiapkan pengecekan jadwal");
    exit;
}

mysqli_stmt_bind_param(
    $cekBentrok,
    "isss",
    $id_lab,
    $tanggal_pinjam,
    $jam_selesai,
    $jam_mulai
);

mysqli_stmt_execute($cekBentrok);
$hasilBentrok = mysqli_stmt_get_result($cekBentrok);

if (mysqli_num_rows($hasilBentrok) > 0) {
    header("Location: dashboard.php?error=Jadwal+bentrok!+Laboratorium+sudah+digunakan+pada/jam+tersebut");
    exit;
}

$stmt = mysqli_prepare($conn, "
    INSERT INTO peminjaman(id_user, id_lab, tanggal_pinjam, jam_mulai, jam_selesai, keperluan, status)
    VALUES(?, ?, ?, ?, ?, ?, 'menunggu')
");

if (!$stmt) {
    header("Location: dashboard.php?error=Gagal menyiapkan data peminjaman");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iissss",
    $id_user,
    $id_lab,
    $tanggal_pinjam,
    $jam_mulai,
    $jam_selesai,
    $keperluan
);

if (!mysqli_stmt_execute($stmt)) {
    header("Location: dashboard.php?error=Peminjaman gagal diajukan");
    exit;
}

header("Location: dashboard.php?success=Peminjaman berhasil diajukan");
exit;
?>