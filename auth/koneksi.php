<?php
$databaseHost = "localhost";
$databaseName = "login";
$databaseUsername = "root";
$databasePassword = "";
$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);

if ($mysqli) {
   
} else {
    echo "koneksi gagal.<br/>";
}
