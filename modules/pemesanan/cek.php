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
           (k.jumlah * o.harga_obat) as subtotal
    FROM keranjang k 
    JOIN obat o ON k.ID_obat = o.ID_obat 
    WHERE k.ID_user = ? 
    ORDER BY k.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$total_harga = 0;
foreach ($cart_items as $item) {
    $total_harga += $item['subtotal'];
}
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
            $total_harga,
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
        .navbar-brand {
            font-weight: bold;
            color: #2c5f52 !important;
        }
        .btn-primary {
            background-color: #2c5f52;
            border-color: #2c5f52;
        }
        .btn-primary:hover {
            background-color: #1e3f35;
            border-color: #1e3f35;
        }
        .navbar {
            background-color: #2c5f52 !important;
        }
        .checkout-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .order-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .item-row {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .item-row:last-child {
            border-bottom: none;
        }
        .required {
            color: #dc3545;
        }
        .payment-info {
            display: none;
            margin-top: 15px;
        }
        .payment-info.show {
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-prescription-bottle-alt me-2"></i>DocTo
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../index.php">
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
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-credit-card me-2"></i>Checkout</h2>
                <p class="text-muted">Lengkapi informasi untuk menyelesaikan pesanan</p>
            </div>
        </div>

        <form method="POST" id="checkoutForm">
            <div class="row">
                <div class="col-lg-4 order-lg-2">
                    <div class="checkout-card">
                        <h4 class="mb-3"><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h4>
                        
                        <div class="order-summary">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="item-row">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['nama_obat']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($item['jenis_obat']) ?></small>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary"><?= $item['jumlah'] ?> pcs</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <strong><?= formatRupiah($item['subtotal']) ?></strong>
                                        <br>
                                        <small class="text-muted">@ <?= formatRupiah($item['harga_obat']) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong><?= formatRupiah($total_harga) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ongkos Kirim:</span>
                                    <strong class="text-success">Gratis</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <h5>Total:</h5>
                                    <h5 class="text-primary"><?= formatRupiah($total_harga) ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 order-lg-1">
                    <div class="checkout-card">
                        <h4 class="mb-3"><i class="fas fa-truck me-2"></i>Informasi Pengiriman</h4>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="alamat_pengiriman" class="form-label">
                                    Alamat Lengkap <span class="required">*</span>
                                </label>
                                <textarea class="form-control <?= (!empty($error_msg) && empty($_POST['alamat_pengiriman'])) ? 'is-invalid' : '' ?>" 
                                          id="alamat_pengiriman" name="alamat_pengiriman" 
                                          rows="3" required placeholder="Masukkan alamat lengkap untuk pengiriman"><?= isset($_POST['alamat_pengiriman']) ? htmlspecialchars($_POST['alamat_pengiriman']) : '' ?></textarea>
                                <div class="form-text">Minimal 10 karakter</div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="nomor_telepon" class="form-label">
                                    Nomor Telepon <span class="required">*</span>
                                </label>
                                <input type="tel" class="form-control <?= (!empty($error_msg) && empty($_POST['nomor_telepon'])) ? 'is-invalid' : '' ?>" 
                                       id="nomor_telepon" name="nomor_telepon" 
                                       required placeholder="08xxxxxxxxxx"
                                       value="<?= isset($_POST['nomor_telepon']) ? htmlspecialchars($_POST['nomor_telepon']) : '' ?>">
                                <div class="form-text">Format: 08xxxxxxxxxx</div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" 
                                          rows="2" placeholder="Catatan khusus untuk pesanan"><?= isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : '' ?></textarea>
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
                            <p class="mb-0">Pembayaran dilakukan langsung kepada kurir saat obat diterima. Pastikan uang pas sejumlah <strong><?= formatRupiah($total_harga) ?></strong></p>
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

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-prescription-bottle-alt me-2"></i>DocTo</h5>
                    <p class="mb-0">Apotek online terpercaya untuk kebutuhan kesehatan Anda.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 DocTo. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showPaymentInfo(type) {
            document.getElementById('transfer-info').classList.remove('show');
            document.getElementById('cod-info').classList.remove('show');
            
            if (type === 'transfer') {
                document.getElementById('transfer-info').classList.add('show');
            } else if (type === 'cod') {
                document.getElementById('cod-info').classList.add('show');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const transferRadio = document.getElementById('transfer');
            const codRadio = document.getElementById('cod');
            
            if (transferRadio && transferRadio.checked) {
                showPaymentInfo('transfer');
            } else if (codRadio && codRadio.checked) {
                showPaymentInfo('cod');
            }
        });
    </script>
</body>
</html>