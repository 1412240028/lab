<?php
session_start();
include '../koneksi.php';

// Cek session
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

// Validasi input
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

$allowed_status = ['disetujui', 'ditolak'];

if($id == 0 || !in_array($status, $allowed_status)){
    header("Location: dashboard.php");
    exit;
}

// Update pakai prepared statement
$stmt = mysqli_prepare($conn,"
    UPDATE peminjaman
    SET status=?, dibaca=0
    WHERE id_peminjaman=?
");

mysqli_stmt_bind_param($stmt, "si", $status, $id);
mysqli_stmt_execute($stmt);

header("Location: dashboard.php");
exit;
?>