<?php

// Fungsi untuk membersihkan input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk format tanggal Indonesia
function formatTanggalIndo($tanggal) {
    if (empty($tanggal)) return '-';
    
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    
    if (count($pecahkan) == 3) {
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
    
    return $tanggal;
}

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk mengecek login
function checkLogin() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk mengecek role admin
function checkAdmin() {
    checkLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk mengecek role dokter
function checkDokter() {
    checkLogin();
    if ($_SESSION['role'] !== 'dokter') {
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk generate ID unik
function generateId($prefix = '') {
    return $prefix . date('YmdHis') . rand(100, 999);
}

// Fungsi untuk validasi email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fungsi untuk hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fungsi untuk menghitung umur dari tanggal lahir
function hitungUmur($tanggal_lahir) {
    if (empty($tanggal_lahir)) return 0;
    
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $umur = $sekarang->diff($lahir);
    
    return $umur->y;
}

// Fungsi untuk mengecek stok obat rendah
function cekStokRendah($stok, $min_stok = 10) {
    return $stok <= $min_stok;
}

// Fungsi untuk mengecek obat yang akan expired
function cekAkanExpired($tanggal_expired, $hari = 30) {
    $today = new DateTime();
    $expired_date = new DateTime($tanggal_expired);
    $diff = $today->diff($expired_date);
    
    return ($expired_date > $today && $diff->days <= $hari);
}

// Fungsi untuk mengecek obat yang sudah expired
function cekExpired($tanggal_expired) {
    $today = new DateTime();
    $expired_date = new DateTime($tanggal_expired);
    
    return $expired_date < $today;
}

// Fungsi untuk log aktivitas
function logActivity($user_id, $activity, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $activity, $description]);
    } catch(PDOException $e) {
        // Log error tapi jangan stop aplikasi
        error_log("Log activity error: " . $e->getMessage());
    }
}

// Fungsi untuk mendapatkan nama hari dalam bahasa Indonesia
function getNamaHari($tanggal) {
    $hari = array(
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );
    
    return $hari[date('l', strtotime($tanggal))];
}

// Fungsi untuk pagination
function getPaginationData($current_page, $total_records, $records_per_page = 10) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return array(
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'records_per_page' => $records_per_page,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    );
}

// Fungsi untuk menampilkan alert
function showAlert($type, $message) {
    $icon = array(
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    );
    
    $alert_class = $type == 'error' ? 'danger' : $type;
    
    return "
    <div class='alert alert-{$alert_class} alert-dismissible fade show'>
        <i class='fas {$icon[$type]} me-2'></i>{$message}
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

// Fungsi untuk upload file
function uploadFile($file, $destination = 'uploads/', $allowed_types = array('jpg', 'jpeg', 'png', 'pdf')) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => 'Error upload file');
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return array('success' => false, 'message' => 'Tipe file tidak diizinkan');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        return array('success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)');
    }
    
    $new_filename = generateId('file_') . '.' . $file_extension;
    $upload_path = $destination . $new_filename;
    
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return array('success' => true, 'filename' => $new_filename, 'path' => $upload_path);
    } else {
        return array('success' => false, 'message' => 'Gagal upload file');
    }
}

// Fungsi untuk mengirim email (sederhana)
function sendEmail($to, $subject, $message, $from = 'noreply@docto.com') {
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Fungsi untuk generate laporan sederhana
function generateReport($data, $title, $headers) {
    $html = "
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { color: #333; }
    </style>
    <h2>$title</h2>
    <p>Tanggal: " . date('d-m-Y H:i:s') . "</p>
    <table>
        <thead><tr>";
    
    foreach ($headers as $header) {
        $html .= "<th>$header</th>";
    }
    
    $html .= "</tr></thead><tbody>";
    
    foreach ($data as $row) {
        $html .= "<tr>";
        foreach ($row as $cell) {
            $html .= "<td>$cell</td>";
        }
        $html .= "</tr>";
    }
    
    $html .= "</tbody></table>";
    
    return $html;
}

// Fungsi untuk backup database (sederhana)
function backupDatabase($pdo, $filename = null) {
    if (!$filename) {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    }
    
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $backup = '';
        
        foreach ($tables as $table) {
            $backup .= "\n-- Table: $table\n";
            $backup .= "DROP TABLE IF EXISTS `$table`;\n";
            
            $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
            $backup .= $create['Create Table'] . ";\n\n";
            
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
                
                $values = array();
                foreach ($rows as $row) {
                    $escaped = array_map(function($value) use ($pdo) {
                        return $pdo->quote($value);
                    }, array_values($row));
                    $values[] = "(" . implode(", ", $escaped) . ")";
                }
                
                $backup .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        file_put_contents("backups/$filename", $backup);
        return array('success' => true, 'filename' => $filename);
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}
?>