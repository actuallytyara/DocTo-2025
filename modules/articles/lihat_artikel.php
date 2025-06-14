<?php
session_start(); 
include 'koneksi.php';
include 'functions.php';

if (!isset($_GET['slug'])) {
    header("Location: artikel.php");
    exit();
}

$slug = $_GET['slug'];
$article = getArticleBySlug($conn, $slug);

if (!$article) {
    header("Location: artikel.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['judul']) ?> - DocTo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(95deg,rgb(193, 214, 206) 0%,rgb(255, 255, 255) 100%);
    min-height: 100vh;
    color: #333;
}

/* Header Styles */
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
    color: #2E7D32;
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
    color:rgb(44, 121, 86);
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
    background: linear-gradient(90deg, #356859, #37966f 100%);
    border: none;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-success:hover {
    background: linear-gradient(90deg,rgb(44, 86, 73),rgb(46, 122, 91) 100%);
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

/* Article Detail Page - Perbaikan untuk gambar */
.article-detail-image {
    max-height: 400px;
    object-fit: cover;
    width: 100%;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
    display: block;
}

.article-detail-image:hover {
    transform: scale(1.02);
}

.article-content {
    line-height: 1.8;
    color: #444;
    font-size: 1.1rem;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.article-content h1, .article-content h2, .article-content h3 {
    color:rgb(46, 125, 91);
    margin: 30px 0 15px 0;
    font-weight: 600;
}

.article-content p {
    margin-bottom: 20px;
    text-align: justify;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 20px 0;
}

article h1 {
    color:rgb(45, 123, 79);
    font-weight: 700;
    font-size: 2.5rem;
    margin-bottom: 20px;
    line-height: 1.3;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.text-muted small {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    color: #666;
    border: 1px solid #dee2e6;
}

.breadcrumb {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 15px 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #dee2e6;
}

.breadcrumb-item {
    font-weight: 500;
}

.breadcrumb-item a {
    color: #356859;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
    color:rgb(76, 175, 127);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #666;
    font-weight: 500;
}

.btn-outline-secondary {
    border: 2px solidrgb(46, 125, 97);
    color:rgb(46, 125, 92);
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    background: white;
    box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);
}

.btn-outline-secondary:hover {
    background: #356859;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.3);
    text-decoration: none;
}

.col-md-8.offset-md-2 {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    margin-top: 30px;
    margin-bottom: 30px;
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
    
    .docto-header .row {
        text-align: center;
    }
    
    .docto-header .col-md-9 {
        margin-top: 15px;
    }
    
    .nav-link {
        margin: 5px;
        display: inline-block;
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
    background: linear-gradient(90deg, #356859, #37966f 100%);     
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(90deg, #356859, #37966f 100%);
}

.image-error {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    color: #6c757d;
    margin-bottom: 30px;
}

.image-error i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #dee2e6;
}
    </style>
</head>
<body>
    <header class="docto-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="index.php" class="text-white text-decoration-none">
                        <h1 class="docto-logo"> <img src="../../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;" onerror="this.style.display='none';">DocTo</h1>
                    </a>
                </div>
                <div class="col-md-9">
                    <nav class="text-right">
                        <a class="nav-link d-inline-block" href="index.php">Beranda</a>
                        <a class="nav-link d-inline-block" href="artikel.php">Artikel</a>
                        <a class="nav-link d-inline-block" href="katalog_obat.php">Katalog Obat</a>
                        <a class="nav-link d-inline-block" href="tentang.php">Tentang</a>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a class="nav-link d-inline-block" href="admin_artikel.php">Kelola Artikel</a>
                        <?php endif; ?>
                        <a class="nav-link d-inline-block" href="logout.php">Logout</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="artikel.php">Artikel</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($article['judul']) ?></li>
                    </ol>
                </nav>
                
                <article>
                    <h1 class="mb-4"><?= htmlspecialchars($article['judul']) ?></h1>
                    <p class="text-muted">
                        <small>Dipublikasikan pada <?= date('d F Y', strtotime($article['tanggal_posting'])) ?></small>
                    </p>
                    
                    <?php 
                    if (!empty($article['gambar'])):
                        $imagePath = '';
                        $possiblePaths = [
                            'uploads/' . $article['gambar'],
                            'assets/images/' . $article['gambar'],
                            'images/' . $article['gambar'],
                            $article['gambar']
                        ];
                        
                        foreach ($possiblePaths as $path) {
                            if (file_exists($path)) {
                                $imagePath = $path;
                                break;
                            }
                        }
                        
                        if ($imagePath): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 class="article-detail-image" 
                                 alt="<?= htmlspecialchars($article['judul']) ?>"
                                 onerror="this.parentElement.innerHTML='<div class=\'image-error\'><i>📷</i><p>Gambar tidak dapat dimuat</p><small>File: <?= htmlspecialchars($article['gambar']) ?></small></div>'">
                        <?php else: ?>
                            <div class="image-error">
                                <i>📷</i>
                                <p>Gambar tidak ditemukan</p>
                                <small>File: <?= htmlspecialchars($article['gambar']) ?></small>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="article-content">
                        <?= $article['isi_artikel'] ?>
                    </div>
                </article>
                
                <div class="mt-5">
                    <a href="artikel.php" class="btn btn-outline-secondary">&larr; Kembali ke Daftar Artikel</a>
                </div>
            </div>
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