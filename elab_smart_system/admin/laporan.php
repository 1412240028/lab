<?php
session_start();
include '../koneksi.php';

$data = mysqli_query($conn,"
SELECT peminjaman.*, users.nama, laboratorium.nama_lab
FROM peminjaman
JOIN users ON peminjaman.id_user = users.id_user
JOIN laboratorium ON peminjaman.id_lab = laboratorium.id_lab
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Laporan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="background:#efefef">

<div class="container mt-4">

<h3>Laporan Peminjaman</h3>

<div class="card p-4">

<table class="table">

<tr>
<th>Nama</th>
<th>Lab</th>
<th>Tanggal</th>
<th>Status</th>
</tr>

<?php while($d = mysqli_fetch_assoc($data)) { ?>

<tr>

<td><?= $d['nama'] ?></td>

<td><?= $d['nama_lab'] ?></td>

<td><?= $d['tanggal_pinjam'] ?></td>

<td><?= $d['status'] ?></td>

</tr>

<?php } ?>

</table>

<a href="dashboard.php" class="btn btn-primary">
Kembali
</a>

</div>

</div>

</body>
</html>