<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Register</title>
    <link rel="stylesheet" href="../assets/css/style_login_register.css">
</head>

<body>
    <div class="container">
        <div class="kotak_register">
            <h1>Register</h1>
            <form action="Register.php" method="post" name="form1">
                <table>

                    <tr>
                        <td>Username</td>
                        <td><input type="text" name="username" placeholder="Isi Username anda" required></td>
                    </tr>

                    <tr>
                        <td>Password</td>
                        <td><input type="Password" name="password" placeholder="Isi Password anda" required></td>
                    </tr>
                    
                    <tr>
                        <td>Pengguna</td>
                        <td>
                            <select name="pengguna" id="pengguna" required>
                                <option disabled selected> Pilih </option>>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>email</td>
                        <td><input type="text" name="email" placeholder="Isi email anda" required></td>
                    </tr>
                    <tr>
                         <td><button class="tombol_login" name="submit"> Daftar</button>
                         <div style="display: flex; align-items: center; gap: 15px; margin-top: 5px;">
                         <a href="/projekukl/modules/dokter/indexdokter.php"
                         style="font-size: 14px; color: #2c6e63; text-decoration: none;">Daftar sebagai dokter?</a>
                    </tr>

                    
</div>


<?php 
session_start();
 
if (isset($_SESSION['username'])) { 
    header("Location: berhasil_login.php");
    exit();
}
 
if (isset($_POST['submit'])) {
    $usernames = $_POST['username'];
    $passwords = $_POST['password'];
    $penggunas = $_POST['pengguna'];
    $emails = $_POST['email']; 
    
    include "koneksi.php";
    
    // Insert user data into table
    $result = mysqli_query($mysqli, "INSERT INTO tb_login (username, password, pengguna, email) 
             VALUES ('$usernames', '$passwords', '$penggunas', '$emails')");
    
    // Redirect to login page after successful registration
    header("location:login.php");
    exit();
}
?>