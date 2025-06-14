<?php
$host = 'localhost';
$dbname = 'login';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

function getDashboardStats($pdo) {
    $stats = [];
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_login WHERE pengguna = 'user'");
        $stats['total_users'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_login WHERE pengguna = 'admin'");
        $stats['total_admins'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM dokter");
        $stats['total_dokter'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM rekam_medis");
        $stats['total_rekam_medis'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM rekam_medis WHERE DATE(Tanggal) = CURDATE()");
        $stats['rekam_medis_hari_ini'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pemesanan");
        $stats['total_pemesanan'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pemesanan WHERE DATE(tanggal_pemesanan) = CURDATE()");
        $stats['pemesanan_hari_ini'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM obat");
        $stats['total_obat'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM obat WHERE stok_obat < 10");
        $stats['obat_stok_menipis'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM resep_obat");
        $stats['total_resep'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM resep_obat WHERE DATE(tanggal_resep) = CURDATE()");
        $stats['resep_hari_ini'] = $stmt->fetch()['total'];
        
    } catch(PDOException $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
        $stats = [
            'total_users' => 0,
            'total_admins' => 0,
            'total_dokter' => 0,
            'total_rekam_medis' => 0,
            'rekam_medis_hari_ini' => 0,
            'total_pemesanan' => 0,
            'pemesanan_hari_ini' => 0,
            'total_obat' => 0,
            'obat_stok_menipis' => 0,
            'total_resep' => 0,
            'resep_hari_ini' => 0
        ];
    }
    
    return $stats;
}

function getRecentActivities($pdo, $limit = 10) {
    $activities = [];
    
    try {
        $stmt = $pdo->prepare("
            (SELECT 'user_registration' as type, 
                    CONCAT('Pengguna baru: ', username) as description,
                    created_at as activity_time
             FROM tb_login 
             WHERE created_at IS NOT NULL
             ORDER BY created_at DESC LIMIT 5)
            
            UNION ALL
            
            (SELECT 'pemesanan' as type,
                    CONCAT('Pemesanan baru: ', nomor_pemesanan) as description,
                    created_at as activity_time
             FROM pemesanan
             ORDER BY created_at DESC LIMIT 5)
            
            UNION ALL
            
            (SELECT 'rekam_medis' as type,
                    CONCAT('Rekam medis baru untuk pasien ID: ', ID_user) as description,
                    created_at as activity_time
             FROM rekam_medis
             ORDER BY created_at DESC LIMIT 5)
            
            ORDER BY activity_time DESC
            LIMIT :limit
        ");
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Error getting recent activities: " . $e->getMessage());
    }
    
    return $activities;
}

function getLowStockMedicines($pdo, $threshold = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT nama_obat, stok_obat, jenis_obat 
            FROM obat 
            WHERE stok_obat < :threshold 
            ORDER BY stok_obat ASC
        ");
        $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting low stock medicines: " . $e->getMessage());
        return [];
    }
}

function getPendingOrders($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.nomor_pemesanan, p.total_harga, p.tanggal_pemesanan, 
                   u.username as customer_name
            FROM pemesanan p
            JOIN tb_login u ON p.ID_user = u.ID_user
            WHERE p.status_pemesanan = 'pending'
            ORDER BY p.tanggal_pemesanan DESC
            LIMIT 10
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting pending orders: " . $e->getMessage());
        return [];
    }
}
?>