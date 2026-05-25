<?php
session_start();
include '../koneksi.php';

$id_user = $_SESSION['id_user'];

$id_lab = $_POST['id_lab'];
$tanggal_pinjam = $_POST['tanggal_pinjam'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$keperluan = $_POST['keperluan'];

mysqli_query($conn,"
INSERT INTO peminjaman(
    id_user,
    id_lab,
    tanggal_pinjam,
    jam_mulai,
    jam_selesai,
    keperluan,
    status
)
VALUES(
    '$id_user',
    '$id_lab',
    '$tanggal_pinjam',
    '$jam_mulai',
    '$jam_selesai',
    '$keperluan',
    'menunggu'
)
");

echo "
<script>
alert('Peminjaman berhasil diajukan');
window.location='dashboard.php';
</script>
";
?>