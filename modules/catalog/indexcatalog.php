<?php
session_start();
require_once('koneksi.php');
require_once('functions.php');


$query = "SELECT * FROM catalog ORDER BY ID_katalog LIMIT 6";
$result = mysqli_query($koneksi, $query);

$catalogs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $catalogs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTo - Katalog Obat Online</title>
    <link rel="stylesheet" href="style_dokter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

    
        header {
            background: linear-gradient(90deg, #356859, #37966f);
            color: #fff;
            padding: 15px 0px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 28px;
        }

        header h1 a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        header h1 a i {
            margin-right: 10px;
            font-size: 24px;
        }

        header .logo {
            display: flex;
            align-items: center;
        }

        header .logo i {
            font-size: 24px;
            margin-right: 10px;
        }

        header nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        header nav ul li {
            margin-left: 20px;
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        header nav ul li a:hover,
        header nav ul li a.active {
            background-color: rgba(255,255,255,0.2);
        }

        header .user-actions {
            display: flex;
            align-items: center;
        }

        header .user-actions a {
            margin-left: 15px;
            color: white;
            font-size: 18px;
        }

        /* Hero Section - Sesuai Figma */
        .hero {
            background: #f9f9f9;;
            color: white;
            padding: 40px 0;
            text-align: left;
        }

        .hero h2 {
            font-size: 28px;
            margin-bottom: 15px;
            text-align:left;
            padding: auto;
        }

        .hero p {
            font-size: 16px;
            margin-bottom: 25px;
            max-width: 800px;
            margin-left: 0;
            margin-right: auto;
            text-align: left;
        }

        .hero-btn {
            display: inline-block;
            background-color: #e8f1ef;
            color: #356859;
            margin-left: 0;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
           
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Featured Products - Sesuai Figma */
        .featured-products {
            padding: 40px 0;
            background-color: #f9f9f9;
        }

        .featured-products h2 {
            text-align: left;
            margin-bottom: 30px;
            color: #356859;
            font-size: 24px;
        }

        .product-slider {
            position: relative;
            margin-bottom: 30px;
            background-color: #e8f1ef;
            padding: 30px;
            border-radius: 8px;
        }

        .product-grid {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 10px 0;
            scroll-behavior: smooth;
        }

        .product-grid::-webkit-scrollbar {
            height: 5px;
        }

        .product-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .product-grid::-webkit-scrollbar-thumb {
            background: #356859;
            border-radius: 10px;
        }

        .product-card {
            min-width: 220px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-info h3 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #356859;
            font-size: 16px;
        }

        .product-info .description {
            color: #666;
            margin-bottom: 12px;
            font-size: 13px;
            line-height: 1.4;
            height: 55px;
            overflow: hidden;
        }

        .product-info .price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .product-info .btn {
            display: block;
            background-color: #356859;
            color: white;
            text-align: center;
            padding: 8px 0;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .product-info .btn:hover {
            background-color: #37966f;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: #356859;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            z-index: 1;
            border: none;
        }

        .slider-prev {
            left: 10px;
        }

        .slider-next {
            right: 10px;
        }

        /* View All Button */
        .view-all {
            text-align: center;
            margin-top: 30px;
        }

        .view-all .btn {
            display: inline-block;
            background-color: #356859;
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .view-all .btn:hover {
            background-color: #37966f;
        }

        /* About Section */
        .about-section {
            padding: 40px 0;
            text-align: center;
            background-color: white;
        }

        .about-section h2 {
            color: #356859;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .about-section p {
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            font-size: 15px;
        }

        /* Footer */
        footer {
            background: linear-gradient(90deg, #356859, #37966f);
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
            }
            
            header nav ul {
                margin-top: 15px;
                justify-content: center;
            }
            
            header nav ul li {
                margin: 0 10px;
            }
            
            .product-grid {
                flex-wrap: nowrap;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header dengan logo dan navigasi -->
    <header>
        <div class="container">
              <h1>
                 <a href="indexcatalog.php">
                 <img src="images/logo_docto.png" alt="DocTo Logo" style="height: 55px; vertical-align: middle; margin-right: 10px;">
                 <span style="font-size: 32px; margin-top: 3px;">DocTo</span>
                 </a>
               </h1>
            <nav>
                <ul>
                    <li><a href="/projekukl/index.php" class="active">Beranda</a></li>
                    <li><a href="../articles/artikel.php">Artikel</a></li>
                    <li><a href="katalog.php">Katalog Obat</a></li>
                    <li><a href="tentang.php">Tentang</a></li>
                    <li><a href="#">Login</a></li>
                    <li><a href="#">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero section -->
    <section class="hero">
        <div class="container">
            <h2>Katalog Obat</h2>
            <p>Katalog obat online terlengkap dengan informasi kesehatan terpercaya. Temukan obat yang Anda butuhkan dengan mudah dan dapatkan informasi lengkap tentang penggunaannya.</p>
            <a href="katalog.php" class="hero-btn"><i class="fas fa-pills"></i> Lihat Katalog Obat</a>
        </div>
    </section>

    <!-- Featured products section -->
    <section class="featured-products">
        <div class="container">
            <h2>Obat Unggulan</h2>
            <div class="product-slider">
                <button class="slider-arrow slider-prev" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                <div class="product-grid" id="productGrid">
                    <?php if(empty($catalogs)): ?>
                        <p>Tidak ada produk unggulan saat ini.</p>
                    <?php else: ?>
                        <?php foreach($catalogs as $item): ?>
                            <div class="product-card">
                                <img src="images/<?php echo htmlspecialchars($item['Nama_barang']); ?>.png" alt="<?php echo htmlspecialchars($item['Nama_barang']); ?>" onerror="this.src='images/default.png'">
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($item['Nama_barang']); ?></h3>
                                    <p class="description">
                                        <?php 
                                        // Add a sample description if not available in database
                                        echo isset($item['Deskripsi']) ? htmlspecialchars($item['Deskripsi']) : 'Obat ' . htmlspecialchars($item['Jenis_barang']) . ' berkualitas tinggi untuk kebutuhan kesehatan Anda.'; 
                                        ?>
                                    </p>
                                    <p class="price">Rp <?php echo number_format($item['Harga_barang'], 0, ',', '.'); ?></p>
                                    <a href="detail_katalog.php?id=<?php echo $item['ID_katalog']; ?>" class="btn"><i class="fas fa-eye"></i> Lihat Detail</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button class="slider-arrow slider-next" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="view-all">
                <a href="katalog.php" class="btn"><i class="fas fa-list"></i> Lihat Semua Produk</a>
            </div>
        </div>
    </section>

    <!-- About section -->
    <section class="about-section">
        <div class="container">
            <h2>Tentang DocTo</h2>
            <p>DocTo adalah platform kesehatan online yang menyediakan informasi obat-obatan terpercaya dan artikel kesehatan untuk membantu Anda menjaga kesehatan dengan lebih baik.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> DocTo - Katalog Obat Online</p>
        </div>
    </footer>

    <!-- JavaScript untuk slider -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productGrid = document.getElementById('productGrid');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            nextBtn.addEventListener('click', function() {
                productGrid.scrollBy({
                    left: 250,
                    behavior: 'smooth'
                });
            });
            
            prevBtn.addEventListener('click', function() {
                productGrid.scrollBy({
                    left: -250,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>