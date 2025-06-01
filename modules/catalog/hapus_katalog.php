<?php
session_start();
require_once('koneksi.php');
require_once('functions.php');


if (!isset($_GET['id'])) {
    header('Location: katalog.php');
    exit;
}

$id = intval($_GET['id']);


if (isset($_POST['confirm'])) {
    if (deleteCatalog($id)) {
       
        header('Location: katalog.php?deleted=1');
        exit;
    } else {
       
        $error_message = "Gagal menghapus produk. Silakan coba lagi.";
    }
} elseif (isset($_POST['cancel'])) {
    
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
    <title>Hapus Produk - DocTo</title>
    <link rel="stylesheet" href="style_dokter.css">
    <style>
        .confirmation-box {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .confirmation-box h3 {
            color: #721c24;
            margin-top: 0;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
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
            <h2>Hapus Produk</h2>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="confirmation-box">
                <h3>Konfirmasi Hapus</h3>
                <p>Apakah Anda yakin ingin menghapus produk berikut?</p>
                <ul>
                    <li><strong>Nama:</strong> <?php echo htmlspecialchars($item['Nama_barang']); ?></li>
                    <li><strong>Harga:</strong> Rp <?php echo number_format($item['Harga_barang'], 0, ',', '.'); ?></li>
                    <li><strong>Jenis:</strong> <?php echo htmlspecialchars($item['Jenis_barang']); ?></li>
                </ul>
                <p><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
                
                <form method="POST" action="hapus_katalog.php?id=<?php echo $id; ?>">
                    <button type="submit" name="confirm" class="btn btn-danger">Ya, Hapus Produk</button>
                    <button type="submit" name="cancel" class="btn btn-secondary">Tidak, Batal</button>
                </form>
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
