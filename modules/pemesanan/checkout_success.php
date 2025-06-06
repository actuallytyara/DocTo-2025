<?php
require_once 'config.php';
checkLogin();
 
$nomor_pemesanan = $_GET['order'] ?? '';
 
if (empty($nomor_pemesanan)) {
    header('Location: riwayat_pemesanan.php');
    exit();
}
 
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.email 
    FROM pemesanan p 
    JOIN tb_login u ON p.ID_user = u.ID_user 
    WHERE p.nomor_pemesanan = ? AND p.ID_user = ?
");
$stmt->execute([$nomor_pemesanan, $_SESSION['user_id']]);
$order = $stmt->fetch();
 
if (!$order) {
    echo "<script>alert('Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.'); window.location.href='riwayat_pemesanan.php';</script>";
    exit();
}
 
$stmt = $pdo->prepare("
    SELECT dp.*, o.nama_obat, o.jenis_obat 
    FROM detail_pemesanan dp 
    JOIN obat o ON dp.ID_obat = o.ID_obat 
    WHERE dp.ID_pemesanan = ?
");
$stmt->execute([$order['ID_pemesanan']]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 5px;
            padding: 8px 16px !important;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .order-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .order-card h2 {
            color: #28a745;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .order-card .lead {
            color: #6c757d;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .order-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: 2px solid #e9ecef;
        }

        .order-info h4 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin: -30px -30px 25px -30px;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .order-info .row {
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .order-info .row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .order-info .col-sm-4 {
            font-weight: 600;
            color: #495057;
        }

        .order-info .col-sm-8 {
            color: #212529;
            font-weight: 500;
        }

        .order-info .badge.bg-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: bold;
        }

        .order-info .text-primary {
            font-size: 1.5rem !important;
            font-weight: bold !important;
            color: #28a745 !important;
        }

        .item-row {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            border-bottom: 1px solid #eee;
        }

        .item-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .item-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .item-row h6 {
            font-weight: bold;
            color: #212529;
            margin-bottom: 5px;
        }

        .item-row small {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .item-row .badge.bg-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .item-row strong {
            font-weight: bold;
            color: #28a745;
            font-size: 1.1rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
            border: none !important;
            border-radius: 20px;
            padding: 25px;
            margin: 25px 0;
        }

        .alert-info h5 {
            color: #1976d2;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .alert-info .text-start {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .alert-success {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%) !important;
            border: none !important;
            border-radius: 20px;
            padding: 25px;
            margin: 25px 0;
        }

        .alert-success h5 {
            color: #2e7d32;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .btn-primary:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4) !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-color: transparent !important;
        }

        .btn-outline-primary {
            border: 2px solid #667eea !important;
            color: #667eea !important;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-color: #667eea !important;
            color: white !important;
            transform: translateY(-3px);
        }

        .mt-4.pt-3.border-top {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            margin-top: 30px !important;
            border-top: 3px solid #667eea !important;
        }

        .mt-4.pt-3.border-top h6 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }

        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
            color: white;
            margin-top: 50px;
        }

        .alert.alert-info.mt-3 {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%) !important;
            border: none !important;
            border-radius: 15px;
            color: #856404 !important;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .order-card {
                margin: 20px;
                padding: 25px;
            }

            .order-card h2 {
                font-size: 2rem;
            }

            .order-info .row {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 5px;
            }

            .item-row .row {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .btn-primary, .btn-outline-primary {
                width: 100%;
                margin: 5px 0;
            }

            .d-grid.gap-2.d-md-flex.justify-content-md-center {
                text-align: center;
            }
        }
    </style>
</head>
<body class="bg-light"> 
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="order-card text-center"> 
                    <i class="fas fa-check-circle success-icon"></i>
                     
                    <h2 class="text-success mb-3">Pesanan Berhasil Dibuat!</h2>
                    <p class="lead text-muted mb-4">
                        Terima kasih atas pemesanan Anda. Pesanan Anda telah berhasil diproses dan akan segera kami siapkan.
                    </p>
 
                    <div class="order-info text-start">
                        <h4 class="mb-3"><i class="fas fa-receipt me-2"></i>Detail Pesanan</h4>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Nomor Pesanan:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($order['nomor_pemesanan']) ?></span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Tanggal Pesanan:</strong></div>
                            <div class="col-sm-8"><?= date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])) ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Total Pembayaran:</strong></div>
                            <div class="col-sm-8">
                                <strong class="text-primary">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Metode Pembayaran:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($order['metode_pembayaran']) ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-warning"><?= ucfirst($order['status_pemesanan']) ?></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Alamat Pengiriman:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($order['alamat_pengiriman']) ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Nomor Telepon:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($order['nomor_telepon']) ?></div>
                        </div>

                        <?php if (!empty($order['catatan'])): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Catatan:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($order['catatan']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
 
                    <?php if (!empty($order_items)): ?>
                    <div class="order-info text-start">
                        <h4 class="mb-3"><i class="fas fa-pills me-2"></i>Item Pesanan</h4>
                        
                        <?php foreach ($order_items as $item): ?>
                        <div class="item-row">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1"><?= htmlspecialchars($item['nama_obat']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($item['jenis_obat']) ?></small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <span class="badge bg-secondary"><?= $item['jumlah'] ?> pcs</span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
                                    <br>
                                    <small class="text-muted">@ Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
 
                    <?php if ($order['metode_pembayaran'] == 'Transfer Bank'): ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h5>
                        <p class="mb-2">Silakan transfer ke rekening berikut:</p>
                        <div class="text-start">
                            <strong>Bank BCA</strong><br>
                            No. Rekening: <strong>1234567890</strong><br>
                            Atas Nama: <strong>DocTo Pharmacy</strong><br>
                            Jumlah: <strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong>
                        </div>
                        <p class="mt-2 mb-0">
                            <small>Mohon konfirmasi pembayaran melalui WhatsApp: <strong>0812-3456-7890</strong></small>
                        </p>
                    </div>
                    <?php elseif ($order['metode_pembayaran'] == 'COD'): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-truck me-2"></i>Cash on Delivery (COD)</h5>
                        <p class="mb-0">Pembayaran akan dilakukan saat obat diterima. Pastikan Anda siap dengan uang pas sebesar <strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong></p>
                    </div>
                    <?php endif; ?>
 
                    <div class="mt-4">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="riwayat_pemesanan.php" class="btn btn-primary me-md-2">
                                <i class="fas fa-history me-2"></i>Lihat Riwayat Pesanan
                            </a>
                            <a href="katalog.php" class="btn btn-outline-primary">
                                <i class="fas fa-pills me-2"></i>Belanja Lagi
                            </a>
                        </div>
                    </div>
 
                    <div class="mt-4 pt-3 border-top">
                        <h6><i class="fas fa-headset me-2"></i>Butuh Bantuan?</h6>
                        <p class="text-muted mb-0">
                            Hubungi customer service kami di <strong>0812-3456-7890</strong> 
                            atau email <strong>support@docto.com</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <footer class="bg-dark text-light mt-5 py-4">
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
                <small>&copy; <?= date('Y') ?> DocTo. Semua hak dilindungi.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
     
    <script> 
        let countdown = 10;
        const countdownElement = document.createElement('div');
        countdownElement.className = 'alert alert-info mt-3';
        countdownElement.innerHTML = `<i class="fas fa-clock me-2"></i>Halaman akan otomatis dialihkan ke riwayat pesanan dalam <span id="countdown">${countdown}</span> detik.`;
        
        document.querySelector('.order-card').appendChild(countdownElement);
        
        const countdownTimer = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownTimer);
                window.location.href = 'riwayat_pemesanan.php';
            }
        }, 1000);
    </script>
</body>
</html>