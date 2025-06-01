<?php
session_start();
ob_start(); 

include "koneksi.php";


$username = mysqli_real_escape_string($mysqli, $_POST['Username']);
$password = mysqli_real_escape_string($mysqli, $_POST['password']);


$query = $mysqli->prepare("SELECT * FROM tb_login WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    if (password_verify($password, $data['password'])) {
      
        $_SESSION['username'] = $username;
        $_SESSION['pengguna'] = $data['pengguna'];

        if ($data['pengguna'] == "user") {
            header("location:user.php");
            exit; 
        } elseif ($data['pengguna'] == "admin") {
            header("location:admin.php");
            exit; 
        } else {
            
            header("location:index.php?pesan=unknown_pengguna");
            exit;
        }
    } else {
        
        header("location:index.php?pesan=wrong_password");
        exit;
    }
} else {
    // Terakhir, Username tidak ditemukan
    header("location:index.php?pesan=not_found");
    exit;
}