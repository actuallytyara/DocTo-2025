<?php

session_start();
require_once('koneksi.php');
require_once('functions.php');



$success_message = '';
$error_message = '';


if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    if (deleteCatalog($id)) {
        $success_message = "Produk berhasil dihapus.";
    } else {
        $error_message = "Gagal menghapus produk.";
    }
}


if (isset($_POST['add'])) {
    $nama = $_POST['nama_barang'];
    $harga = $_POST['harga_barang'];
    $jenis = $_POST['jenis_barang'];
    $user_id = null;
    if (isset($_SESSION['ID_user'])) {
    $user_id = $_SESSION['ID_user'];
    } elseif (isset($_SESSION['id_user'])) {
    $user_id = $_SESSION['id_user'];
    } elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    }


    
    if (addCatalog($nama, $harga, $jenis, $user_id)) {
        $success_message = "Produk berhasil ditambahkan.";
    } else {
        $error_message = "Gagal menambahkan produk.";
    }
}


if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama = $_POST['nama_barang'];
    $harga = $_POST['harga_barang'];
    $jenis = $_POST['jenis_barang'];
    
    if (updateCatalog($id, $nama, $harga, $jenis)) {
        $success_message = "Produk berhasil diperbarui.";
    } else {
        $error_message = "Gagal memperbarui produk.";
    }
}



$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_item = getCatalogById($edit_id);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Katalog Obat - DocTo</title>
    <link rel="stylesheet" href="styleadmin.css">
    <style>
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-submit {
            background-color:#356859;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
       .btn-add {
        background-color:#356859;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
       }

       .btn-add, .btn-submit {
         transition: background-color 0.3s;
       }

       .btn-submit:hover, .btn-add:hover {
         background-color: #1a5642
       }

        .catalog-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .catalog-table th, .catalog-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .catalog-table th {
            background-color: #f2f2f2;
        }
        
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
            text-decoration: none;
        }
        
        .btn-edit {
            background-color: #6aa84f; /* Hijau terang */
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-edit:hover{
            background-color: #38761d;
        }
        
        .btn-delete {
            background-color: #cc0000; /* Merah untuk tombol delete */
         color: white;
         transition: background-color 0.3s ease;
        }
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>DocTo Admin</h1>
            <nav>
                <ul>
                    <li><a href="../rekammedis/admin_dashboard.php">Dashboard</a></li>
                    <li><a href="katalog.php" class="active">Katalog Obat</a></li>
                    <li><a href="admin_user.php">Pengguna</a></li>
                    <li><a href="../articles/admin_artikel.php">Artikel</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h2><?php echo $edit_item ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h2>
            
            <?php if($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="admin_katalog.php">
                    <?php if($edit_item): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_item['ID_katalog']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nama_barang">Nama Obat</label>
                        <input type="text" id="nama_barang" name="nama_barang" value="<?php echo $edit_item ? htmlspecialchars($edit_item['Nama_barang']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_barang">Harga (Rp)</label>
                        <input type="number" id="harga_barang" name="harga_barang" step="0.01" value="<?php echo $edit_item ? $edit_item['Harga_barang'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jenis_barang">Jenis Obat</label>
                        <input type="text" id="jenis_barang" name="jenis_barang" value="<?php echo $edit_item ? htmlspecialchars($edit_item['Jenis_barang']) : ''; ?>" required>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_item ? 'update' : 'add'; ?>" class="btn-submit">
                        <?php echo $edit_item ? 'Update Produk' : 'Tambah Produk'; ?>
                    </button>

                    <a href="tambah_katalog.php" class="btn-add" style="font-size: 14px; color:rgb(255, 255, 255); text-decoration: none;">Tambah Katalog</a>
                    

                    <?php if($edit_item): ?>
                        <a href="admin_katalog.php" style="margin-left: 10px; text-decoration: none; color: #333;">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <h2>Daftar Produk</h2>
            
            <table class="catalog-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Obat</th>
                        <th>Harga</th>
                        <th>Jenis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($catalogs)): ?>
                        <tr>
                            <td colspan="5">Tidak ada produk yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($catalogs as $item): ?>
                            <tr>
                                <td><?php echo $item['ID_katalog']; ?></td>
                                <td><?php echo $item['Nama_barang']; ?></td>
                                <td><?php echo 'Rp ' . number_format($item['Harga_barang'], 0, ',', '.'); ?></td>
                                <td><?php echo ($item['Jenis_barang']); ?></td>
                                <td>
                                    <a href="admin_katalog.php?edit=<?php echo $item['ID_katalog']; ?>" class="btn-edit">Edit</a>
                                    <form method="POST" action="admin_katalog.php" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['ID_katalog']; ?>">
                                        <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> DocTo Admin Panel</p>
        </div>
    </footer>
</body>
</html>
