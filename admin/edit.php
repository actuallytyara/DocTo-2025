<?php
session_start();

require_once 'koneksi.php';

$message = '';
$messageType = '';
$user_data = [];

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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: user_dashboard.php');
    exit;
}

$user_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM tb_login WHERE ID_user = ? AND pengguna = 'user'");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        $_SESSION['message'] = "User tidak ditemukan atau bukan user biasa";
        $_SESSION['message_type'] = "error";
        header('Location: user_dashboard.php');
        exit;
    }
} catch(PDOException $e) {
    $message = "Error mengambil data user: " . $e->getMessage();
    $messageType = "error";
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username maksimal 50 karakter";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    } elseif (strlen($email) > 100) {
        $errors[] = "Email maksimal 100 karakter";
    }
    
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter";
        } elseif (strlen($password) > 255) {
            $errors[] = "Password maksimal 255 karakter";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak cocok";
        }
    }
    
    if (!empty($nomor_telepon)) {
        if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $nomor_telepon)) {
            $errors[] = "Format nomor telepon tidak valid";
        }
    }
    
    if (!empty($alamat) && strlen($alamat) > 255) {
        $errors[] = "Alamat maksimal 255 karakter";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT ID_user, username, email FROM tb_login WHERE (username = ? OR email = ?) AND ID_user != ?");
            $stmt->execute([$username, $email, $user_id]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_user) {
                if ($existing_user['username'] === $username) {
                    $errors[] = "Username sudah digunakan user lain";
                }
                if ($existing_user['email'] === $email) {
                    $errors[] = "Email sudah digunakan user lain";
                }
            }
        } catch(PDOException $e) {
            $errors[] = "Error checking existing user: " . $e->getMessage();
        }
    }
    
$gambar_path = $user_data['gambar']; 
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
    
    $upload_dir = __DIR__ . '/uploads/'; 
    $web_path = 'uploads/'; 
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_info = pathinfo($_FILES['gambar']['name']);
    $extension = strtolower($file_info['extension']);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowed_extensions)) {
        $errors[] = "Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, GIF, atau WEBP";
    } elseif ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
        $errors[] = "Ukuran file terlalu besar. Maksimal 5MB";
    } else {
        $new_filename = 'user_' . $user_id . '_' . uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $new_filename;
        $web_gambar_path = $web_path . $new_filename;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
            if (!empty($user_data['gambar']) && $user_data['gambar'] !== $web_gambar_path) {
                $old_file_path = __DIR__ . '/' . $user_data['gambar'];
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $gambar_path = $web_gambar_path; 
        } else {
            $errors[] = "Gagal mengupload file";
        }
    }
}

function getImageUrl($imagePath) {
    if (empty($imagePath)) {
        return null;
    }
    
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    if (strpos($imagePath, 'uploads/') === 0) {
        return $imagePath;
    }
    
    if (strpos($imagePath, '../') === 0) {
        return str_replace('../', '', $imagePath);
    }

    return 'uploads/' . basename($imagePath);
}

    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE tb_login SET username = ?, email = ?, password = ?, alamat = ?, nomor_telepon = ?, gambar = ? WHERE ID_user = ?");
                $result = $stmt->execute([$username, $email, $hashed_password, $alamat, $nomor_telepon, $gambar_path, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE tb_login SET username = ?, email = ?, alamat = ?, nomor_telepon = ?, gambar = ? WHERE ID_user = ?");
                $result = $stmt->execute([$username, $email, $alamat, $nomor_telepon, $gambar_path, $user_id]);
            }
            
            if ($result) {
                $pdo->commit();
                $_SESSION['message'] = "Data user berhasil diperbarui!";
                $_SESSION['message_type'] = "success";
                
                $stmt = $pdo->prepare("SELECT * FROM tb_login WHERE ID_user = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                header('Location: user_dashboard.php');
                exit;
            } else {
                $pdo->rollback();
                $message = "Gagal memperbarui data user";
                $messageType = "error";
            }
        } catch(PDOException $e) {
            $pdo->rollback();
            $message = "Error updating user: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?php echo htmlspecialchars($user_data['username']); ?> - Web Dokter Tyara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="css/edit_user.css">
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
                        <i class="fas fa-user-edit" style="margin-right: 0.5rem; color: #37705D;"></i>
                        Edit User
                    </h1>
                    <p>Ubah informasi pengguna sistem Web Dokter Tyara</p>
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

            <!-- Edit Form -->
            <div class="edit-container">
                <div class="edit-header">
                    <h2>
                        <i class="fas fa-user-edit"></i>
                        Edit User: <?php echo htmlspecialchars($user_data['username']); ?>
                    </h2>
                    <p>ID User: #<?php echo htmlspecialchars($user_data['ID_user']); ?></p>
                </div>

                <div class="edit-form">
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="editUserForm">
                        <div class="form-grid">
                            <!-- Basic Information -->
                            <div class="form-row">
                                <div class="form-group required">
                                    <label for="username">
                                        <i class="fas fa-user"></i>
                                        Username
                                    </label>
                                    <input type="text" 
                                           id="username" 
                                           name="username" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($user_data['username']); ?>" 
                                           required 
                                           minlength="3" 
                                           maxlength="50"
                                           autocomplete="username">
                                    <small class="form-text">Minimal 3 karakter, maksimal 50 karakter</small>
                                </div>

                                <div class="form-group required">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i>
                                        Email
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                                           required 
                                           maxlength="100"
                                           autocomplete="email">
                                    <small class="form-text">Format email yang valid</small>
                                </div>
                            </div>

                            <!-- Password Section -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">
                                        <i class="fas fa-lock"></i>
                                        Password Baru
                                    </label>
                                    <div class="password-toggle">
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               class="form-control" 
                                               minlength="6" 
                                               maxlength="255"
                                               autocomplete="new-password">
                                        <button type="button" class="toggle-btn" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                    <small class="form-text">Kosongkan jika tidak ingin mengubah password</small>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">
                                        <i class="fas fa-lock"></i>
                                        Konfirmasi Password
                                    </label>
                                    <div class="password-toggle">
                                        <input type="password" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               class="form-control" 
                                               minlength="6" 
                                               maxlength="255"
                                               autocomplete="new-password">
                                        <button type="button" class="toggle-btn" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye" id="confirm_password-icon"></i>
                                        </button>
                                    </div>
                                    <small class="form-text">Harus sama dengan password baru</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nomor_telepon">
                                        <i class="fas fa-phone"></i>
                                        Nomor Telepon
                                    </label>
                                    <input type="tel" 
                                           id="nomor_telepon" 
                                           name="nomor_telepon" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($user_data['nomor_telepon']); ?>" 
                                           pattern="[0-9+\-\s()]{8,20}"
                                           autocomplete="tel">
                                    <small class="form-text">Format: 08xxxxxxxxxx atau +62xxxxxxxxx</small>
                                </div>

                                <div class="form-group">
                                    <label for="alamat">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Alamat
                                    </label>
                                    <input type="text" 
                                           id="alamat" 
                                           name="alamat" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($user_data['alamat']); ?>" 
                                           maxlength="255"
                                           autocomplete="address-line1">
                                    <small class="form-text">Alamat lengkap pengguna</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="gambar">
                                    <i class="fas fa-image"></i>
                                    Foto Profil
                                </label>
                                
                                <?php 
                                $currentImagePath = findImagePath($user_data['gambar']);
                                if ($currentImagePath): ?>
                                    <div style="margin-bottom: 1rem;">
                                        <p><strong>Foto saat ini:</strong></p>
                                        <img src="<?php echo htmlspecialchars($currentImagePath); ?>" 
                                             alt="Current Profile" 
                                             class="current-image"
                                             id="currentImage">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="file-input-wrapper">
                                    <input type="file" 
                                           id="gambar" 
                                           name="gambar" 
                                           accept="image/*"
                                           onchange="previewImage(this)">
                                    <label for="gambar" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        Pilih Foto Baru
                                    </label>
                                </div>
                                
                                <div class="file-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i>
                                        Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 5MB
                                    </small>
                                </div>
                                
                                <img id="imagePreview" class="image-preview" alt="Preview">
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="user_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </a>
                            
                            <div style="display: flex; gap: 1rem;">
                                <button type="reset" class="btn btn-outline" onclick="resetForm()">
                                    <i class="fas fa-undo"></i>
                                    Reset
                                </button>
                                
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i><button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
                
                const label = document.querySelector('.file-input-label');
                label.innerHTML = `<i class="fas fa-check-circle"></i> ${file.name}`;
                label.style.borderColor = '#28a745';
                label.style.backgroundColor = '#d4edda';
            } else {
                preview.style.display = 'none';
                const label = document.querySelector('.file-input-label');
                label.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Pilih Foto Baru';
                label.style.borderColor = '#dee2e6';
                label.style.backgroundColor = '#f8f9fa';
            }
        }

        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset form? Semua perubahan akan hilang.')) {
                document.getElementById('editUserForm').reset();
                
                const preview = document.getElementById('imagePreview');
                preview.style.display = 'none';
                
                const label = document.querySelector('.file-input-label');
                label.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Pilih Foto Baru';
                label.style.borderColor = '#dee2e6';
                label.style.backgroundColor = '#f8f9fa';
                
                const passwordFields = ['password', 'confirm_password'];
                passwordFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    const icon = document.getElementById(fieldId + '-icon');
                    if (field) {
                        field.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }
        }

        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok dengan password baru!');
                document.getElementById('confirm_password').focus();
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password && confirmPassword) {
                if (password === confirmPassword) {
                    this.style.borderColor = '#28a745';
                } else {
                    this.style.borderColor = '#dc3545';
                }
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });

        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            if (username.length < 3) {
                this.style.borderColor = '#dc3545';
            } else if (username.length > 50) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });

        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailRegex.test(email)) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });

        document.getElementById('nomor_telepon').addEventListener('input', function() {
            const phone = this.value;
            const phoneRegex = /^[0-9+\-\s()]{8,20}$/;
            
            if (!phone || phoneRegex.test(phone)) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });

        document.getElementById('gambar').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024; 
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    this.value = '';
                    document.getElementById('imagePreview').style.display = 'none';
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak diizinkan! Gunakan JPG, JPEG, PNG, GIF, atau WEBP.');
                    this.value = '';
                    document.getElementById('imagePreview').style.display = 'none';
                    return;
                }
            }
        });

        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !mobileToggle.contains(e.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>