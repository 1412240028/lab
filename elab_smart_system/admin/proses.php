<?php
include '../koneksi.php';

$id = $_GET['id'];
$status = $_GET['status'];

mysqli_query($conn,"
UPDATE peminjaman
SET status='$status'
WHERE id_peminjaman='$id'
");

header('Location: dashboard.php');
?>