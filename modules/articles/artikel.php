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
        .article-card {
            margin-bottom: 30px;
            transition: transform 0.3s;
            height: 100%;
        }
        .article-card:hover {
            transform: translateY(-5px);
        }
        .article-image {
            height: 200px;
            object-fit: cover;
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
        
        /* Fix untuk kolom yang rata */
        .article-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        .article-col {
            display: flex;
            margin-bottom: 30px;
            padding: 0 15px;
        }
        .card {
            display: flex;
            flex-direction: column;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
        }
        .card-text {
            flex: 1;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .card-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .article-col {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 25px;
            }
            .article-row {
                margin: 0 -10px;
            }
            .article-col {
                padding: 0 10px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 991px) {
            .article-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        @media (min-width: 992px) {
            .article-col {
                flex: 0 0 33.333333%;
                max-width: 33.333333%;
            }
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
        <h2 class="mb-4">Artikel Kesehatan</h2>
        
        <div class="row">
            <?php foreach ($articles as $article): ?>
                <?php if ($article['status'] == 'published'): ?>
                    <div class="col-md-4">
                        <div class="card article-card">
                            <?php if (!empty($article['gambar'])): ?>
                                <img src="uploads/<?= $article['gambar'] ?>" class="card-img-top article-image" alt="<?= $article['judul'] ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/350x200" class="card-img-top article-image" alt="Placeholder">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= $article['judul'] ?></h5>
                                <p class="card-text"><?= substr(strip_tags($article['isi_artikel']), 0, 100) ?>...</p>
                                <a href="lihat_artikel.php?slug=<?= $article['slug'] ?>" class="btn btn-success">Baca Selengkapnya</a>
                            </div>
                            <div class="card-footer text-muted">
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