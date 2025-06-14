<?php
include 'koneksi.php';
include 'functions.php';


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if (deleteArticle($conn, $id)) {
        header("Location: admin_artikel.php?status=deleted");
        exit();
    }
}

$articles = getAllArticles($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - DocTo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
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
        .table-responsive {
            margin-top: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-published {
            background-color: #28a745;
            color: white;
        }
        .status-draft {
            background-color: #6c757d;
            color: white;
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
                        <h1 class="docto-logo">DocTo Admin</h1>
                    </a>
                </div>
                <div class="col-md-9">
                    <nav class="text-right">
                        <a class="nav-link d-inline-block" href="index.php">Beranda</a>
                        <a class="nav-link d-inline-block" href="artikel.php">Artikel</a>
                        <a class="nav-link d-inline-block" href="katalog_obat.php">Katalog Obat</a>
                        <a class="nav-link d-inline-block" href="tentang.php">Tentang</a>
                        <a class="nav-link d-inline-block active" href="admin_artikel.php">Kelola Artikel</a>
                        <a class="nav-link d-inline-block" href="logout.php">Logout</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Artikel Kesehatan</h2>
            <a href="tambah_artikel.php" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Tambah Artikel
            </a>
        </div>
        
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'added'): ?>
                <div class="alert alert-success">Artikel berhasil ditambahkan</div>
            <?php elseif ($_GET['status'] == 'updated'): ?>
                <div class="alert alert-success">Artikel berhasil diperbarui</div>
            <?php elseif ($_GET['status'] == 'deleted'): ?>
                <div class="alert alert-success">Artikel berhasil dihapus</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($articles) > 0): ?>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><?= $article['ID_artikel'] ?></td>
                                <td><?= $article['judul'] ?></td>
                                <td><?= date('d/m/Y', strtotime($article['tanggal_posting'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $article['status'] == 'published' ? 'status-published' : 'status-draft' ?>">
                                        <?= $article['status'] == 'published' ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="lihat_artikel.php?slug=<?= $article['slug'] ?>" class="btn btn-sm btn-info" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_artikel.php?id=<?= $article['ID_artikel'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $article['ID_artikel'] ?>)" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada artikel</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
    
    <script>
        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus artikel ini?")) {
                window.location.href = "admin_artikel.php?action=delete&id=" + id;
            }
        }
    </script>
</body>
</html>