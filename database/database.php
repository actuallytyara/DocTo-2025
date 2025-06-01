<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'login';

$conn = new mysqli($host, $user, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
function fetchArticles($conn) {
    $query = "SELECT title, description, link FROM articles";
    $result = $conn->query($query);
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    return $articles;
}

function fetchCatalog($conn) {
    $query = "SELECT name, description, link FROM catalog";
    $result = $conn->query($query);
    $catalog = [];
    while ($row = $result->fetch_assoc()) {
        $catalog[] = $row;
    }
    return $catalog;
}
?>