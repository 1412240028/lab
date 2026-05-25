<?php
session_start();

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "elab_smart_system"
);

if(!$conn){
    die("Koneksi database gagal");
}

if(isset($_POST['login'])){

    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));

    $query = mysqli_query($conn,"
    SELECT * FROM users
    WHERE email='$email'
    AND password='$password'
    ");

    $cek = mysqli_num_rows($query);

    if($cek > 0){

        $data = mysqli_fetch_assoc($query);

        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

        if($data['role'] == 'admin'){

            header("Location: admin/dashboard.php");
            exit;

        }elseif($data['role'] == 'mahasiswa'){

            header("Location: mahasiswa/dashboard.php");
            exit;

        }else{

            echo "<script>alert('Role tidak dikenali')</script>";

        }

    }else{

        echo "<script>alert('Login gagal')</script>";

    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Login E-Lab</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#efefef;
    font-family:Arial;
}

.login-box{
    max-width:400px;
    margin:auto;
    margin-top:80px;
    background:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.btn-login{
    background:#4b2ea7;
    color:white;
}

</style>

</head>

<body>

<div class="login-box">

<h3 class="text-center mb-4">
E-Lab Smart System
</h3>

<form method="POST">

<div class="mb-3">

<input
type="email"
name="email"
class="form-control"
placeholder="Masukkan Email"
required>

</div>

<div class="mb-3">

<input
type="password"
name="password"
class="form-control"
placeholder="Masukkan Password"
required>

</div>

<button
type="submit"
name="login"
class="btn btn-login w-100">

Login

</button>

</form>

<div class="mt-4">

<p>
<b>Admin</b><br>
admin@gmail.com<br>
admin123
</p>

<p>
<b>Mahasiswa</b><br>
mahasiswa@gmail.com<br>
123456
</p>

</div>

</div>

</body>
</html>