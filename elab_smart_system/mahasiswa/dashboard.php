<?php
session_start();
include '../koneksi.php';

$lab = mysqli_query($conn,"
SELECT * FROM laboratorium
WHERE status='tersedia'
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Dashboard Mahasiswa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#efefef;
    font-family:Arial;
}

.mobile{
    max-width:430px;
    margin:auto;
    background:white;
    min-height:100vh;
}

.header{
    background:#4b2ea7;
    color:white;
    padding:25px;
    border-bottom-left-radius:20px;
    border-bottom-right-radius:20px;
}

.card-box{
    background:white;
    padding:20px;
    border-radius:20px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

.btn-ungu{
    background:#4b2ea7;
    color:white;
}

</style>

</head>

<body>

<div class="mobile">

<div class="header">

<h3>Dashboard Mahasiswa</h3>

<p>
<?= $_SESSION['nama']; ?>
</p>

</div>

<div class="p-4">

<div class="card-box">

<h5>Ajukan Peminjaman Laboratorium</h5>

<form method="POST" action="simpan.php">

<div class="mb-3">

<label>Laboratorium</label>

<select name="id_lab" class="form-control" required>

<option value="">Pilih Lab</option>

<?php while($d = mysqli_fetch_assoc($lab)) { ?>

<option value="<?= $d['id_lab'] ?>">

<?= $d['nama_lab'] ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">
<label>Tanggal Pinjam</label>

<input type="date"
name="tanggal_pinjam"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Jam Mulai</label>

<input type="time"
name="jam_mulai"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Jam Selesai</label>

<input type="time"
name="jam_selesai"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Keperluan</label>

<textarea
name="keperluan"
class="form-control"
required></textarea>
</div>

<button class="btn btn-ungu w-100">

Ajukan Peminjaman

</button>

</form>

</div>

</div>

</div>

</body>
</html>