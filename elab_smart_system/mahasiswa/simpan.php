<?php
session_start();
include '../koneksi.php';

// Cek session
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa'){
    header("Location: ../login.php");
    exit;
}

// Validasi input
$id_user = (int)$_SESSION['id_user'];
$id_lab = isset($_POST['id_lab']) ? (int)$_POST['id_lab'] : 0;
$tanggal_pinjam = isset($_POST['tanggal_pinjam']) ? trim($_POST['tanggal_pinjam']) : '';
$jam_mulai = isset($_POST['jam_mulai']) ? trim($_POST['jam_mulai']) : '';
$jam_selesai = isset($_POST['jam_selesai']) ? trim($_POST['jam_selesai']) : '';
$keperluan = isset($_POST['keperluan']) ? trim($_POST['keperluan']) : '';

// Cek kalau ada yang kosong
if($id_lab == 0 || $tanggal_pinjam == '' || $jam_mulai == '' || $jam_selesai == '' || $keperluan == ''){
    echo "<script>alert('Semua field harus diisi'); window.location='dashboard.php';</script>";
    exit;
}

// Cek jam mulai harus lebih awal dari jam selesai
if($jam_mulai >= $jam_selesai){
    echo "<script>alert('Jam mulai harus lebih awal dari jam selesai'); window.location='dashboard.php';</script>";
    exit;
}

// Cek bentrok jadwal
$cekBentrok = mysqli_prepare($conn,"
    SELECT * FROM peminjaman
    WHERE id_lab = ?
    AND tanggal_pinjam = ?
    AND status = 'disetujui'
    AND (
        (jam_mulai < ? AND jam_selesai > ?)
        OR (jam_mulai < ? AND jam_selesai > ?)
        OR (jam_mulai >= ? AND jam_selesai <= ?)
    )
");

mysqli_stmt_bind_param(
    $cekBentrok, "isssssss",
    $id_lab,
    $tanggal_pinjam,
    $jam_selesai, $jam_mulai,
    $jam_selesai, $jam_selesai,
    $jam_mulai, $jam_selesai
);

mysqli_stmt_execute($cekBentrok);
$hasilBentrok = mysqli_stmt_get_result($cekBentrok);

if(mysqli_num_rows($hasilBentrok) > 0){
    echo "<script>alert('Jadwal bentrok! Lab sudah dibooking di jam tersebut'); window.location='dashboard.php';</script>";
    exit;
}

// Simpan peminjaman
$stmt = mysqli_prepare($conn,"
    INSERT INTO peminjaman(id_user, id_lab, tanggal_pinjam, jam_mulai, jam_selesai, keperluan, status)
    VALUES(?, ?, ?, ?, ?, ?, 'menunggu')
");

mysqli_stmt_bind_param($stmt, "iissss", $id_user, $id_lab, $tanggal_pinjam, $jam_mulai, $jam_selesai, $keperluan);
mysqli_stmt_execute($stmt);

echo "<script>alert('Peminjaman berhasil diajukan'); window.location='dashboard.php';</script>";
?>