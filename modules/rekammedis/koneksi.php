<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'login';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $test = $pdo->query("SELECT 1");
    if (!$test) {
        throw new Exception("Test query gagal");
    }
    
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (empty($data)) return '';
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatTanggalIndo')) {
    function formatTanggalIndo($tanggal) {
        if (empty($tanggal)) return '-';
        
        $bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        
        $pecahkan = explode('-', $tanggal);
        
        if (count($pecahkan) == 3 && checkdate($pecahkan[1], $pecahkan[2], $pecahkan[0])) {
            return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
        }
        
        return $tanggal; 
    }
}

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        if (empty($angka) || !is_numeric($angka)) return 'Rp 0';
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('validateDate')) {
    function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
?>