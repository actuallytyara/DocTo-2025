<?php 
session_start();
 
require_once 'koneksi.php';
 
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
 
try {
    $stmt = $pdo->prepare("SELECT * FROM tb_login WHERE pengguna = 'user' ORDER BY ID_user ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error getting users: " . $e->getMessage());
    $users = [];
}
 
$totalUsers = count($users);
$activeUsers = 0;
$recentUsers = 0;
$currentDate = date('Y-m-d');
$weekAgo = date('Y-m-d', strtotime('-7 days'));

foreach($users as $user) { 
    if (!empty($user['gambar'])) {
        $activeUsers++;
    } 
    $recentUsers++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="dashboard-container"> 
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
                        <i class="fas fa-users" style="margin-right: 0.5rem; color: #37705D;"></i>
                        Dashboard User Management
                    </h1>
                    <p>Kelola data pengguna sistem Web Dokter Tyara</p>
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

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-change positive">
                            +<?php echo $totalUsers; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-change positive">
                            +<?php echo $activeUsers; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($activeUsers); ?></div>
                    <div class="stat-label">User dengan Profil</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-change positive">
                            +<?php echo $recentUsers; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($recentUsers); ?></div>
                    <div class="stat-label">Pengguna Terdaftar</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-change positive">
                            +100%
                        </div>
                    </div>
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Data Tersinkron</div>
                </div>
            </div>

            <!-- Add User Button -->
            <div class="add-user-container">
                <a href="add_user.php" class="add-user-button">
                    <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i>
                    Tambah User Baru
                </a>
            </div>

            <!-- User Table -->
            <div class="table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="color: #37705D; margin: 0;">
                        <i class="fas fa-table" style="margin-right: 0.5rem;"></i>
                        Data Pengguna Sistem
                    </h3>
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn btn-outline" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                        <button class="btn btn-outline" onclick="exportData()">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                    </div>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 120px;">Username</th>
                            <th style="width: 140px;">Email</th>
                            <th style="width: 80px;">Password</th>
                            <th style="width: 70px;">Role</th>
                            <th style="width: 120px;">Alamat</th>
                            <th style="width: 100px;">No. Telepon</th>
                            <th style="width: 100px;">Foto</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php $nomor = 1; ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td style="text-align: center; font-weight: 600; font-size: 12px;"><?php echo $nomor++; ?></td>
                                    <td>
                                        <strong style="font-size: 13px;"><?php echo htmlspecialchars($user['username']); ?></strong>
                                    </td>
                                    <td style="font-size: 12px;"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                    <td>
                                        <span class="password-mask" style="font-size: 12px;">
                                            <?php echo str_repeat('●', min(strlen($user['password']), 6)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="role-user" style="font-size: 10px; padding: 2px 6px;">
                                            <i class="fas fa-user" style="margin-right: 2px; font-size: 9px;"></i>
                                            <?php echo htmlspecialchars($user['pengguna']); ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($user['alamat'] ?? '-'); ?></td>
                                    <td style="font-size: 12px;"><?php echo htmlspecialchars($user['nomor_telepon'] ?? '-'); ?></td>
                                    <td style="text-align: center;">
                                        <?php 
                                        $imagePath = findImagePath($user['gambar']);
                                        if ($imagePath): ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                                 alt="User Image" 
                                                 class="user-image"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div class="no-image" style="display: none;">
                                                Gagal memuat
                                            </div>
                                            <div class="image-info" style="font-size: 9px;">
                                                <?php echo htmlspecialchars(substr(basename($imagePath), 0, 10)); ?>...
                                            </div>
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-user-circle" style="font-size: 20px; color: #ccc;"></i><br>
                                                <span style="font-size: 9px;"><?php echo empty($user['gambar']) ? 'No foto' : 'Not found'; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?php echo $user['ID_user']; ?>">
                                                <button type="button" class="editButton">
                                                    <i class="fas fa-edit"></i>
                                                    Edit
                                                </button>
                                            </a>
                                            <a href="delete.php?id=<?php echo $user['ID_user']; ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data pengguna <?php echo htmlspecialchars($user['username']); ?>?')">
                                                <button type="button" class="deleteButton">
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: #666;">
                                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #ccc;"></i>
                                    <strong>Tidak ada data pengguna</strong><br>
                                    <small>Belum ada pengguna yang terdaftar dalam sistem</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="content-grid" style="margin-top: 2rem;">
                <div class="content-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-bolt" style="margin-right: 0.5rem;"></i>
                            Aksi Cepat User Management
                        </h3>
                    </div>
                    <div class="card-content">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <button class="btn" onclick="window.location.href='add_user.php'">
                                <i class="fas fa-user-plus"></i>
                                Tambah User Baru
                            </button>
                            <button class="btn" onclick="exportData()">
                                <i class="fas fa-file-export"></i>
                                Export Data User
                            </button>
                            <button class="btn" onclick="window.location.href='user_reports.php'">
                                <i class="fas fa-chart-bar"></i>
                                Laporan User
                            </button>
                            <button class="btn btn-outline" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
 
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
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
 
        function exportData() {
            alert('Fitur export data akan segera tersedia!'); 
        }
 
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            document.title = `Dashboard User - ${timeString}`;
        }

        setInterval(updateTime, 1000);
        updateTime();
 
        function confirmDelete(username, id) {
            if (confirm(`Apakah Anda yakin ingin menghapus data pengguna "${username}"?\n\nTindakan ini tidak dapat dibatalkan!`)) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
 
        console.log('User Dashboard loaded successfully');
         
        setInterval(function() {
            console.log('Auto refresh check...'); 
        }, 300000);
    </script>
</body>
</html>