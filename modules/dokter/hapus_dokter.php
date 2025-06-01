<?php
include 'koneksidokter.php';
session_start();

$id = mysqli_real_escape_string($koneksi, $_GET['id']);


$id = $_GET['id'];
$query = "DELETE FROM dokter WHERE ID_dokter = '$id'";

if(mysqli_query($koneksi, $query)){
    header("Location: indexdokter.php");
    exit;
} else {
    echo "Error: " . mysqli_error($koneksi);
    echo "<br><a href='indexdokter.php'>Kembali ke daftar dokter</a>";
}
?>