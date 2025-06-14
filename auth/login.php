<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman form login</title>
    <link rel="stylesheet" href="../assets/css/style_login_register.css">
</head>

<body>
    <div class="container">
        <div class="kotak_login">
            <p class="tulisan_login">Silahkan login </p>
            <form action="" method="post" role="form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" class="Form_login" placeholder="username"
                        autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" class="Form_login" placeholder="123@email"
                        autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" class="Form_login" placeholder="password"
                        autocomplete="off" required>
                </div>
                
                <div class="paragraph">
                 <p> Belum punya akun?</p> <a href="Register.php">Register</a>
                </div>
                
                <button type="submit" class="tombol_login" name="submit">Login</button>
                
            </form>
           
        </div>
    </div>
</body>

</html>

<?php
include '../database/koneksi.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['username'])) {
    header("Location: berhasil_login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $emails = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $passwords = $_POST['password'];

    $query_mysql = "SELECT * FROM tb_login WHERE email='$emails' AND username='$username'";
    $result = mysqli_query($conn, $query_mysql);
   
    if ($result && $result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);

        if ($row['password'] == $passwords) {
            $_SESSION['user_id'] = $row['ID_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['pengguna'] = $row['pengguna'];
            $_SESSION['email'] = $row['email'];
            
            echo "<script>alert('Login berhasil!'); window.location.href='berhasil_login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Email atau password salah. Silakan coba lagi!');</script>";
        }
    } else {
        echo "<script>alert('Email atau username tidak ditemukan. Silakan coba lagi!');</script>";
    }
}
?>