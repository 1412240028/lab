<?php
session_start();
include '../koneksi.php';

$data = mysqli_query($conn,"
SELECT * FROM laboratorium
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Jadwal Lab</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="background:#efefef">

<div class="container mt-4">

<h3>Jadwal Laboratorium</h3>

<div class="card p-4">

<table class="table">

<tr>
<th>Nama Lab</th>
<th>Status</th>
<th>Lokasi</th>
</tr>

<?php while($d = mysqli_fetch_assoc($data)) { ?>

<tr>
<td><?= $d['nama_lab'] ?></td>
<td><?= $d['status'] ?></td>
<td><?= $d['lokasi'] ?></td>
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