<?php

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
    <title><?= $article['judul'] ?> - DocTo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .article-image {
            max-height: 400px;
            object-fit: cover;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .article-content {
            line-height: 1.8;
        }
        .docto-header {
            background-color: #2a7d60;
            color: white;
            padding: 15px 0;
        }
        .docto-logo {
            font-size: 28px;
            font-weight: bold;
        }
        .nav-link {
            color: white !important;
            margin: 0 10px;
        }
    </style>
</head>
<body>
  
    <header class="docto-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="index.php" class="text-white text-decoration-none">
                        <h1 class="docto-logo">DocTo</h1>
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
                        <li class="breadcrumb-item active" aria-current="page"><?= $article['judul'] ?></li>
                    </ol>
                </nav>
                
                <article>
                    <h1 class="mb-4"><?= $article['judul'] ?></h1>
                    <p class="text-muted">
                        <small>Dipublikasikan pada <?= date('d F Y', strtotime($article['tanggal_posting'])) ?></small>
                    </p>
                    
                    <?php if (!empty($article['gambar'])): ?>
                        <img src="uploads/<?= $article['gambar'] ?>" class="article-image" alt="<?= $article['judul'] ?>">
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