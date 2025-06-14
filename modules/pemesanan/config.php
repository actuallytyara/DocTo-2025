<?php
$host = 'localhost';
$dbname = 'login';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function checkAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['pengguna'] != 'admin') {
        header('Location: login.php');
        exit();
    }
}

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('debugCheckout')) {
    function debugCheckout() {
        echo "<script>console.log('POST Data:', " . json_encode($_POST) . ");</script>";
        echo "<script>console.log('SESSION User ID:', '" . $_SESSION['user_id'] . "');</script>";
    }
}

if (!function_exists('generateOrderNumber')) {
    function generateOrderNumber() {
        return date('Ymd') . rand(1000, 9999);
    }
}

if (!function_exists('getStatusBadgeClass')) {
    function getStatusBadgeClass($status) {
        switch ($status) {
            case 'menunggu': return 'warning';
            case 'dikonfirmasi': return 'info';
            case 'dikirim': return 'primary';
            case 'selesai': return 'success';
            case 'dibatalkan': return 'danger';
            default: return 'secondary';
        }
    }
}

?>