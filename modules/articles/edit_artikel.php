<?php

include 'koneksi.php';
include 'functions.php';



if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error_message = "ID artikel tidak valid";
    header("Location: admin_artikel.php?error=invalid_id");
    exit();
}

$id = intval($_GET['id']);


try {
    
    $stmt = $conn->prepare("SHOW COLUMNS FROM artikel");
    $stmt->execute();
    $result = $stmt->get_result();

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field']; // kolom nama
    }

    
    $id_column = 'id_artikel'; // Default asumsi
    if (in_array('id_artikel', $columns)) {
        $id_column = 'id_artikel';
    } elseif (in_array('artikel_id', $columns)) {
        $id_column = 'artikel_id';
    }

   
    $stmt = $conn->prepare("SELECT * FROM artikel WHERE $id_column = ?");
    $stmt->bind_param("i", $id); // bind_param
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();


    
    if (!$article) {
        $error_message = "Artikel tidak ditemukan";
        header("Location: admin_artikel.php?error=not_found");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error saat mengambil data artikel: " . $e->getMessage());
    $error_message = "Gagal mengambil data artikel";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $isi_artikel = trim($_POST['isi_artikel']);
    $status = $_POST['status'];
    
    $errors = [];
    
    if (empty($judul)) {
        $errors[] = "Judul artikel tidak boleh kosong";
    }
    
    if (empty($isi_artikel)) {
        $errors[] = "Isi artikel tidak boleh kosong";
    }
    
    // kalo ndak eeror, bisa disimpan di dataabse
    if (empty($errors)) {
        $data = [
            'judul' => $judul,
            'isi_artikel' => $isi_artikel,
            'status' => $status,
            'gambar' => isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 ? $_FILES['gambar'] : null
        ];
        
        try {
            if (updateArticle($conn, $id, $data)) {
                header("Location: admin_artikel.php?status=updated");
                exit();
            } else {
                $errors[] = "Gagal memperbarui artikel";
            }
        } catch (Exception $e) {
            error_log("Error saat update artikel: " . $e->getMessage());
            $errors[] = "Terjadi kesalahan saat menyimpan artikel: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - DocTo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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
        .required-field::after {
            content: " *";
            color: red;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <!-- Debug Info (Hapus setelah debugging selesai) -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] == 1): ?>
    <div class="alert alert-info">
        <h5>Debug Info:</h5>
        <p>Session ID: <?= session_id() ?></p>
        <p>Username: <?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Tidak ada' ?></p>
        <p>Is Admin: <?= isset($_SESSION['is_admin']) ? var_export($_SESSION['is_admin'], true) : 'Tidak ada' ?></p>
        <p>Session Data: <pre><?= print_r($_SESSION, true) ?></pre></p>
    </div>
    <?php endif; ?>

    
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
                        <a class="nav-link d-inline-block active" href="admin_artikel.php">Kelola Artikel</a>
                        <a class="nav-link d-inline-block" href="logout.php">Logout</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <h2 class="mb-4">Edit Artikel</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form action="edit_artikel.php?id=<?php echo htmlspecialchars($id); ?>" method="POST" enctype="multipart/form-data" class="mb-5">
            <div class="form-group">
                <label for="judul" class="required-field">Judul Artikel</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($article['judul'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="isi_artikel" class="required-field">Isi Artikel</label>
                <textarea class="form-control" id="isi_artikel" name="isi_artikel" rows="10" required><?php echo htmlspecialchars($article['isi_artikel'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Status Publikasi</label>
                <select class="form-control" id="status" name="status">
                    <option value="published" <?php echo isset($article['status']) && $article['status'] == 'published' ? 'selected' : ''; ?>>Publikasikan</option>
                    <option value="draft" <?php echo isset($article['status']) && $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar Artikel (Opsional)</label>
                <input type="file" class="form-control-file" id="gambar" name="gambar" accept="image/*">
                <small class="form-text text-muted">Unggah gambar baru akan menimpa gambar yang ada. Biarkan kosong untuk mempertahankan gambar saat ini.</small>
                
                <?php if (isset($article['gambar']) && !empty($article['gambar'])): ?>
                    <div class="mt-2">
                        <p>Gambar saat ini:</p>
                        <img src="uploads/artikel/<?php echo htmlspecialchars($article['gambar']); ?>" alt="Gambar artikel" class="preview-image">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="admin_artikel.php" class="btn btn-secondary ml-2">Batal</a>
            </div>
        </form>
        
        <div class="alert alert-info">
            <h5>Troubleshooting</h5>
            <p>Jika data tidak bisa disimpan, pastikan:</p>
            <ol>
                <li>Fungsi <code>updateArticle()</code> sudah tersedia di file <code>functions.php</code></li>
                <li>Folder <code>uploads/artikel/</code> sudah dibuat dan memiliki izin tulis</li>
                <li>Koneksi database berfungsi dengan baik</li>
                <li>Struktur tabel <code>artikel</code> sesuai dengan yang diharapkan</li>
            </ol>
            <p>Untuk debugging, Anda bisa menambahkan parameter <code>?debug=1</code> pada URL.</p>
        </div>
    </div>


    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>DocTo</h5>
                    <p class="small">Platform informasi kesehatan terpercaya</p>
                </div>
                <div class="col-md-4">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled small">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="artikel.php" class="text-white">Artikel</a></li>
                        <li><a href="katalog_obat.php" class="text-white">Katalog Obat</a></li>
                        <li><a href="tentang.php" class="text-white">Tentang</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p class="small">Email: info@docto.id<br>
                    Telepon: 021-12345678</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="small mb-0">&copy; <?php echo date('Y'); ?> DocTo. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </div>
    </footer>

   
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
           
            $('#isi_artikel').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
           
            $('#gambar').change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('.preview-image').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
</body>
</html>