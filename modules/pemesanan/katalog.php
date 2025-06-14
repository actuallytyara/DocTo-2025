<?php
require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$cart_count = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!$is_logged_in) {
        $error_msg = "Silakan login terlebih dahulu untuk menambahkan obat ke keranjang!";
    } else {
        $id_obat = $_POST['id_obat'];
        $jumlah = $_POST['jumlah'];
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM keranjang WHERE ID_user = ? AND ID_obat = ?");
            $stmt->execute([$user_id, $id_obat]);
            
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE keranjang SET jumlah = jumlah + ? WHERE ID_user = ? AND ID_obat = ?");
                $stmt->execute([$jumlah, $user_id, $id_obat]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO keranjang (ID_user, ID_obat, jumlah) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $id_obat, $jumlah]);
            }
            
            $success_msg = "Obat berhasil ditambahkan ke keranjang!";
        } catch (Exception $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM obat WHERE stok_obat > 0 ORDER BY nama_obat");
$stmt->execute();
$obat_list = $stmt->fetchAll();

if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT SUM(jumlah) as total FROM keranjang WHERE ID_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetch()['total'] ?? 0;
}

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
    <title>Katalog Obat - DocTo</title>
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
        
        .card {
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background:rgb(68, 150, 128);
            color: white;
            text-align: center;
            padding: 20px;
            border: none;
        }
        
        .medicine-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: #37705D;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: #2a5346;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #ff8f00 0%, #ff6f00 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.3);
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
        
        .expired-warning {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
            animation: blink 1.5s infinite;
        }
        
        .expiry-warning {
            background: linear-gradient(135deg,rgb(232, 90, 77) 0%, #ff9800 100%);
            color: white;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }
        
        .price-tag {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stock-info {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
            font-size: 0.85rem;
            margin-right: 10px;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background:rgb(255, 255, 255);
            border-radius: 20px;
            color:#37705D;
        }
        
        .header-section h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            margin-top: 40px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4facfe;
            box-shadow: 0 0 10px rgba(79, 172, 254, 0.3);
        }

        .login-prompt {
            background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
            border-left: 4px solid #4caf50;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.2);
        }

        .login-prompt h5 {
            color: #2e7d32;
            font-weight: 600;
        }

        .login-prompt p {
            color: #388e3c;
            margin-bottom: 15px;
        }

        .login-prompt .btn-success {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .login-prompt .btn-success:hover {
            background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .login-prompt .btn-outline-success {
            border: 2px solid #4caf50;
            color: #4caf50;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .login-prompt .btn-outline-success:hover {
            background: #4caf50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .guest-card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(108, 117, 125, 0.85);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .guest-lock-icon {
            font-size: 4rem;
            color: #6c757d;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: lockPulse 2s ease-in-out infinite;
        }

        @keyframes lockPulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        .guest-card {
            pointer-events: none;
        }

        .guest-card .card-body {
            filter: blur(2px);
        }

        
footer {
    background: linear-gradient(90deg, #37966f, rgb(31, 86, 63) 100%);
    color: white;
    padding: 30px 0 20px 0;
    margin-top: 30px;
    width: 100%;
}


.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}



footer h5 {
    color: #81C784;
    margin-bottom: 15px;
    font-weight: 600;
    font-size: 1.1rem;
}

footer p {
    color: #C8E6C9;
    line-height: 1.6;
    margin-bottom: 15px;
}

footer a {
    color: #C8E6C9;
    text-decoration: none;
    transition: color 0.3s ease;
    display: block;
    padding: 5px 0;
}

footer a:hover {
    color: white;
    text-decoration: none;
}

.list-unstyled {
    list-style: none;
    padding: 0;
    margin: 0;
}

.list-unstyled li {
    margin-bottom: 8px;
}

.text-center {
    text-align: center;
}

.mt-3 {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
    .row {
        flex-direction: column;
        gap: 20px;
    }
    
    .col-md-6, .col-md-3 {
        min-width: 100%;
    }
    
    footer {
        padding: 30px 0;
    }
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
                <a class="nav-link active" href="katalog.php">
                    <i class="fas fa-pills me-1"></i>Katalog Obat
                </a>
                
                <?php if ($is_logged_in): ?>
                    <a class="nav-link position-relative" href="keranjang.php">
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
                <?php else: ?>
                    <a class="nav-link" href="../../auth/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <a class="nav-link" href="../../auth/register.php">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div style="margin-top: 80px;">
        <div class="container">
            <?php if (!$is_logged_in): ?>
                <div class="login-prompt">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3" style="color: #2e7d32;"></i>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Belum Login?</h5>
                            <p class="mb-2">Silakan login terlebih dahulu untuk menambahkan obat ke keranjang dan melakukan pemesanan.</p>
                            <a href="../../auth/login.php" class="btn btn-success me-2">
                                <i class="fas fa-sign-in-alt me-1"></i>Login Sekarang
                            </a>
                            <a href="../../auth/register.php" class="btn btn-outline-success">
                                <i class="fas fa-user-plus me-1"></i>Daftar Akun Baru
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 15px; border: none;">
                    <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 15px; border: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="header-section">
                <h2><i class="fas fa-pills me-3"></i>Katalog Obat</h2>
                <p class="lead mb-0">Temukan obat yang Anda butuhkan dengan mudah hanya di DocTo</p>
            </div>

            <div class="row">
                <?php foreach ($obat_list as $obat): 
                    $expiry_status = checkExpiry($obat['tanggal_kadaluarsa']);
                    $icon = $medicine_icons[$obat['jenis_obat']] ?? $medicine_icons['default'];
                ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card <?= !$is_logged_in ? 'guest-card' : '' ?>">
                            <?php if (!$is_logged_in): ?>
                                <div class="guest-card-overlay">
                                    <i class="fas fa-lock guest-lock-icon"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-header">
                                <i class="<?= $icon ?> medicine-icon"></i>
                                <h5 class="mb-0"><?= htmlspecialchars($obat['nama_obat']) ?></h5>
                                <small><?= htmlspecialchars($obat['jenis_obat']) ?></small>
                            </div>
                            <div class="card-body">
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
                                
                                <p class="card-text mb-3">
                                    <?= htmlspecialchars($obat['deskripsi']) ?>
                                </p>
                                
                                <div class="mb-3">
                                    <span class="price-tag">
                                        <i class="fas fa-tag me-1"></i><?= formatRupiah($obat['harga_obat']) ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="stock-info">
                                        <i class="fas fa-box me-1"></i>Stok: <?= $obat['stok_obat'] ?>
                                    </span>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-calendar me-1"></i>Exp: <?= date('d/m/Y', strtotime($obat['tanggal_kadaluarsa'])) ?>
                                    </small>
                                </div>
                                
                                <?php if ($expiry_status != 'expired'): ?>
                                    <?php if ($is_logged_in): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="id_obat" value="<?= $obat['ID_obat'] ?>">
                                            <input type="number" name="jumlah" class="form-control form-control-sm" 
                                                   value="1" min="1" max="<?= $obat['stok_obat'] ?>" style="width: 80px;">
                                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm flex-grow-1">
                                                <i class="fas fa-cart-plus me-1"></i>Tambah
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="../../auth/login.php" class="btn btn-warning btn-sm w-100">
                                            <i class="fas fa-sign-in-alt me-1"></i>Login untuk Membeli
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-danger btn-sm w-100" disabled>
                                        <i class="fas fa-ban me-1"></i>Tidak Dapat Dibeli
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($obat_list)): ?>
                <div class="empty-state">
                    <i class="fas fa-pills fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Tidak ada obat tersedia</h4>
                    <p class="text-muted">Silakan coba lagi nanti</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>DocTo</h5>
                    <p>Sistem informasi kesehatan untuk semua kebutuhan medis Anda.</p>
                </div>
                <div class="col-md-3">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="artikel.php" class="text-white">Artikel</a></li>
                        <li><a href="katalog_obat.php" class="text-white">Katalog Obat</a></li>
                        <li><a href="tentang.php" class="text-white">Tentang</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled">
                        <li>Email: info@docto.com</li>
                        <li>Telepon: (021) 1234-5678</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-3">
                <p>© 2025 DocTo. All rights reserved.</p>
            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>