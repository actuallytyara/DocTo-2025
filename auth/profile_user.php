<?php
session_start();

include('../database/koneksi.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!$conn) {
    die("Error: Koneksi database gagal. Pastikan file koneksi.php sudah benar.");
}

$username = $_SESSION['username'];

$stmt = mysqli_prepare($conn, "SELECT * FROM tb_login WHERE username = ?");
if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    die("Error: Data user tidak ditemukan.");
}

$user_id = $user_data['ID_user'];

$janji_result = false;
if (mysqli_query($conn, "SHOW TABLES LIKE 'janji_temu'")) {
    $stmt_janji = mysqli_prepare($conn, "
        SELECT jt.*, d.username 
        FROM janji_temu jt 
        LEFT JOIN dokter d ON jt.ID_dokter = d.ID_dokter 
        WHERE jt.ID_user = ? 
        ORDER BY jt.created_at DESC 
        LIMIT 5
    ");
    if ($stmt_janji) {
        mysqli_stmt_bind_param($stmt_janji, "i", $user_id);
        mysqli_stmt_execute($stmt_janji);
        $janji_result = mysqli_stmt_get_result($stmt_janji);
    }
}

$pemesanan_result = false;
if (mysqli_query($conn, "SHOW TABLES LIKE 'pemesanan'")) {
    $stmt_pemesanan = mysqli_prepare($conn, "
        SELECT * FROM pemesanan 
        WHERE ID_user = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    if ($stmt_pemesanan) {
        mysqli_stmt_bind_param($stmt_pemesanan, "i", $user_id);
        mysqli_stmt_execute($stmt_pemesanan);
        $pemesanan_result = mysqli_stmt_get_result($stmt_pemesanan);
    }
}

$upload_message = '';
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_image'])) {
    $target_dir = "../admin/uploads/";
    
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            $upload_message = "Error: Tidak bisa membuat folder upload.";
            $upload_success = false;
        }
    }
    
    if (!isset($_FILES["profile_image"]) || $_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi'
        ];
        
        $error_code = $_FILES["profile_image"]["error"] ?? UPLOAD_ERR_NO_FILE;
        $upload_message = $error_messages[$error_code] ?? 'Error tidak diketahui';
        $upload_success = false;
    } else {
        $uploaded_file = $_FILES["profile_image"];
        $file_extension = strtolower(pathinfo($uploaded_file["name"], PATHINFO_EXTENSION));
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $upload_ok = 1;
        $error_message = "";
        
        $check = getimagesize($uploaded_file["tmp_name"]);
        if($check === false) {
            $error_message = "File bukan gambar yang valid.";
            $upload_ok = 0;
        }
        
        if ($uploaded_file["size"] > 5000000) {
            $error_message = "File terlalu besar. Maksimal 5MB.";
            $upload_ok = 0;
        }
        
        $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
        if(!in_array($file_extension, $allowed_extensions)) {
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            $upload_ok = 0;
        }
        
        if (!is_writable($target_dir)) {
            $error_message = "Folder upload tidak dapat ditulis.";
            $upload_ok = 0;
        }
        
        if ($upload_ok == 0) {
            $upload_message = $error_message;
            $upload_success = false;
        } else {
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file)) {
                if (!empty($user_data['gambar'])) {
                    $old_image_path = getFullImagePath($user_data['gambar']);
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                $relative_path = "uploads/" . $new_filename;
                $update_stmt = mysqli_prepare($conn, "UPDATE tb_login SET gambar = ? WHERE ID_user = ?");
                
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "si", $relative_path, $user_id);
                    
                    if (mysqli_stmt_execute($update_stmt)) {
                        $upload_message = "Gambar profil berhasil diupdate!";
                        $upload_success = true;
                        $user_data['gambar'] = $relative_path;
                    } else {
                        $upload_message = "Error updating database: " . mysqli_error($conn);
                        $upload_success = false;
                    }
                    
                    mysqli_stmt_close($update_stmt);
                } else {
                    $upload_message = "Error preparing database statement.";
                    $upload_success = false;
                }
            } else {
                $upload_message = "Error: Gagal memindahkan file ke folder tujuan.";
                $upload_success = false;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_image'])) {
    if (!empty($user_data['gambar'])) {
        $full_path = getFullImagePath($user_data['gambar']);
        
        if (file_exists($full_path)) {
            if (unlink($full_path)) {
                $update_stmt = mysqli_prepare($conn, "UPDATE tb_login SET gambar = NULL WHERE ID_user = ?");
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "i", $user_id);
                    if (mysqli_stmt_execute($update_stmt)) {
                        $upload_message = "Foto profil berhasil dihapus!";
                        $upload_success = true;
                        $user_data['gambar'] = null;
                    } else {
                        $upload_message = "Error updating database: " . mysqli_error($conn);
                        $upload_success = false;
                    }
                    mysqli_stmt_close($update_stmt);
                }
            } else {
                $upload_message = "Error menghapus file gambar.";
                $upload_success = false;
            }
        } else {
            $update_stmt = mysqli_prepare($conn, "UPDATE tb_login SET gambar = NULL WHERE ID_user = ?");
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "i", $user_id);
                if (mysqli_stmt_execute($update_stmt)) {
                    $upload_message = "Foto profil berhasil dihapus!";
                    $upload_success = true;
                    $user_data['gambar'] = null;
                } else {
                    $upload_message = "Error updating database: " . mysqli_error($conn);
                    $upload_success = false;
                }
                mysqli_stmt_close($update_stmt);
            }
        }
    } else {
        $upload_message = "Tidak ada gambar untuk dihapus.";
        $upload_success = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    
    $update_stmt = mysqli_prepare($conn, "UPDATE tb_login SET email = ?, nomor_telepon = ?, alamat = ? WHERE ID_user = ?");
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "sssi", $email, $telepon, $alamat, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $upload_message = "Profile berhasil diupdate!";
            $upload_success = true;
            $user_data['email'] = $email;
            $user_data['nomor_telepon'] = $telepon;
            $user_data['alamat'] = $alamat;
        } else {
            $upload_message = "Error updating profile: " . mysqli_error($conn);
            $upload_success = false;
        }
        mysqli_stmt_close($update_stmt);
    }
}

function getFullImagePath($imagePath) {
    if (empty($imagePath)) {
        return null;
    }
    
    if (strpos($imagePath, 'uploads/') === 0) {
        return "../admin/" . $imagePath;
    }
    
    if (strpos($imagePath, '/') === false) {
        return "../admin/uploads/" . $imagePath;
    }
    
    return $imagePath;
}

function getImageDisplayPath($imagePath) {
    if (empty($imagePath)) {
        return null;
    }
    
    $full_path = getFullImagePath($imagePath);
    
    if ($full_path && file_exists($full_path)) {
        if (strpos($imagePath, 'uploads/') === 0) {
            return "../admin/" . $imagePath;
        } else {
            return "../admin/uploads/" . basename($imagePath);
        }
    }
    
    return null;
}

function hasProfileImage($user_data) {
    if (empty($user_data['gambar'])) {
        return false;
    }
    
    $display_path = getImageDisplayPath($user_data['gambar']);
    return $display_path && file_exists($display_path);
}

if ($stmt) mysqli_stmt_close($stmt);
if (isset($stmt_janji) && $stmt_janji) mysqli_stmt_close($stmt_janji);
if (isset($stmt_pemesanan) && $stmt_pemesanan) mysqli_stmt_close($stmt_pemesanan);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile User - DocTo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="profile.css">
    <style>
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .profile-avatar i {
            font-size: 50px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .current-image {
            text-align: center;
            margin: 20px 0;
        }
        
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #ddd;
            object-fit: cover;
        }
        
        .image-preview {
            text-align: center;
            margin: 15px 0;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #ddd;
            object-fit: cover;
        }
        
        .preview-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 12px;
            color: #856404;
        }
    </style>
</head>
<body>
    <?php if (!empty($upload_message)): ?>
        <div id="notification" class="notification <?php echo $upload_success ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($upload_message); ?>
            <span class="close-notification" onclick="closeNotification()">&times;</span>
        </div>
    <?php endif; ?>

    <div class="header">
        <div class="logo">
            <img src="../assets/images/logo_docto.png" alt="Logo DocTo" style="height: 55px; margin-right: 10px; margin-top: -5px;">
            DocTo
        </div>
        <div class="header-icons">
            <i class="fas fa-user"></i>
            <i class="fas fa-envelope"></i>
            <i class="fas fa-bell"></i>
        </div>
    </div>

    <a href="berhasil_login.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
    </a>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php 
                    if (hasProfileImage($user_data)) {
                        $display_path = getImageDisplayPath($user_data['gambar']);
                        echo '<img src="' . htmlspecialchars($display_path) . '?v=' . time() . '" alt="Profile Picture" onclick="openImageModal()" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                        echo '<i class="fas fa-user" style="display: none;"></i>';
                    } else {
                        echo '<i class="fas fa-user"></i>';
                    }
                    ?>
                </div>
                
                <?php if (!empty($user_data['gambar'])): ?>
                    <div class="debug-info" style="display: none;">
                        <strong>Debug Info:</strong><br>
                        DB Path: <?php echo htmlspecialchars($user_data['gambar']); ?><br>
                        Display Path: <?php echo htmlspecialchars(getImageDisplayPath($user_data['gambar']) ?? 'NULL'); ?><br>
                        File Exists: <?php echo file_exists(getImageDisplayPath($user_data['gambar']) ?? '') ? 'YES' : 'NO'; ?><br>
                        Full Path: <?php echo htmlspecialchars(getFullImagePath($user_data['gambar']) ?? 'NULL'); ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-name"><?php echo htmlspecialchars($user_data['username']); ?></div>
                <div class="profile-role"><?php echo htmlspecialchars($user_data['pengguna'] ?? 'User'); ?></div>
            </div>
            
            <div class="profile-content">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Informasi Personal
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['email'] ?? 'Belum diisi'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Telepon</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['nomor_telepon'] ?? 'Belum diisi'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Alamat</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['alamat'] ?? 'Belum diisi'); ?></div>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="openEditModal()">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </button>
                    <button class="btn btn-secondary" onclick="openImageModal()">
                        <i class="fas fa-camera"></i>
                        Kelola Foto
                    </button>
                    <a href="change_password.php" class="btn btn-secondary">
                        <i class="fas fa-key"></i>
                        Ubah Password
                    </a>
                </div>
            </div>
        </div>

        <div class="riwayat-section">
            <div class="section-title">
                <i class="fas fa-history"></i>
                Riwayat Aktivitas
            </div>
            
            <div class="riwayat-tabs">
                <button class="tab-btn active" onclick="showTab('janji')">Janji Temu</button>
                <button class="tab-btn" onclick="showTab('pemesanan')">Pemesanan</button>
            </div>
            
            <div id="janji-tab" class="tab-content active">
                <?php if ($janji_result && mysqli_num_rows($janji_result) > 0): ?>
                    <?php while ($janji = mysqli_fetch_assoc($janji_result)): ?>
                        <div class="riwayat-item">
                            <div class="riwayat-header">
                                <div class="riwayat-title">
                                    Dr. <?php echo htmlspecialchars($janji['username'] ?? 'Tidak diketahui'); ?>
                                </div>
                                <span class="status-badge status-<?php echo $janji['Status']; ?>">
                                    <?php echo ucfirst($janji['Status']); ?>
                                </span>
                            </div>
                            <div class="riwayat-details">
                                <div><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($janji['Tanggal'])); ?> - <?php echo date('H:i', strtotime($janji['Waktu'])); ?></div>
                                <div><i class="fas fa-notes-medical"></i> <?php echo htmlspecialchars($janji['keluhan']); ?></div>
                                <?php if ($janji['biaya'] > 0): ?>
                                    <div><i class="fas fa-money-bill"></i> Rp <?php echo number_format($janji['biaya'], 0, ',', '.'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="riwayat-item">
                        <div class="riwayat-title">Belum ada riwayat janji temu</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div id="pemesanan-tab" class="tab-content">
                <?php if ($pemesanan_result && mysqli_num_rows($pemesanan_result) > 0): ?>
                    <?php while ($pemesanan = mysqli_fetch_assoc($pemesanan_result)): ?>
                        <div class="riwayat-item">
                            <div class="riwayat-header">
                                <div class="riwayat-title">
                                    <?php echo htmlspecialchars($pemesanan['nomor_pemesanan']); ?>
                                </div>
                                <span class="status-badge status-<?php echo $pemesanan['status_pemesanan']; ?>">
                                    <?php echo ucfirst($pemesanan['status_pemesanan']); ?>
                                </span>
                            </div>
                            <div class="riwayat-details">
                                <div><i class="fas fa-calendar"></i> <?php echo date('d M Y H:i', strtotime($pemesanan['tanggal_pemesanan'])); ?></div>
                                <div><i class="fas fa-money-bill"></i> Rp <?php echo number_format($pemesanan['total_harga'], 0, ',', '.'); ?></div>
                                <div><i class="fas fa-credit-card"></i> <?php echo ucfirst(str_replace('_', ' ', $pemesanan['metode_pembayaran'])); ?></div>
                                <?php if ($pemesanan['catatan']): ?>
                                    <div><i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($pemesanan['catatan']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="riwayat-item">
                        <div class="riwayat-title">Belum ada riwayat pemesanan</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Profile</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telepon">Nomor Telepon</label>
                    <input type="tel" id="telepon" name="telepon" value="<?php echo htmlspecialchars($user_data['nomor_telepon'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <h2>Kelola Foto Profil</h2>
            
            <?php if (hasProfileImage($user_data)): ?>
                <div class="current-image-section">
                    <h3>Foto Profil Saat Ini</h3>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars(getImageDisplayPath($user_data['gambar']) . '?v=' . time()); ?>" alt="Current Profile Picture">
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="upload-section">
                <h3>Upload Foto Baru</h3>
                <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="profile_image" class="file-label">
                            <i class="fas fa-upload"></i>
                            Pilih Gambar
                        </label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(this)" required>
                        <small class="form-text">Format: JPG, JPEG, PNG, GIF. Maksimal: 5MB</small>
                    </div>
                    
                    <div class="preview-container" id="previewContainer" style="display: none;">
                        <h4>Preview Gambar Baru:</h4>
                        <div class="image-preview">
                            <img id="imagePreview" alt="Preview">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="upload_image" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Upload Gambar
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <?php if (hasProfileImage($user_data)): ?>
                <div class="delete-section">
                    <h3>Hapus Foto Profil</h3>
                    <p>Anda dapat menghapus foto profil saat ini jika diperlukan.</p>
                    <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                        <button type="submit" name="delete_image" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            Hapus Foto Profil
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const style = document.createElement('style');
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                z-index: 10000;
                box-shadow: 0 4px
                max-width: 400px;
            }
            .notification.success {
                background-color: #28a745;
            }
            .notification.error {
                background-color: #dc3545;
            }
            .close-notification {
                float: right;
                font-size: 20px;
                font-weight: bold;
                line-height: 20px;
                cursor: pointer;
                margin-left: 10px;
            }
        `;
        document.head.appendChild(style);

        function closeNotification() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'none';
            }
        }

        setTimeout(closeNotification, 5000);

        function openEditModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openImageModal() {
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function showTab(tabName) {
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });

            document.getElementById(tabName + '-tab').classList.add('active');
            
            event.target.classList.add('active');
        }

        function previewImage(input) {
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                previewContainer.style.display = 'none';
            }
        }

        function resetForm() {
            document.getElementById('uploadForm').reset();
            document.getElementById('previewContainer').style.display = 'none';
        }

        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const imageModal = document.getElementById('imageModal');
            
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            if (event.target == imageModal) {
                imageModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>