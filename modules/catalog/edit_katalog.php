<?php
session_start();
require_once('koneksi.php');
require_once('functions.php');

$success_message = '';
$error_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_barang'];
    $harga = $_POST['harga_barang'];
    $jenis = $_POST['jenis_barang'];
    $user_id = $_SESSION['user_id'];
    
  
    $image_path = 'images/default.jpg'; 
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/';
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            $error_message = "Gagal mengunggah gambar produk.";
        }
    }
    
   
    if (empty($error_message)) {
        if (addCatalog($nama, $harga, $jenis, $user_id)) {
            $success_message = "Produk berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan produk ke database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - DocTo</title>
    <link rel="stylesheet" href="stylekatalog.css">
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
            <h2>Tambah Produk Baru</h2>
            
            <?php if($success_message): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $success_message; ?>
                    <p>Kembali ke <a href="katalog.php">Katalog</a></p>
                </div>
            <?php endif; ?>
            
            <?php if($error_message): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="tambah_katalog.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_barang">Nama Obat</label>
                    <input type="text" id="nama_barang" name="nama_barang" required>
                </div>
                
                <div class="form-group">
                    <label for="harga_barang">Harga (Rp)</label>
                    <input type="number" id="harga_barang" name="harga_barang" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="jenis_barang">Jenis Obat</label>
                    <input type="text" id="jenis_barang" name="jenis_barang" required>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar Produk (opsional)</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                </div>
                
                <button type="submit" class="btn">Tambah Produk</button>
                <a href="katalog.php" style="margin-left: 10px; text-decoration: none; color: #333;">Batal</a>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> DocTo - Katalog Obat Online</p>
        </div>
    </footer>
</body>
</html>
