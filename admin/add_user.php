<?php
session_start();

require_once 'koneksi.php';

$message = '';
$messageType = '';

function findImagePath($imageName) {
    if (empty($imageName)) {
        return null;
    }
    
    $possiblePaths = [
        $imageName,
        'uploads/' . basename($imageName),
        '../uploads/' . basename($imageName),
        str_replace('../uploads/', 'uploads/', $imageName),
        str_replace('uploads/', '../uploads/', $imageName),
        __DIR__ . '/uploads/' . basename($imageName),
        __DIR__ . '/../uploads/' . basename($imageName)
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            if (strpos($path, __DIR__) === 0) {
                return str_replace(__DIR__ . '/', '', $path);
            }
            return $path;
        }
    }
    
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $pengguna = 'user'; 

    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_login WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errors[] = "Username atau email sudah terdaftar";
            }
        } catch(PDOException $e) {
            $errors[] = "Error checking existing user: " . $e->getMessage();
        }
    }
    
    $gambar_path = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_info = pathinfo($_FILES['gambar']['name']);
        $extension = strtolower($file_info['extension']);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowed_extensions)) {
            $errors[] = "Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF";
        } elseif ($_FILES['gambar']['size'] > 5 * 1024 * 1024) { 
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB";
        } else {
            $new_filename = 'user_' . uniqid() . '.' . $extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                $gambar_path = $upload_path;
            } else {
                $errors[] = "Gagal mengupload file";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO tb_login (username, email, password, alamat, nomor_telepon, pengguna, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$username, $email, $hashed_password, $alamat, $nomor_telepon, $pengguna, $gambar_path]);
            
            if ($result) {
                $message = "User berhasil ditambahkan!";
                $messageType = "success";
                
                $username = $email = $alamat = $nomor_telepon = '';
            } else {
                $message = "Gagal menambahkan user";
                $messageType = "error";
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}

$userInfo = [];
if (isset($_SESSION['ID_user'])) {
    try {
        $stmt = $pdo->prepare("SELECT username, email, gambar FROM tb_login WHERE ID_user = ?");
        $stmt->execute([$_SESSION['ID_user']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting user info: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Baru - Web Dokter Tyara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user.css">
    <style>
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #37705D;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #37705D;
            box-shadow: 0 0 0 2px rgba(55, 112, 93, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }
        
        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-upload-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            border: 2px dashed #ddd;
            border-radius: 5px;
            background: #f9f9f9;
            transition: all 0.3s;
        }
        
        .file-upload:hover .file-upload-btn {
            border-color: #37705D;
            background: #f0f7f4;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            margin-top: 1rem;
            display: none;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-primary {
            background: #37705D;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #2a5547;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>
                    <span class="icon">
                        <i class="fas fa-hospital"></i>
                    </span>
                    Admin Dashboard
                </h2>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-home"></i>
                        </span>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="user_dashboard.php" class="nav-link active">
                        <span class="nav-icon">
                            <i class="fas fa-users"></i>
                        </span>
                        Dashboard User
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_management.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-user-shield"></i>
                        </span>
                        Dashboard Admin
                    </a>
                </div>
                <div class="nav-item">
                    <a href="dokter_dashboard.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-user-md"></i>
                        </span>
                        Dashboard Dokter
                    </a>
                </div>
                <div class="nav-item">
                    <a href="rekammedis/rekammedis.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-file-medical"></i>
                        </span>
                        Dashboard Rekam Medis
                    </a>
                </div>
                <div class="nav-item">
                    <a href="pemesanan_dashboard.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </span>
                        Dashboard Pemesanan
                    </a>
                </div>
                <div class="nav-item">
                    <a href="obat_dashboard.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-pills"></i>
                        </span>
                        Dashboard Obat
                    </a>
                </div>
                <div class="nav-item">
                    <a href="resep_dashboard.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </span>
                        Dashboard Resep Obat
                    </a>
                </div>
                <div class="nav-item">
                    <a href="laporan.php" class="nav-link">
                        <span class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                        Laporan
                    </a>
                </div>
                <div class="nav-item">
                    <a href="logout.php" class="nav-link" style="color: #e74c3c;">
                        <span class="nav-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                        Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="welcome-section">
                    <h1>
                        <i class="fas fa-user-plus" style="margin-right: 0.5rem; color: #37705D;"></i>
                        Tambah User Baru
                    </h1>
                    <p>Formulir untuk menambah pengguna baru ke sistem Web Dokter Tyara</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php if (!empty($userInfo['gambar']) && file_exists($userInfo['gambar'])): ?>
                            <img src="<?php echo htmlspecialchars($userInfo['gambar']); ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($userInfo['username'] ?? 'Admin'); ?></h3>
                        <p>Super Administrator</p>
                    </div>
                </div>
            </header>

            <!-- Form Container -->
            <div class="form-container">
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $messageType; ?>">
                        <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="addUserForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user"></i>
                                Username *
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                   placeholder="Masukkan username" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                   placeholder="Masukkan alamat email" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                Password *
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 6 karakter" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i>
                                Konfirmasi Password *
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Ulangi password" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">
                            <i class="fas fa-map-marker-alt"></i>
                            Alamat
                        </label>
                        <textarea id="alamat" 
                                  name="alamat" 
                                  rows="3" 
                                  placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($alamat ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="nomor_telepon">
                            <i class="fas fa-phone"></i>
                            Nomor Telepon
                        </label>
                        <input type="tel" 
                               id="nomor_telepon" 
                               name="nomor_telepon" 
                               value="<?php echo htmlspecialchars($nomor_telepon ?? ''); ?>"
                               placeholder="Contoh: 08123456789">
                    </div>
                    
                    <div class="form-group">
                        <label for="gambar">
                            <i class="fas fa-image"></i>
                            Foto Profile
                        </label>
                        <div class="file-upload">
                            <input type="file" 
                                   id="gambar" 
                                   name="gambar" 
                                   accept="image/*" 
                                   onchange="previewImage(this)">
                            <div class="file-upload-btn">
                                <i class="fas fa-cloud-upload-alt" style="margin-right: 0.5rem;"></i>
                                Pilih File Gambar
                            </div>
                        </div>
                        <img id="preview" class="preview-image" alt="Preview">
                        <small style="color: #666; display: block; margin-top: 0.5rem;">
                            Format: JPG, JPEG, PNG, GIF (Maksimal 5MB)
                        </small>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan User
                        </button>
                        <a href="user_dashboard.php" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        function previewImage(input) {
            const preview = document.getElementById('preview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });

        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#ddd';
            }
        });
    </script>
</body>
</html>