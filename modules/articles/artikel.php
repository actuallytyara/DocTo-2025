<?php
session_start();
include 'koneksi.php';
include 'functions.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$articles = getAllArticles($conn);

function getImagePath($imageName) {
    if (empty($imageName)) {
        return null;
    }
    
    $imageName = trim($imageName);
    
    $possiblePaths = [
        'uploads/' . $imageName,
        'images/' . $imageName,
        '../images/' . $imageName,
        '../../images/' . $imageName,
        'assets/images/' . $imageName,
        '../assets/images/' . $imageName,
        '../../assets/images/' . $imageName,
        'modules/articles/images/' . $imageName,
        '../modules/articles/images/' . $imageName,
        '../../modules/articles/images/' . $imageName
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}

function debugImagePath($imageName) {
    if (empty($imageName)) {
        return "No image name provided";
    }
    
    $imageName = trim($imageName);
    $currentDir = getcwd();
    
    $possiblePaths = [
        'uploads/' . $imageName,
        'images/' . $imageName,
        '../images/' . $imageName,
        '../../images/' . $imageName,
        'assets/images/' . $imageName,
        '../assets/images/' . $imageName,
        '../../assets/images/' . $imageName
    ];
    
    $debug = "Current directory: $currentDir\n";
    $debug .= "Looking for image: $imageName\n\n";
    
    foreach ($possiblePaths as $path) {
        $exists = file_exists($path) ? 'EXISTS' : 'NOT FOUND';
        $debug .= "Path: $path - $exists\n";
    }
    
    return $debug;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Kesehatan - DocTo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            color: #37966f;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5rem;
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
            background-color: #f8f9fa;
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

        .image-placeholder {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 3rem;
            height: 200px;
        }

        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-line;
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

        .alert {
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        @media (min-width: 769px) and (max-width: 991px) {
            .col-md-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
    </style>
</head>
<body>
    <header class="docto-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="../../index.php" class="text-white text-decoration-none">
                        <h1 class="docto-logo">
                            <img src="../../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;" onerror="this.style.display='none';"> 
                            DocTo
                        </h1>
                    </a>
                </div>
                <div class="col-md-9">
                    <nav class="text-right">
                        <a class="nav-link d-inline-block" href="../../index.php"><i class="fa-solid fa-house"></i> Beranda</a>
                        <a class="nav-link d-inline-block" href="artikel.php"><i class="fa-solid fa-newspaper"></i> Artikel</a>
                        <a class="nav-link d-inline-block" href="katalog_obat.php"><i class="fa-solid fa-basket-shopping"></i> Katalog Obat</a>
                        <a class="nav-link d-inline-block" href="tentang.php"><i class="fa-solid fa-stethoscope"></i> Tentang</a>
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
        
        <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
            <div class="debug-info">
                <h5>Debug Information:</h5>
                <p>Current working directory: <?= getcwd() ?></p>
                <p>Database connection: <?= $conn ? 'Connected' : 'Failed' ?></p>
                <p>Articles found: <?= count($articles) ?></p>
                <?php 
                $debugCount = 0;
                foreach ($articles as $article): 
                    if ($article['status'] == 'published' && $debugCount < 3): 
                        $debugCount++;
                ?>
                        <h6>Article <?= $debugCount ?>: <?= htmlspecialchars($article['judul']) ?></h6>
                        <div class="debug-info"><?= debugImagePath($article['gambar']) ?></div>
                <?php 
                    endif; 
                endforeach; 
                ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <?php 
            $publishedArticles = array_filter($articles, function($article) {
                return $article['status'] == 'published';
            });
            
            if (!empty($publishedArticles)): 
                foreach ($publishedArticles as $article): 
            ?>
                <div class="col-md-4">
                    <div class="card article-card">
                        <?php 
                        $imagePath = getImagePath($article['gambar']);
                        $imageName = trim($article['gambar']);
                        ?>
                        
                        <?php if ($imagePath && file_exists($imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 class="article-image" 
                                 alt="<?= htmlspecialchars($article['judul']) ?>"
                                 onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'article-image image-placeholder\'><i class=\'fas fa-image\'></i></div>';">
                        <?php else: ?>
                            <div class="article-image image-placeholder">
                                <i class="fas fa-image"></i>
                                <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                                    <small style="position: absolute; bottom: 5px; left: 5px; color: red; font-size: 10px;">
                                        Image: <?= htmlspecialchars($imageName) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($article['judul']) ?></h5>
                            <p class="card-text"><?= substr(strip_tags($article['isi_artikel']), 0, 100) ?>...</p>
                            <a href="lihat_artikel.php?slug=<?= urlencode($article['slug']) ?>" class="btn btn-success">Baca Selengkapnya</a>
                        </div>
                        <div class="card-footer">
                            <?= date('d F Y', strtotime($article['tanggal_posting'])) ?>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach; 
            else: 
            ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle"></i> Belum Ada Artikel</h4>
                        <p>Saat ini belum ada artikel yang dipublikasikan. Silakan kembali lagi nanti.</p>
                        <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                            <small class="text-muted">Total articles in database: <?= count($articles) ?></small>
                        <?php endif; ?>
                    </div>
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>