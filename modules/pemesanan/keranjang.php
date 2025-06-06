<?php
require_once 'config.php';
 
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        $id_keranjang = $_POST['id_keranjang'];
        $jumlah = $_POST['jumlah'];
        
        if ($jumlah > 0) {
            $stmt = $pdo->prepare("UPDATE keranjang SET jumlah = ? WHERE ID_keranjang = ? AND ID_user = ?");
            $stmt->execute([$jumlah, $id_keranjang, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM keranjang WHERE ID_keranjang = ? AND ID_user = ?");
            $stmt->execute([$id_keranjang, $_SESSION['user_id']]);
        }
        $success_msg = "Keranjang berhasil diupdate!";
    }
    
    if (isset($_POST['remove_item'])) {
        $id_keranjang = $_POST['id_keranjang'];
        $stmt = $pdo->prepare("DELETE FROM keranjang WHERE ID_keranjang = ? AND ID_user = ?");
        $stmt->execute([$id_keranjang, $_SESSION['user_id']]);
        $success_msg = "Item berhasil dihapus dari keranjang!";
    }
}
 
$stmt = $pdo->prepare("
    SELECT k.*, o.nama_obat, o.jenis_obat, o.harga_obat, o.stok_obat, o.tanggal_kadaluarsa,
           (k.jumlah * o.harga_obat) as subtotal
    FROM keranjang k 
    JOIN obat o ON k.ID_obat = o.ID_obat 
    WHERE k.ID_user = ? 
    ORDER BY k.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();
 
$total_harga = 0;
$has_expired_items = false;
foreach ($cart_items as $item) {
    $total_harga += $item['subtotal'];
    $expiry = new DateTime($item['tanggal_kadaluarsa']);
    $today = new DateTime();
    if ($expiry < $today) {
        $has_expired_items = true;
    }
}
 
$stmt = $pdo->prepare("SELECT SUM(jumlah) as total FROM keranjang WHERE ID_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_count = $stmt->fetch()['total'] ?? 0;
 
function checkExpiry($tanggal_kadaluarsa) {
    $today = new DateTime();
    $expiry = new DateTime($tanggal_kadaluarsa);
    $diff = $today->diff($expiry);
    
    if ($expiry < $today) {
        return 'expired';
    } elseif ($diff->days <= 30) {
        return 'warning';
    }
    return 'safe';
}
 
$medicine_icons = [
    'Tablet' => 'fas fa-tablets',
    'Kapsul' => 'fas fa-capsules',
    'Sirup' => 'fas fa-prescription-bottle',
    'Salep' => 'fas fa-pump-medical',
    'Tetes' => 'fas fa-eye-dropper',
    'default' => 'fas fa-pills'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #E2EBE9 0%, #D2F1E6 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, #37705D 0%, #37956F 50%) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #ffffff !important;
            font-size: 1.8rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .navbar-brand i {
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: #ffffff !important;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 16px !important;
            border-radius: 8px;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .cart-item {
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .medicine-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .quantity-input {
            width: 80px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .quantity-input:focus {
            border-color: #4facfe;
            box-shadow: 0 0 10px rgba(79, 172, 254, 0.3);
        }
        
        .total-section {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2c5f52 0%, #4facfe 100%);
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 600;
            padding: 12px 24px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e4238 0%, #00d4fe 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(44, 95, 82, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid #4facfe;
            color: #4facfe;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-color: transparent;
            transform: translateY(-2px);
        }
        
        .btn-outline-secondary {
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-danger {
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-danger:hover {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border-color: transparent;
            transform: translateY(-2px);
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .header-section {
            text-align: left;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg,rgb(102, 234, 181) 0%,rgb(40, 61, 130) 100%);
            border-radius: 20px;
            color: white;
        }
        
        .header-section h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            margin-top: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .price-tag {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .stock-info {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            display: inline-block;
            font-size: 0.85rem;
        }
        
        .expired-warning {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
            animation: blink 1.5s infinite;
        }
        
        .expiry-warning {
            background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }
        
        .summary-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .summary-card h4 {
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .item-medicine-type {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            display: inline-block;
            margin-left: 10px;
        }

        ::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #356859 0%, #2a5346 100%);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #356859 0%, #2a5346 100%);
}
    </style>
</head>
<body> 
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="../../index.php">
                <img src="../../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;">DocTo
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../index.php">
                    <i class="fas fa-home me-1"></i>Beranda
                </a>
                <a class="nav-link" href="katalog.php">
                    <i class="fas fa-pills me-1"></i>Katalog Obat
                </a>
                <a class="nav-link active position-relative" href="keranjang.php">
                    <i class="fas fa-shopping-cart me-1"></i>Keranjang
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <a class="nav-link" href="riwayat_pemesanan.php">
                    <i class="fas fa-history me-1"></i>Riwayat
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div style="margin-top: 80px;">
        <div class="container"> 
            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 15px; border: none; background: linear-gradient(135deg, #51cf66 0%, #40c057 100%); color: white;">
                    <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
 
            <div class="header-section">
                <h2><i class="fas fa-shopping-cart me-3"></i>Keranjang Belanja</h2>
                <p class="lead mb-0">Kelola item kebutuhan anda</p>
            </div>

            <?php if (!empty($cart_items)): ?>
                <div class="row"> 
                    <div class="col-lg-8">
                        <?php foreach ($cart_items as $item): 
                            $expiry_status = checkExpiry($item['tanggal_kadaluarsa']);
                            $icon = $medicine_icons[$item['jenis_obat']] ?? $medicine_icons['default'];
                        ?>
                            <div class="cart-item">
                                <?php if ($expiry_status == 'expired'): ?>
                                    <div class="expired-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        EXPIRED - JANGAN DIKONSUMSI!
                                    </div>
                                <?php elseif ($expiry_status == 'warning'): ?>
                                    <div class="expiry-warning">
                                        <i class="fas fa-clock me-2"></i>
                                        Hampir Kadaluarsa - Hati-hati!
                                    </div>
                                <?php endif; ?>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center">
                                        <i class="<?= $icon ?> medicine-icon"></i>
                                    </div>
                                    
                                    <div class="col-md-5">
                                        <h5 class="mb-2">
                                            <?= htmlspecialchars($item['nama_obat']) ?>
                                            <span class="item-medicine-type"><?= htmlspecialchars($item['jenis_obat']) ?></span>
                                        </h5>
                                        <div class="mb-2">
                                            <span class="price-tag">
                                                <i class="fas fa-tag me-1"></i><?= formatRupiah($item['harga_obat']) ?> / item
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Exp: <?= date('d/m/Y', strtotime($item['tanggal_kadaluarsa'])) ?>
                                        </small>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_keranjang" value="<?= $item['ID_keranjang'] ?>">
                                            <div class="input-group">
                                                <input type="number" name="jumlah" class="form-control quantity-input" 
                                                       value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['stok_obat'] ?>">
                                                <button type="submit" name="update_cart" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <div class="mt-2">
                                            <span class="stock-info">
                                                <i class="fas fa-box me-1"></i>Stok: <?= $item['stok_obat'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2 text-center">
                                        <strong class="text-primary fs-5"><?= formatRupiah($item['subtotal']) ?></strong>
                                    </div>
                                    
                                    <div class="col-md-1 text-center">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_keranjang" value="<?= $item['ID_keranjang'] ?>">
                                            <button type="submit" name="remove_item" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Hapus item ini dari keranjang?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                     
                    <div class="col-lg-4">
                        <div class="total-section">
                            <div class="summary-card">
                                <h4><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h4>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-pills me-1"></i>Jumlah Item:</span>
                                    <strong><?= count($cart_items) ?> item</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-money-bill-wave me-1"></i>Total Harga:</span>
                                    <strong class="fs-4"><?= formatRupiah($total_harga) ?></strong>
                                </div>
                            </div>
                            
                            <?php if ($has_expired_items): ?>
                                <div class="alert alert-warning" style="border-radius: 15px; border: none;">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Perhatian!</strong> Ada obat yang sudah kadaluarsa di keranjang Anda.
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-3">
                                <a href="checkout.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Checkout Sekarang
                                </a>
                                <a href="katalog.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?> 
                <div class="empty-state">
                    <i class="fas fa-shopping-cart fa-5x mb-4"></i>
                    <h4 class="text-muted mb-3">Keranjang Kosong</h4>
                    <p class="text-muted mb-4">Anda belum menambahkan obat ke dalam keranjang. Yuk, mulai belanja obat yang Anda butuhkan!</p>
                    <a href="katalog.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-pills me-2"></i>Lihat Katalog Obat
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>