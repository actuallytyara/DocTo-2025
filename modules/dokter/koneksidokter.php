<?php
$host = "localhost";
$user = "root"; 
$password = ""; 
$database = "login";  


$koneksi = mysqli_connect($host, $user, $password, $database);


if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>