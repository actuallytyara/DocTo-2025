<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt_artikel = $pdo->prepare("SELECT * FROM artikel WHERE status = 'published' ORDER BY tanggal_posting DESC LIMIT 6");
    $stmt_artikel->execute();
    $articles = $stmt_artikel->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_obat = $pdo->prepare("SELECT * FROM obat WHERE stok_obat > 0 ORDER BY nama_obat ASC LIMIT 8");
    $stmt_obat->execute();
    $catalog = $stmt_obat->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    $articles = [];
    $catalog = [];
}

function truncateText($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTo - Artikel dan Katalog Obat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav>
        <div class="navbar">
             <h1 class="logo" style="display: flex; align-items: center;">
             <img src="assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;">
             <span style="font-size: 32px; margin-top: -3px;">DocTo</span>
              </h1>
            <ul class="nav-links">
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                    <li><a href="auth/berhasil_login.php" class="tbl-ijo">
                        <i class="fa-solid fa-user"></i> 
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a></li>
                    <li><a href="auth/logout.php" class="tbl-merah">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a></li>
                <?php else: ?>
                    <li><a href="auth/login.php" class="tbl-biru">
                        <i class="fa-solid fa-sign-in-alt"></i> Login
                    </a></li>
                    <li><a href="auth/Register.php" class="tbl-ijo">
                        <i class="fa-solid fa-address-card"></i> Register
                    </a></li>
                <?php endif; ?>
                
                <li><a href="index.php"><i class="fa-solid fa-house"></i> Beranda</a></li>
                <li><a href="modules/articles/artikel.php"><i class="fa-solid fa-newspaper"></i> Artikel</a></li>
                <li><a href="modules/pemesanan/katalog.php"><i class="fa-solid fa-basket-shopping"></i> Katalog Obat</a></li>
                <li><a href="Tentang.php"><i class="fa-solid fa-stethoscope"></i> Tentang</a></li>
            </ul>
        </div>
    </nav>

    <main class="content">
        <section class="articles">
            <h2>Artikel Kesehatan Terbaru</h2>
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                <article class="article">
                    <?php if (!empty($article['gambar'])): ?>
                        <img src="modules/articles/images/<?php echo htmlspecialchars($article['gambar']); ?>" 
                             alt="<?php echo htmlspecialchars($article['judul']); ?>"
                             class="article-thumbnail">
                    <?php else: ?>
                        <img src="assets/images/default-article.jpg" 
                             alt="<?php echo htmlspecialchars($article['judul']); ?>"
                             class="article-thumbnail">
                    <?php endif; ?>

                    <div class="article-content">
                        <h3><?php echo htmlspecialchars($article['judul']); ?></h3>
                        <p class="article-date">
                            <i class="fa-regular fa-calendar"></i> 
                            <?php echo formatTanggal($article['tanggal_posting']); ?>
                        </p>
                        <p><?php echo truncateText($article['isi_artikel'], 200); ?></p>
                        <a href="modules/articles/lihat_artikel.php?slug=<?php echo urlencode($article['slug']); ?>" 
                           class="read-more-btn">
                            Baca Selengkapnya
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                style="margin-left: 5px;">
                                <path d="M8 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="2"></path>
                            </svg>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-content">Belum ada artikel yang tersedia.</p>
            <?php endif; ?>
            
            <div class="view-all">
                <a href="modules/articles/artikel.php" class="view-all-btn">
                    <i class="fa-solid fa-newspaper"></i> Lihat Semua Artikel
                </a>
            </div>
        </section>

        <aside class="catalog">
            <h2>Katalog Obat</h2>
            <div class="catalog-container">
                <?php if (!empty($catalog)): ?>
                    <?php foreach ($catalog as $item): ?>
                    <div class="catalog-card">
                        <h3><?php echo htmlspecialchars($item['nama_obat']); ?></h3>
                        <div class="catalog-image-container">
                            <?php 
                            $default_image = '';
                            switch(strtolower($item['jenis_obat'])) {
                                case 'tablet':
                                    $default_image = 'assets/images/obat/tablet-default.jpg';
                                    break;
                                case 'kapsul':
                                    $default_image = 'assets/images/obat/kapsul-default.jpg';
                                    break;
                                case 'sirup':
                                    $default_image = 'assets/images/obat/sirup-default.jpg';
                                    break;
                                default:
                                    $default_image = 'assets/images/obat/obat-default.jpg';
                            }
                            ?>
                            <img src="<?php echo $default_image; ?>" 
                                 alt="<?php echo htmlspecialchars($item['nama_obat']); ?>" 
                                 class="catalog-image">
                        </div>
                        
                        <div class="catalog-info">
                            <p class="catalog-type">
                                <i class="fa-solid fa-pills"></i> 
                                <?php echo htmlspecialchars($item['jenis_obat']); ?>
                            </p>
                            <p class="catalog-description">
                                <?php echo htmlspecialchars(truncateText($item['deskripsi'], 80)); ?>
                            </p>
                            <p class="catalog-stock">
                                <i class="fa-solid fa-boxes-stacked"></i> 
                                Stok: <?php echo $item['stok_obat']; ?>
                            </p>
                            <?php if (!empty($item['tanggal_kadaluarsa'])): ?>
                            <p class="catalog-expired">
                                <i class="fa-solid fa-calendar-xmark"></i> 
                                Exp: <?php echo formatTanggal($item['tanggal_kadaluarsa']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="catalog-price">
                            <p><?php echo formatRupiah($item['harga_obat']); ?></p>
                        </div>
                        
                        <div class="catalog-actions">
                            <a href="modules/pemesanan/detail_obat.php?id=<?php echo $item['ID_obat']; ?>" 
                               class="btn-detail">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="modules/pemesanan/keranjang.php?add=<?php echo $item['ID_obat']; ?>" 
                                   class="btn-cart">
                                    <i class="fa-solid fa-cart-plus"></i> Keranjang
                                </a>
                            <?php else: ?>
                                <a href="auth/login.php" class="btn-cart">
                                    <i class="fa-solid fa-sign-in-alt"></i> Login untuk Pesan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-content">Belum ada obat yang tersedia.</p>
                <?php endif; ?>
            </div>
            
            <div class="view-all">
                <a href="modules/pemesanan/katalog.php" class="view-all-btn">
                    <i class="fa-solid fa-basket-shopping"></i> Lihat Semua Obat
                </a>
            </div>
        </aside>
    </main>

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
                        <li><a href="modules/articles/artikel.php" class="text-white">Artikel</a></li>
                        <li><a href="modules/pemesanan/katalog.php" class="text-white">Katalog Obat</a></li>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            const images = document.querySelectorAll('img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                imageObserver.observe(img);
            });
        });
    </script>
</body>
</html>