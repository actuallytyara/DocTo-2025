<?php
session_start();
require_once('koneksi.php');
require_once('functions.php');


$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$catalogs = empty($keyword) ? getAllCatalog() : searchCatalog($keyword);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Obat - DocTo</title>
    <link rel="stylesheet" href="stylekatalog.css">
    <style>
        .catalog-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        
        .catalog-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 280px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .catalog-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .catalog-item h3 {
            margin: 0 0 10px 0;
            color: #2c7873;
        }
        
        .catalog-item .price {
            font-weight: bold;
            color: #e74c3c;
            font-size: 18px;
            margin: 10px 0;
        }
        
        .catalog-item .type {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .search-container {
            margin: 20px;
            display: flex;
        }
        
        .search-container input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            width: 300px;
        }
        
        .search-container button {
            padding: 8px 15px;
            background-color: #2c7873;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="text-decoration: none; color: white;">DocTo</a></h1>
            <nav>
                <ul>
                    <li><a href="indexcatalog.php">Beranda</a></li>
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
            <h2>Katalog Obat</h2>
            
            <div class="search-container">
                <form action="katalog.php" method="GET">
                    <input type="text" name="keyword" placeholder="Cari obat..." value="<?php echo htmlspecialchars($keyword); ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>
            
            <div class="catalog-container">
                <?php if(empty($catalogs)): ?>
                    <p>Tidak ada produk yang ditemukan.</p>
                <?php else: ?>
                    <?php foreach($catalogs as $item): ?>
                        <div class="catalog-item">
                            <h3><?php echo htmlspecialchars($item['Nama_barang']); ?></h3>
                            <p class="type">Jenis: <?php echo htmlspecialchars($item['Jenis_barang']); ?></p>
                            <p class="price"><?php echo 'Rp ' . number_format($item['Harga_barang'], 0, ',', '.'); ?></p>
                            <a href="detail_katalog.php?id=<?php echo $item['ID_katalog']; ?>" class="btn">Lihat Detail</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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