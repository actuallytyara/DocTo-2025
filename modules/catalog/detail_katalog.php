<?php
session_start();
require_once('koneksi.php');
require_once('functions.php');


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: katalog.php');
    exit;
}


$item = getCatalogById($id);

if (!$item) {
    header('Location: katalog.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['Nama_barang']); ?> - DocTo</title>
    <link rel="stylesheet" href="style_dokter.css">
    <style>
        .product-detail {
            display: flex;
            gap: 30px;
            margin: 30px 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .product-image {
            flex: 0 0 40%;
        }
        
        .product-image img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .product-info {
            flex: 0 0 60%;
        }
        
        .product-info h2 {
            color: #2c7873;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .product-price {
            font-size: 22px;
            font-weight: bold;
            color: #e74c3c;
            margin: 15px 0;
        }
        
        .product-type {
            background-color: #eaf2f8;
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: #3498db;
            margin-bottom: 15px;
        }
        
        .back-btn {
            display: inline-block;
            background-color: #7f8c8d;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .buy-btn {
            display: inline-block;
            background-color:rgb(66, 164, 87);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="text-decoration: none; color: white;">DocTo</a></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="artikel.php">Artikel</a></li>
                    <li><a href="katalog.php" class="active">Katalog Obat</a></li>
                    <li><a href="tentang.php">Tentang</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="product-detail">
                <div class="product-image">
                    <img src="images/<?php echo htmlspecialchars($item['Nama_barang']); ?>.png" alt="<?php echo htmlspecialchars($item['Nama_barang']); ?>" onerror="this.src='images/default.png'">
                </div>
                <div class="product-info">
                    <h2><?php echo htmlspecialchars($item['Nama_barang']); ?></h2>
                    <div class="product-type"><?php echo htmlspecialchars($item['Jenis_barang']); ?></div>
                    <div class="product-price"><?php echo 'Rp ' . number_format($item['Harga_barang'], 0, ',', '.'); ?></div>
                    
                    <p>Informasi detail tentang produk ini akan ditampilkan di sini.</p>
                    
                    <div class="product-actions">
                        <a href="katalog.php" class="back-btn">Kembali</a>
                        <a href="#" class="buy-btn">Beli Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> DocTo - Katalog Obat Online</p>
        </div>
    </footer>
</body>
</html>
