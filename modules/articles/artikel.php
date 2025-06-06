<?php
include 'koneksi.php';
include 'functions.php';



$articles = getAllArticles($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Kesehatan - DocTo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style_artikel.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(95deg,#c1d6ce 0%,rgb(255, 255, 255) 100%);
    min-height: 100vh;
    color: #333;
}

.docto-header {
    background: linear-gradient(90deg, #356859, #37966f);
    color: white;
    padding: 15px 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.docto-header .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.docto-header .row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

.docto-header .col-md-3 {
    flex: 0 0 auto;
}

.docto-header .col-md-9 {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

.docto-logo {
    color: #fff;
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
}

.docto-logo img {
    height: 55px;
    margin-right: 10px;
    margin-top: -5px;
}

.docto-logo:hover {
    text-decoration: none;
    color: white !important;
}

nav {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}

.nav-link {
    color: white !important;
    margin: 0 15px;
    padding: 8px 15px;
    border-radius: 20px;
    transition: all 0.3s ease;
    text-decoration: none;
    font-weight: bold;
    font-size: 1rem;
    display: inline-block;
    white-space: nowrap;
}

.nav-link:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    text-decoration: none;
    color: white !important;
}

h2 {
    color: #37966f;
    font-weight: 700;
    margin-bottom: 30px;
    text-align: center;
    font-size: 2.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.article-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    overflow: hidden;
    margin-bottom: 30px;
    height: 100%;
    border: none;
}

.article-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.article-image {
    height: 200px;
    width: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.article-card:hover .article-image {
    transform: scale(1.05);
}

.card-body {
    padding: 25px;
    display: flex;
    flex-direction: column;
    height: calc(100% - 200px);
}

.card-title {
    color: #37966f;
    font-weight: 600;
    font-size: 1.3rem;
    margin-bottom: 15px;
    line-height: 1.4;
}

.card-text {
    color: #666;
    line-height: 1.6;
    flex-grow: 1;
    margin-bottom: 20px;
}

.btn-success {
    background: linear-gradient(90deg, rgb(53, 104, 89), rgb(55, 150, 111));
    border: none;
    border-radius: 15px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-success:hover {
    background: linear-gradient(90deg, #356859, #37966f 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 15px 25px;
    color: #666;
    font-size: 0.9rem;
    margin-top: auto;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding: 0 15px;
    display: flex;
    margin-bottom: 30px;
}

.article-content {
    line-height: 1.8;
    color: #444;
    font-size: 1.1rem;
}

.article-content h1, .article-content h2, .article-content h3 {
    color: #37966f;
    margin: 30px 0 15px 0;
}

.article-content p {
    margin-bottom: 20px;
}

.breadcrumb {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 15px 20px;
}

.breadcrumb-item a {
    color: #37966f;
    text-decoration: none;
    font-weight: 500;
}

.breadcrumb-item a:hover {
    color: #356859;
}

.btn-outline-secondary {
    border: 2px solid #37966f;
    color: #37966f;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    background: #37966f;
    color: white;
    transform: translateY(-2px);
}

footer {
   background: linear-gradient(90deg, #37966f ,rgb(31, 86, 63) 100%);
    color: white;
    padding: 40px 0;
    margin-top: 50px;
}

footer h5 {
    color: #81C784;
    margin-bottom: 20px;
    font-weight: 600;
}

footer a {
    color: #C8E6C9;
    text-decoration: none;
    transition: color 0.3s ease;
}

footer a:hover {
    color: white;
    text-decoration: none;
}

footer ul li {
    margin-bottom: 8px;
}

@media (max-width: 991px) {
    .docto-header .row {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .docto-header .col-md-9 {
        justify-content: center;
    }
    
    nav {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .container {
        margin: 20px auto;
        padding: 20px;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    .docto-logo {
        font-size: 24px;
    }
    
    .docto-logo img {
        height: 45px;
    }
    
    .nav-link {
        margin: 2px;
        padding: 6px 12px;
        font-size: 13px;
    }
}

@media (max-width: 576px) {
    .docto-header {
        padding: 10px 0;
    }
    
    .nav-link {
        margin: 1px;
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .docto-logo {
        font-size: 20px;
    }
    
    .docto-logo img {
        height: 35px;
    }
}

@media (min-width: 769px) and (max-width: 991px) {
    .col-md-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

.article-card {
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
}

.article-card:nth-child(1) { animation-delay: 0.1s; }
.article-card:nth-child(2) { animation-delay: 0.2s; }
.article-card:nth-child(3) { animation-delay: 0.3s; }
.article-card:nth-child(4) { animation-delay: 0.4s; }
.article-card:nth-child(5) { animation-delay: 0.5s; }
.article-card:nth-child(6) { animation-delay: 0.6s; }

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
    <!-- Header -->
    <header class="docto-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="index.php" class="text-white text-decoration-none">
                        <h1 class="docto-logo"><img src="../../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;"> DocTo</h1>
                    </a>
                </div>
                <div class="col-md-9">
                    <nav class="text-right">
                        <a class="nav-link d-inline-block" href="../../index.php">Beranda</a>
                        <a class="nav-link d-inline-block" href="artikel.php">Artikel</a>
                        <a class="nav-link d-inline-block" href="katalog_obat.php">Katalog Obat</a>
                        <a class="nav-link d-inline-block" href="tentang.php">Tentang</a>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a class="nav-link d-inline-block" href="admin_artikel.php">Kelola Artikel</a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <h2 class="mb-4">Artikel Kesehatan</h2>
        
        <div class="row">
    <?php foreach ($articles as $article): ?>
        <?php if ($article['status'] == 'published'): ?>
            <div class="col-md-4">
                <div class="card article-card">
                    <?php if (!empty($article['gambar'])): ?>
                        <img src="uploads/<?= $article['gambar'] ?>" class="article-image" alt="<?= $article['judul'] ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/350x200" class="article-image" alt="Placeholder">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= $article['judul'] ?></h5>
                        <p class="card-text"><?= substr(strip_tags($article['isi_artikel']), 0, 100) ?>...</p>
                        <a href="lihat_artikel.php?slug=<?= $article['slug'] ?>" class="btn btn-success">Baca Selengkapnya</a>
                    </div>
                    <div class="card-footer">
                        <?= date('d F Y', strtotime($article['tanggal_posting'])) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>