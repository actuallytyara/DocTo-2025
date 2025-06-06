<?php
require_once 'config.php';
checkLogin();
 
$stmt = $pdo->prepare("SELECT COUNT(*) FROM keranjang WHERE ID_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_count = $stmt->fetchColumn();

if ($cart_count == 0) {
    echo "<script>alert('Keranjang kosong! Silakan tambahkan obat terlebih dahulu.'); window.location.href='katalog.php';</script>";
    exit();
}
 
$stmt = $pdo->prepare("
    SELECT k.*, o.nama_obat, o.jenis_obat, o.harga_obat, o.stok_obat,
           (k.jumlah * o.harga_obat) as subtotal,
           o.tanggal_kadaluarsa
    FROM keranjang k 
    JOIN obat o ON k.ID_obat = o.ID_obat 
    WHERE k.ID_user = ? 
    ORDER BY k.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();
 
$total_harga = 0;
$total_items = 0;
foreach ($cart_items as $item) {
    $total_harga += $item['subtotal'];
    $total_items += $item['jumlah'];
}
 
$shipping_cost = $total_harga >= 50000 ? 0 : 10000;
$subtotal = $total_harga;
$total = $subtotal + $shipping_cost;
 
$error_msg = '';
$success_msg = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    try { 
        $alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
        $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
        $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');
        
     
        if (empty($alamat_pengiriman)) {
            throw new Exception("Alamat pengiriman harus diisi!");
        }
        
        if (strlen($alamat_pengiriman) < 10) {
            throw new Exception("Alamat pengiriman terlalu pendek! Minimal 10 karakter.");
        }
        
        if (empty($nomor_telepon)) {
            throw new Exception("Nomor telepon harus diisi!");
        }
        
        if (!preg_match('/^08\d{8,11}$/', $nomor_telepon)) {
            throw new Exception("Format nomor telepon tidak valid! Gunakan format 08xxxxxxxxxx");
        }
        
        if (empty($metode_pembayaran)) {
            throw new Exception("Metode pembayaran harus dipilih!");
        }
        
        $valid_payment_methods = ['Transfer Bank', 'COD'];
        if (!in_array($metode_pembayaran, $valid_payment_methods)) {
            throw new Exception("Metode pembayaran tidak valid!");
        }
         
        $pdo->beginTransaction();
        $transaction_started = true;
         
        do {
            $nomor_pemesanan = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE nomor_pemesanan = ?");
            $stmt->execute([$nomor_pemesanan]);
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);
         
        $stmt = $pdo->prepare("
            INSERT INTO pemesanan (nomor_pemesanan, ID_user, total_harga, metode_pembayaran, 
                                   alamat_pengiriman, nomor_telepon, catatan, status_pemesanan, tanggal_pemesanan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu', NOW())
        ");
        
        $result = $stmt->execute([
            $nomor_pemesanan,
            $_SESSION['user_id'],
            $total, 
            $metode_pembayaran,
            $alamat_pengiriman,
            $nomor_telepon,
            $catatan
        ]);
        
        if (!$result) {
            throw new Exception("Gagal menyimpan data pemesanan!");
        }
        
        $id_pemesanan = $pdo->lastInsertId();
        
        if (!$id_pemesanan) {
            throw new Exception("Gagal mendapatkan ID pemesanan");
        }
         
        foreach ($cart_items as $item) { 
            $stmt = $pdo->prepare("SELECT stok_obat FROM obat WHERE ID_obat = ?");
            $stmt->execute([$item['ID_obat']]);
            $current_stock = $stmt->fetchColumn();
            
            if ($current_stock === false) {
                throw new Exception("Obat dengan ID {$item['ID_obat']} tidak ditemukan!");
            }
            
            if ($current_stock < $item['jumlah']) {
                throw new Exception("Stok obat '{$item['nama_obat']}' tidak mencukupi! Tersisa: {$current_stock} pcs");
            }
             
            $stmt = $pdo->prepare("
                INSERT INTO detail_pemesanan (ID_pemesanan, ID_obat, jumlah, harga_satuan, subtotal) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $detail_result = $stmt->execute([
                $id_pemesanan,
                $item['ID_obat'],
                $item['jumlah'],
                $item['harga_obat'],
                $item['subtotal']
            ]);
            
            if (!$detail_result) {
                throw new Exception("Gagal menyimpan detail pemesanan untuk obat: {$item['nama_obat']}");
            }
             
            $stmt = $pdo->prepare("UPDATE obat SET stok_obat = stok_obat - ? WHERE ID_obat = ?");
            $stock_result = $stmt->execute([$item['jumlah'], $item['ID_obat']]);
            
            if (!$stock_result) {
                throw new Exception("Gagal mengupdate stok untuk obat: {$item['nama_obat']}");
            }
        }
         
        $stmt = $pdo->prepare("DELETE FROM keranjang WHERE ID_user = ?");
        $clear_result = $stmt->execute([$_SESSION['user_id']]);
        
        if (!$clear_result) {
            throw new Exception("Gagal mengosongkan keranjang belanja");
        }
         
        $pdo->commit();
        $transaction_started = false;
         
        error_log("Order created successfully: $nomor_pemesanan for user: " . $_SESSION['user_id']);
         
        header("Location: checkout_success.php?order=" . urlencode($nomor_pemesanan));
        exit();
        
    } catch (Exception $e) { 
        if (isset($transaction_started) && $transaction_started) {
            try {
                $pdo->rollBack();
            } catch (PDOException $rollbackException) {
                error_log("Rollback failed: " . $rollbackException->getMessage());
            }
        }
        
        $error_msg = $e->getMessage();
        error_log("Checkout error: " . $error_msg);
    }
}
 
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}
 
function getObatIcon($jenis_obat) {
    $icons = [
        'Tablet' => 'fas fa-pills',
        'Kapsul' => 'fas fa-capsules',
        'Sirup' => 'fas fa-flask',
        'Salep' => 'fas fa-pump-medical',
        'Tetes' => 'fas fa-eye-dropper',
        'Injeksi' => 'fas fa-syringe',
        'Spray' => 'fas fa-spray-can',
        'Cream' => 'fas fa-hand-holding-medical',
        'Gel' => 'fas fa-hand-holding-medical',
        'Lotion' => 'fas fa-hand-holding-medical'
    ];
    
    return $icons[$jenis_obat] ?? 'fas fa-pills';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style> 
        :root {
            --primary-color: #37705D;
            --primary-dark: #3DBDB5;
            --primary-light: #6EE7DF;
            --secondary-color: #37956F;
            --accent-color: #96CEB4;
            --text-dark: #2C3E50;
            --text-light: #7F8C8D;
            --bg-light: #F8FFFE;
            --card-shadow: 0 8px 25px rgba(78, 205, 196, 0.15);
            --border-radius: 15px;
        }

        body {
            background: linear-gradient(135deg, #F8FFFE 0%, #E8F8F7 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
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

        .navbar-brand:hover {
            transform: scale(1.05);
            color: #F0F8FF !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
 
        .container {
            max-width: 1200px;
        }

        .page-header {
            text-align: center;
            margin: 2rem 0;
            padding: 2rem;
            background: linear-gradient(135deg, white 0%, var(--bg-light) 100%);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(78, 205, 196, 0.1);
        }

        .page-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }

        .page-header p {
            color: var(--text-light);
            font-size: 1.1rem;
            margin: 0;
        }
 
        .checkout-card {
            background: linear-gradient(145deg, white 0%, #FEFFFE 100%);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            border: 1px solid rgba(78, 205, 196, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .checkout-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-light) 100%);
        }

        .checkout-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(78, 205, 196, 0.2);
        }

        .checkout-card h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(78, 205, 196, 0.1);
        }
 
        .order-summary {
            background: linear-gradient(135deg, var(--bg-light) 0%, #F0F8F7 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid rgba(78, 205, 196, 0.1);
            position: relative;
        }

        .order-summary::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light), var(--secondary-color));
            border-radius: 12px;
            z-index: -1;
        }

        .item-row {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .item-row:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(78, 205, 196, 0.1);
        }

        .item-row:last-child {
            margin-bottom: 0;
        }
 
        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #E8F4F3;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 205, 196, 0.25);
            background: white;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-weight: 500;
            color: var(--text-dark);
            cursor: pointer;
        }
 
        .payment-info {
            border-radius: 12px;
            border: none;
            margin-top: 1rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .payment-info.alert-info {
            background: linear-gradient(135deg, #E3F2FD 0%, #F0F8FF 100%);
            color: #1565C0;
            border-left: 4px solid #2196F3;
        }

        .payment-info.alert-success {
            background: linear-gradient(135deg, #E8F5E8 0%, #F0FFF0 100%);
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }
 
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 205, 196, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 205, 196, 0.4);
        }

        .btn-outline-secondary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(78, 205, 196, 0.3);
        }
 
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge.bg-secondary {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--primary-light) 100%) !important;
            color: var(--text-dark) !important;
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        }
 
        .total-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(78, 205, 196, 0.3);
        }
 
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
            color: #C62828;
            border-left: 4px solid #F44336;
        }

        .alert-success {
            background: linear-gradient(135deg, #E8F5E8 0%, #C8E6C9 100%);
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }
 
        .required {
            color: #FF6B6B;
            font-weight: bold;
        }
 
        footer {
            background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%) !important;
            margin-top: 4rem;
        }
 
        @media (max-width: 768px) {
            .checkout-card {
                padding: 1.5rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-header h2 {
                font-size: 2rem;
            }
            
            .btn-primary, .btn-outline-secondary {
                padding: 0.6rem 1.5rem;
                font-size: 1rem;
            }
        }
 
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .checkout-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .checkout-card:nth-child(2) { animation-delay: 0.1s; }
        .checkout-card:nth-child(3) { animation-delay: 0.2s; }
        .checkout-card:nth-child(4) { animation-delay: 0.3s; }

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
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="beranda.php">
                <i class="fas fa-prescription-bottle-alt me-2"></i>DocTo
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="beranda.php">
                    <i class="fas fa-home me-1"></i>Beranda
                </a>
                <a class="nav-link" href="katalog.php">
                    <i class="fas fa-pills me-1"></i>Katalog Obat
                </a>
                <a class="nav-link" href="keranjang.php">
                    <i class="fas fa-shopping-cart me-1"></i>Keranjang
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

    <div class="container mt-4"> 
        <div class="page-header">
            <h2><i class="fas fa-credit-card me-3"></i>Checkout</h2>
            <p>Lengkapi informasi untuk menyelesaikan pesanan Anda</p>
        </div>
 
        <div class="alert alert-info alert-dismissible fade show d-none" role="alert">
            <i class="fas fa-info-circle me-2"></i>Demo: Pesan informasi akan muncul di sini
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        
        <form id="checkoutForm" method="POST">
            <div class="row"> 
                <div class="col-lg-4 order-lg-2">
                    <div class="checkout-card">
                        <h4><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h4>
                        
                        <div class="order-summary">
                            <?php foreach ($cart_items as $item): 
                                $icon = getObatIcon($item['jenis_obat']);
                            ?>
                                <div class="item-row">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="<?= $icon ?> me-2" style="color: #4facfe;"></i>
                                                <h6 class="mb-0"><?= htmlspecialchars($item['nama_obat']) ?></h6>
                                            </div>
                                            <div class="medicine-type-badge">
                                                <?= htmlspecialchars($item['jenis_obat']) ?>
                                            </div>
                                            <div class="mt-2">
                                                <span class="quantity-badge"><?= $item['jumlah'] ?> pcs</span>
                                            </div>
                                            <?php if (!empty($item['tanggal_kadaluarsa'])): ?>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fas fa-calendar me-1"></i>Exp: <?= date('d/m/Y', strtotime($item['tanggal_kadaluarsa'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <strong><?= formatRupiah($item['subtotal']) ?></strong>
                                            <br>
                                            <small class="text-muted">@ <?= formatRupiah($item['harga_obat']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="total-section">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-pills me-1"></i>Total Item:</span>
                                    <strong><?= $total_items ?> pcs</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-money-bill-wave me-1"></i>Subtotal:</span>
                                    <strong><?= formatRupiah($subtotal) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span><i class="fas fa-truck me-1"></i>Ongkos Kirim:</span>
                                    <strong><?= $shipping_cost == 0 ? 'Gratis' : formatRupiah($shipping_cost) ?></strong>
                                </div>
                                <hr style="border-color: rgba(255,255,255,0.3);">
                                <div class="d-flex justify-content-between">
                                    <h5><i class="fas fa-receipt me-1"></i>Total:</h5>
                                    <h5><?= formatRupiah($total) ?></h5>
                                </div>
                                <?php if ($shipping_cost == 0): ?>
                                    <small class="text-light mt-2 d-block">
                                        <i class="fas fa-gift me-1"></i>Selamat! Anda mendapat gratis ongkir
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

 
                <div class="col-lg-8 order-lg-1"> 
                    <div class="checkout-card">
                        <h4><i class="fas fa-truck me-2"></i>Informasi Pengiriman</h4>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="alamat_pengiriman" class="form-label">
                                    Alamat Lengkap <span class="required">*</span>
                                </label>
                                <textarea class="form-control" id="alamat_pengiriman" name="alamat_pengiriman" 
                                          rows="3" required placeholder="Masukkan alamat lengkap untuk pengiriman"></textarea>
                                <div class="form-text">Minimal 10 karakter</div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="nomor_telepon" class="form-label">
                                    Nomor Telepon <span class="required">*</span>
                                </label>
                                <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon" 
                                       required placeholder="08xxxxxxxxxx">
                                <div class="form-text">Format: 08xxxxxxxxxx</div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" 
                                          rows="2" placeholder="Catatan khusus untuk pesanan"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h4 class="mb-3"><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" 
                                           id="transfer" value="Transfer Bank" required
                                           <?= (isset($_POST['metode_pembayaran']) && $_POST['metode_pembayaran'] == 'Transfer Bank') ? 'checked' : '' ?>
                                           onchange="showPaymentInfo('transfer')">
                                    <label class="form-check-label" for="transfer">
                                        <i class="fas fa-university me-2"></i>Transfer Bank
                                    </label>
                                </div>
                                <small class="text-muted ms-4">Transfer ke rekening BCA</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" 
                                           id="cod" value="COD" required
                                           <?= (isset($_POST['metode_pembayaran']) && $_POST['metode_pembayaran'] == 'COD') ? 'checked' : '' ?>
                                           onchange="showPaymentInfo('cod')">
                                    <label class="form-check-label" for="cod">
                                        <i class="fas fa-truck me-2"></i>Cash on Delivery (COD)
                                    </label>
                                </div>
                                <small class="text-muted ms-4">Bayar saat obat diterima</small>
                            </div>
                        </div>
                         
                        <div id="transfer-info" class="alert alert-info payment-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Transfer</h6>
                            <p class="mb-1"><strong>Bank BCA</strong></p>
                            <p class="mb-1">No. Rekening: <strong>1234567890</strong></p>
                            <p class="mb-1">Atas Nama: <strong>DocTo Pharmacy</strong></p>
                            <p class="mb-0">Setelah transfer, harap konfirmasi via WhatsApp: <strong>0812-3456-7890</strong></p>
                        </div>
                        
                        <div id="cod-info" class="alert alert-success payment-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Cash on Delivery</h6>
                            <p class="mb-0">Pembayaran dilakukan langsung kepada kurir saat obat diterima. Pastikan uang pas</p>
                        </div>
                    </div>
 
                    <div class="checkout-card">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="keranjang.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Keranjang
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="submit" name="place_order" class="btn btn-primary w-100">
                                    <i class="fas fa-check me-2"></i>Buat Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
 
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-prescription-bottle-alt me-2"></i>DocTo</h5>
                    <p class="mb-0">Apotek online terpercaya untuk kebutuhan kesehatan Anda.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Kontak Kami</h6>
                    <p class="mb-0">
                        <i class="fas fa-phone me-2"></i>0812-3456-7890<br>
                        <i class="fas fa-envelope me-2"></i>support@docto.com
                    </p>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <small>&copy; 2024 DocTo. Semua hak dilindungi.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script> 
        function showPaymentInfo(type) { 
            const transferInfo = document.getElementById('transfer-info');
            const codInfo = document.getElementById('cod-info');
            
            transferInfo.style.display = 'none';
            codInfo.style.display = 'none';
             
            setTimeout(() => {
                if (type === 'transfer') {
                    transferInfo.style.display = 'block';
                    transferInfo.style.opacity = '0';
                    setTimeout(() => {
                        transferInfo.style.transition = 'opacity 0.3s ease';
                        transferInfo.style.opacity = '1';
                    }, 10);
                } else if (type === 'cod') {
                    codInfo.style.display = 'block';
                    codInfo.style.opacity = '0';
                    setTimeout(() => {
                        codInfo.style.transition = 'opacity 0.3s ease';
                        codInfo.style.opacity = '1';
                    }, 10);
                }
            }, 100);
        }
 
        document.addEventListener('DOMContentLoaded', function() { 
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
 
            const form = document.getElementById('checkoutForm');
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
            });
 
            const phoneInput = document.getElementById('nomor_telepon');
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.startsWith('0')) {
                    this.value = value;
                }
            });
        });
    </script>
</body>
</html>