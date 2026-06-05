<?php
require_once "_guard.php";
require_once "../koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php?error=Akses+tidak+valid");
    exit;
}

$id = isset($_POST['id_peminjaman']) ? (int) $_POST['id_peminjaman'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

$allowedStatus = ['disetujui', 'ditolak'];

if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
    header("Location: dashboard.php?error=Parameter+tidak+valid");
    exit;
}

$stmt = mysqli_prepare($conn, "
    UPDATE peminjaman
    SET status = ?, dibaca = 0
    WHERE id_peminjaman = ?
    AND status = 'menunggu'
");

if (!$stmt) {
    header("Location: dashboard.php?error=Gagal menyiapkan data peminjaman");
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $status, $id);
mysqli_stmt_execute($stmt);

header("Location: dashboard.php?success=Peminjaman+berhasil+diupdate");
exit;
?>