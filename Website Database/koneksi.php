<?php
$databasehost = "localhost";
$login = "login";
$username = "root";
$password = "";
$mysqli = mysqli_connect ($databasehost, $username, $password, $login );

if ($mysqli) {
    
} else {
    echo "koneksi gagal.<br/>";
}
