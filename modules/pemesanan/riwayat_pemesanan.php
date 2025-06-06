<?php
require_once 'config.php';
checkLogin();
 
$limit = 10;  
$user_id = $_SESSION['user_id'];  
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
 
$limit = (int)$limit;
$offset = (int)$offset;
 
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE ID_user = ?");
$stmt->execute([$user_id]);
$total_records = $stmt->fetchColumn();
 
$total_pages = $limit > 0 ? ceil($total_records / $limit) : 1;
 
$stmt = $pdo->prepare("
    SELECT * FROM pemesanan 
    WHERE ID_user = ? 
    ORDER BY tanggal_pemesanan DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan - DocTo</title>
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
       
        
        .header-section {
            text-align: left;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, rgb(122, 221, 251) 0%, rgb(94, 40, 130) 100%);
            border-radius: 20px;
            color: white;
        }
        
        .header-section h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .order-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
            border: none;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .order-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .order-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #2c5f52;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9ff 100%);
            padding: 8px 15px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .status-menunggu { background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: white; }
        .status-diproses { background: linear-gradient(135deg, #17a2b8 0%, #0097a7 100%); color: white; }
        .status-dikirim { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; }
        .status-selesai { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; }
        .status-dibatalkan { background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%); color: white; }
        
        .order-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            background: rgba(79, 172, 254, 0.1);
            padding: 8px 12px;
            border-radius: 10px;
            color: #2c5f52;
            font-size: 0.9rem;
        }
        
        .info-item i {
            margin-right: 8px;
            color: #4facfe;
        }
        
        .price-display {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }
        
        .price-display .amount {
            font-size: 1.4rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        .btn-group-custom {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }
        
        .btn-detail {
            background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
            color: white;
        }
        
        .btn-detail:hover {
            background: linear-gradient(135deg, #5a2d91 0%, #4a1d75 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(111, 66, 193, 0.4);
            color: white;
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            color: white;
        }
        
        .btn-cancel:hover {
            background: linear-gradient(135deg, #bd2130 0%, #a71e2a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .btn-receipt {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }
        
        .btn-receipt:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            margin-top: 40px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #4facfe;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .empty-state h4 {
            color: #2c5f52;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .empty-state .btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .empty-state .btn:hover {
            background: linear-gradient(135deg, #00d4fe 0%, #4facfe 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }
        
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-link {
            border: none;
            border-radius: 10px !important;
            margin: 0 3px;
            padding: 8px 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #2c5f52;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, #2c5f52 0%, #37705D 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(44, 95, 82, 0.4);
        }
        
        .pagination-info {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9ff 100%);
            border-radius: 15px;
            color: #2c5f52;
            font-weight: 600;
        }
        
        .order-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
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
        
        .order-card {
            animation: fadeInUp 0.6s ease;
        }
        
        .order-card:nth-child(even) {
            animation-delay: 0.1s;
        }
        
        .order-card:nth-child(odd) {
            animation-delay: 0.2s;
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
                <a class="nav-link" href="keranjang.php">
                    <i class="fas fa-shopping-cart me-1"></i>Keranjang
                </a>
                <a class="nav-link active" href="riwayat_pemesanan.php">
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
            <div class="header-section">
                <h2><i class="fas fa-history me-3"></i>Riwayat Pemesanan</h2>
                <p class="lead mb-0">Pantau status dan riwayat pemesanan obat Anda dengan mudah</p>
            </div>

            <?php if (empty($orders)): ?> 
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h4>Belum Ada Pemesanan</h4>
                    <p class="text-muted mb-4">Anda belum memiliki riwayat pemesanan. Mulai berbelanja sekarang!</p>
                    <a href="katalog.php" class="btn btn-primary">
                        <i class="fas fa-pills me-2"></i>Mulai Berbelanja
                    </a>
                </div>
            <?php else: ?>
                 
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="row">
                        <div class="col-lg-8"> 
                            <div class="d-flex align-items-center flex-wrap mb-3">
                                <span class="order-number"><?= htmlspecialchars($order['nomor_pemesanan']) ?></span>
                                <?php
                                $status_class = 'status-secondary';
                                switch(strtolower($order['status_pemesanan'])) {
                                    case 'menunggu':
                                        $status_class = 'status-menunggu';
                                        break;
                                    case 'diproses':
                                        $status_class = 'status-diproses';
                                        break;
                                    case 'dikirim':
                                        $status_class = 'status-dikirim';
                                        break;
                                    case 'selesai':
                                        $status_class = 'status-selesai';
                                        break;
                                    case 'dibatalkan':
                                        $status_class = 'status-dibatalkan';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?= $status_class ?>">
                                    <?= ucfirst($order['status_pemesanan']) ?>
                                </span>
                            </div>
                             
                            <div class="order-info">
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])) ?>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-credit-card"></i>
                                    <?= htmlspecialchars($order['metode_pembayaran']) ?>
                                </div>

                                <?php if (!empty($order['alamat_pengiriman'])): ?>
                                <div class="info-item" style="flex: 1; min-width: 100%;">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars(substr($order['alamat_pengiriman'], 0, 80)) ?>
                                    <?= strlen($order['alamat_pengiriman']) > 80 ? '...' : '' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-lg-4"> 
                            <div class="price-display">
                                <div class="small mb-1">Total Pembayaran</div>
                                <div class="amount">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></div>
                            </div>
                             
                            <div class="btn-group-custom">
                                <a href="detail_pemesanan.php?order=<?= $order['nomor_pemesanan'] ?>" 
                                   class="btn-custom btn-detail">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                
                                <?php if (strtolower($order['status_pemesanan']) == 'menunggu'): ?>
                                <button class="btn-custom btn-cancel" 
                                        onclick="cancelOrder('<?= $order['nomor_pemesanan'] ?>')">
                                    <i class="fas fa-times me-1"></i>Batalkan
                                </button>
                                <?php endif; ?>
                                
                                <?php if (strtolower($order['status_pemesanan']) == 'selesai'): ?>
                                <a href="checkout.php?order=<?= $order['nomor_pemesanan'] ?>" 
                                   class="btn-custom btn-receipt">
                                    <i class="fas fa-receipt me-1"></i>Lihat Nota
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
 
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination"> 
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
 
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
 
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
 
                <div class="pagination-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Menampilkan <?= min($offset + 1, $total_records) ?> - 
                    <?= min($offset + $limit, $total_records) ?> dari <?= $total_records ?> pesanan
                </div>
                <?php endif; ?>
                
            <?php endif; ?>
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
                    <p class="mb-0">&copy; 2024 DocTo. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function cancelOrder(orderNumber) {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                // Send AJAX request to cancel order
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_number=' + encodeURIComponent(orderNumber)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibatalkan');
                        location.reload();
                    } else {
                        alert('Gagal membatalkan pesanan: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membatalkan pesanan');
                });
            }
        }
    </script>
</body>
</html>