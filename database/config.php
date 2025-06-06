<?php 
$databasehost = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "login"; 
$conn = new mysqli($databasehost, $username, $password, $database); 
if ($conn->connect_error) { 
 die("Koneksi gagal: " . $conn->connect_error); 
} 

