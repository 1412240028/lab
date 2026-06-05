<?php
require_once "_guard.php";
require_once "../koneksi.php";

$aksi = isset($_POST['aksi']) ? trim($_POST['aksi']) : '';
$allowedAksi = ['tambah', 'edit', 'hapus'];

if (!in_array($aksi, $allowedAksi, true)) {
    header("Location: kelola.php?success=Laboratorium+berhasil+ditambahkan");
    exit;
}

$allowedStatus = ['tersedia', 'tidak tersedia'];

if ($aksi === 'tambah' || $aksi === 'edit') {
    $nama_lab = isset($_POST['nama_lab']) ? trim($_POST['nama_lab']) : '';
    $kapasitas = isset($_POST['kapasitas']) ? (int) $_POST['kapasitas'] : 0;
    $lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($nama_lab === '' || $kapasitas <= 0 || $lokasi === '' || !in_array($status, $allowedStatus, true)) {
        header("Location: kelola.php?error=Semua field harus diisi dengan benar");
        exit;
    }
}

if ($aksi === 'tambah') {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO laboratorium(nama_lab, kapasitas, lokasi, status)
        VALUES(?, ?, ?, ?)
    ");

    if (!$stmt) {
        header("Location: kelola.php?error=Gagal menyiapkan data laboratorium");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "siss", $nama_lab, $kapasitas, $lokasi, $status);
    mysqli_stmt_execute($stmt);

    header("Location: kelola.php?success=Laboratorium+berhasil+ditambahkan");
    exit;
}

if ($aksi === 'edit') {
    $id_lab = isset($_POST['id_lab']) ? (int) $_POST['id_lab'] : 0;

    if ($id_lab <= 0) {
        header("Location: kelola.php?error=ID laboratorium tidak valid");
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        UPDATE laboratorium
        SET nama_lab = ?, kapasitas = ?, lokasi = ?, status = ?
        WHERE id_lab = ?
    ");

    if (!$stmt) {
        header("Location: kelola.php?error=Gagal menyiapkan data laboratorium");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sissi", $nama_lab, $kapasitas, $lokasi, $status, $id_lab);
    mysqli_stmt_execute($stmt);

    header("Location: kelola.php?success=Laboratorium+berhasil+diubah");
    exit;
}

if ($aksi === 'hapus') {
    $id_lab = isset($_POST['id_lab']) ? (int) $_POST['id_lab'] : 0;

    if ($id_lab <= 0) {
        header("Location: kelola.php?error=ID laboratorium tidak valid");
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        DELETE FROM laboratorium
        WHERE id_lab = ?
    ");

    if (!$stmt) {
        header("Location: kelola.php?error=Gagal menyiapkan data laboratorium");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $id_lab);
    mysqli_stmt_execute($stmt);

    header("Location: kelola.php?success=Laboratorium+berhasil+dihapus");
    exit;
}
?>