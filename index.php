<?php
include 'articles.php';
include 'catalog.php';
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
                <li><a href="auth/Register.php" class="tbl-ijo" ><i class="fa-solid fa-address-card"></i> Register</a></li>
                <li><a href="index.php"><i class="fa-solid fa-house"></i> Beranda</a></li>
                <li><a href="modules/articles/artikel.php"><i class="fa-solid fa-newspaper"></i> Artikel</a></li>
                <li><a href="modules/pemesanan/katalog.php"><i class="fa-solid fa-basket-shopping"></i> Katalog Obat</a></li>
                <li><a href="Tentang.php"><i class="fa-solid fa-stethoscope"></i> Tentang</a></li>
            </ul>
        </div>
    </nav>

    <main class="content">
        <section class="articles">
            <?php foreach ($articles as $article): ?>
            <article class="article">
                <img src="<?php echo $article['thumbnail']; ?>" alt="<?php echo $article['title']; ?>"
                    class="article-thumbnail">

                <div class="article-content">
                    <h2><?php echo $article['title']; ?></h2>
                    <p><?php echo $article['description']; ?></p>
                    <a href="<?php echo $article['link']; ?>" target="_blank" rel="noopener noreferrer">
                        <a href="<?php echo $article['link']; ?>" target="_blank" rel="noopener noreferrer"
                            class="read-more-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                style="margin-right: 5px;">
                                <path d="M8 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="2"></path>
                            </svg>
                        </a>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </section>


        <aside class="catalog">
            <h2>Katalog Obat</h2>
            <div class="catalog-container">
                <?php foreach ($catalog as $item): ?>
                <div class="catalog-card">
                    <h3><?php echo $item['name']; ?></h3>
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="catalog-image">
                    <p><?php echo $item['description']; ?></p> <br>
                    <div class="catalog-price">
                    <p><?php echo $item['price']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </aside>
    </main>
</body>

<footer>
  <p>&copy; 2024 DocToaja.</p>
</footer>

</html>