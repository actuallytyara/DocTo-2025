<?php
require_once 'config.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;



try { 
    $query_pemesanan = "SELECT p.*, u.username, u.email 
                       FROM pemesanan p 
                       JOIN tb_login u ON p.ID_user = u.ID_user 
                       WHERE p.ID_pemesanan = :id_pemesanan";
     
    if ($_SESSION['pengguna'] != 'admin') {
        $query_pemesanan .= " AND p.ID_user = :user_id";
    }
    
    $stmt_pemesanan = $pdo->prepare($query_pemesanan);
    $stmt_pemesanan->bindParam(':id_pemesanan', $id_pemesanan, PDO::PARAM_INT);
    
    if ($_SESSION['pengguna'] != 'admin') {
        $stmt_pemesanan->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    }
    
    $stmt_pemesanan->execute();
    $pemesanan = $stmt_pemesanan->fetch(PDO::FETCH_ASSOC);
    
    
    $query_detail = "SELECT dp.*, o.nama_obat, o.jenis_obat, o.deskripsi 
                    FROM detail_pemesanan dp 
                    JOIN obat o ON dp.ID_obat = o.ID_obat 
                    WHERE dp.ID_pemesanan = :id_pemesanan 
                    ORDER BY dp.ID_detail";
    
    $stmt_detail = $pdo->prepare($query_detail);
    $stmt_detail->bindParam(':id_pemesanan', $id_pemesanan, PDO::PARAM_INT);
    $stmt_detail->execute();
    $detail_items = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
 
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
 
function formatTanggal($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00 00:00:00') {
        return 'Tanggal tidak valid';
    }
    return date('d/m/Y H:i', strtotime($tanggal));
}
 
function getStatusBadge($status) { 
    if (empty($status)) {
        $status = 'pending';
    }
    
    $badges = [
        'pending' => 'badge-warning',
        'dikonfirmasi' => 'badge-info', 
        'diproses' => 'badge-primary',
        'dikirim' => 'badge-success',
        'selesai' => 'badge-success',
        'dibatalkan' => 'badge-danger'
    ];
    
    $class = isset($badges[$status]) ? $badges[$status] : 'badge-secondary';
    return "<span class='badge $class'>" . ucfirst($status) . "</span>";
}
 
if ($_SESSION['pengguna'] == 'admin' && isset($_POST['update_status'])) {
    try {
        $new_status = $_POST['status_pemesanan'];
        $update_query = "UPDATE pemesanan SET status_pemesanan = :status, updated_at = CURRENT_TIMESTAMP WHERE ID_pemesanan = :id";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        $update_stmt->bindParam(':id', $id_pemesanan, PDO::PARAM_INT);
        $update_stmt->execute();
         
        header("Location: detail_pemesanan.php?id=$id_pemesanan&success=Status berhasil diupdate");
        exit();
    } catch(PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}
 
if (empty($pemesanan['status_pemesanan'])) {
    $pemesanan['status_pemesanan'] = 'pending';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        

        
        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-row {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #212529;
        }
        
        .item-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .total-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
        
        .btn-secondary {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
        
        .badge {
            padding: 8px 12px;
            font-size: 0.9em;
            border-radius: 20px;
        }
        
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .badge-primary { background-color: #007bff; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        
        .status-update-form {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body> 
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-prescription-bottle-alt me-2"></i>DocTo
            </a>
            <div class="navbar-nav ms-auto">
                <?php if ($_SESSION['pengguna'] == 'admin'): ?>
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                <?php endif; ?>
                <a class="nav-link" href="riwayat_pemesanan.php">
                    <i class="fas fa-history me-1"></i>Riwayat
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container"> 
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
 
        <div class="detail-card">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="text-primary mb-0">
                        <i class="fas fa-file-alt me-2"></i>Detail Pemesanan
                    </h2>
                    <p class="text-muted mb-0">Nomor: <?= htmlspecialchars($pemesanan['nomor_pemesanan'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <?= getStatusBadge($pemesanan['status_pemesanan']) ?>
                </div>
            </div>
        </div>

        <div class="row"> 
            <div class="col-lg-8">
                <div class="detail-card">
                    <h4 class="section-title">
                        <i class="fas fa-info-circle me-2"></i>Informasi Pemesanan
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Tanggal Pemesanan</div>
                                <div class="info-value">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?= formatTanggal($pemesanan['tanggal_pemesanan']) ?>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Nama Pemesan</div>
                                <div class="info-value">
                                    <i class="fas fa-user me-2"></i>
                                    <?= htmlspecialchars($pemesanan['username'] ?? 'N/A') ?>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?= htmlspecialchars($pemesanan['email'] ?? 'N/A') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Nomor Telepon</div>
                                <div class="info-value">
                                    <i class="fas fa-phone me-2"></i>
                                    <?= htmlspecialchars($pemesanan['nomor_telepon'] ?? 'N/A') ?>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Metode Pembayaran</div>
                                <div class="info-value">
                                    <i class="fas fa-credit-card me-2"></i>
                                    <?= !empty($pemesanan['metode_pembayaran']) ? ucfirst($pemesanan['metode_pembayaran']) : 'Belum dipilih' ?>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Status Pembayaran</div>
                                <div class="info-value">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <span class="badge <?= ($pemesanan['status_pembayaran'] ?? 'belum_bayar') == 'sudah_bayar' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $pemesanan['status_pembayaran'] ?? 'belum_bayar')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Alamat Pengiriman</div>
                        <div class="info-value">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?= htmlspecialchars($pemesanan['alamat_pengiriman'] ?? 'N/A') ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($pemesanan['catatan'])): ?>
                        <div class="info-row">
                            <div class="info-label">Catatan</div>
                            <div class="info-value">
                                <i class="fas fa-sticky-note me-2"></i>
                                <?= htmlspecialchars($pemesanan['catatan']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
 
                <div class="detail-card">
                    <h4 class="section-title">
                        <i class="fas fa-pills me-2"></i>Item Pemesanan
                    </h4>
                    
                    <?php if (!empty($detail_items)): ?>
                        <?php foreach ($detail_items as $item): ?>
                            <div class="item-card">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1 text-primary"><?= htmlspecialchars($item['nama_obat'] ?? 'N/A') ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i><?= htmlspecialchars($item['jenis_obat'] ?? 'N/A') ?>
                                        </small>
                                        <?php if (!empty($item['deskripsi'])): ?>
                                            <p class="mb-0 mt-2 small"><?= htmlspecialchars($item['deskripsi']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="info-label">Jumlah</div>
                                        <span class="badge badge-primary"><?= (int)($item['jumlah'] ?? 0) ?></span>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="info-label">Harga Satuan</div>
                                        <div><?= formatRupiah($item['harga_satuan'] ?? 0) ?></div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="info-label">Subtotal</div>
                                        <div class="fw-bold text-success"><?= formatRupiah($item['subtotal'] ?? 0) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Tidak ada item dalam pemesanan ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
 
            <div class="col-lg-4"> 
                <div class="detail-card total-section">
                    <h4><i class="fas fa-calculator me-2"></i>Total Pemesanan</h4>
                    <h2 class="mb-0"><?= formatRupiah($pemesanan['total_harga'] ?? 0) ?></h2>
                </div>
 
                <?php if ($_SESSION['pengguna'] == 'admin'): ?>
                    <div class="detail-card">
                        <h5 class="section-title">
                            <i class="fas fa-edit me-2"></i>Update Status
                        </h5>
                        
                        <form method="POST" class="status-update-form">
                            <div class="mb-3">
                                <label class="form-label">Status Pemesanan</label>
                                <select name="status_pemesanan" class="form-select" required>
                                    <option value="pending" <?= ($pemesanan['status_pemesanan'] ?? 'pending') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="dikonfirmasi" <?= ($pemesanan['status_pemesanan'] ?? '') == 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                    <option value="diproses" <?= ($pemesanan['status_pemesanan'] ?? '') == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                    <option value="dikirim" <?= ($pemesanan['status_pemesanan'] ?? '') == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                    <option value="selesai" <?= ($pemesanan['status_pemesanan'] ?? '') == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="dibatalkan" <?= ($pemesanan['status_pemesanan'] ?? '') == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Update Status
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
 
                <div class="detail-card text-center">
                    <h5 class="section-title">
                        <i class="fas fa-cogs me-2"></i>Aksi
                    </h5>
                    
                    <div class="d-grid gap-2">
                        <a href="riwayat_pemesanan.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat
                        </a>
                        
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print me-2"></i>Cetak Detail
                        </button>
                        
                        <?php if ($_SESSION['pengguna'] == 'admin'): ?>
                            <a href="admin_dashboard.php" class="btn btn-success">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
     
    <style media="print">
        body { background: white !important; }
        .navbar, .btn, .status-update-form { display: none !important; }
        .detail-card { 
            box-shadow: none !important; 
            border: 1px solid #ddd !important;
            background: white !important;
        }
        .container { margin-top: 0 !important; }
    </style>
</body>
</html>