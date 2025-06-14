<?php
session_start();

require_once 'koneksi.php';


$stats = getDashboardStats($pdo);

$recentActivities = getRecentActivities($pdo, 8);

$lowStockMedicines = getLowStockMedicines($pdo, 10);

$pendingOrders = getPendingOrders($pdo);

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
    <title>Admin Dashboard - Web Dokter Tyara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
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
                    <a href="dashboard.php" class="nav-link active">
                        <span class="nav-icon">
                            <i class="fas fa-home"></i>
                        </span>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="user_dashboard.php" class="nav-link">
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
                    <h1>Selamat Datang, <?php echo htmlspecialchars($userInfo['username'] ?? 'Admin'); ?>!</h1>
                    <p>Kelola sistem Web DocTo dengan mudah dan efisien</p>
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
                            <?php echo $stats['total_users'] > 0 ? '+' : ''; ?><?php echo $stats['total_users']; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-change positive">
                            <?php echo $stats['total_dokter'] > 0 ? '+' : ''; ?><?php echo $stats['total_dokter']; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_dokter']); ?></div>
                    <div class="stat-label">Dokter Aktif</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="stat-change <?php echo $stats['rekam_medis_hari_ini'] > 0 ? 'positive' : 'neutral'; ?>">
                            <?php echo $stats['rekam_medis_hari_ini'] > 0 ? '+' : ''; ?><?php echo $stats['rekam_medis_hari_ini']; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['rekam_medis_hari_ini']); ?></div>
                    <div class="stat-label">Rekam Medis Hari Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="stat-change <?php echo $stats['resep_hari_ini'] > 0 ? 'positive' : ($stats['resep_hari_ini'] < 0 ? 'negative' : 'neutral'); ?>">
                            <?php echo $stats['resep_hari_ini'] > 0 ? '+' : ''; ?><?php echo $stats['resep_hari_ini']; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['resep_hari_ini']); ?></div>
                    <div class="stat-label">Resep Hari Ini</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
                            Aktivitas Terbaru
                        </h3>
                        <button class="btn btn-outline" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php
                                        switch($activity['type']) {
                                            case 'user_registration':
                                                echo '<i class="fas fa-user-plus"></i>';
                                                break;
                                            case 'pemesanan':
                                                echo '<i class="fas fa-shopping-cart"></i>';
                                                break;
                                            case 'rekam_medis':
                                                echo '<i class="fas fa-file-medical"></i>';
                                                break;
                                            default:
                                                echo '<i class="fas fa-info-circle"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <h4><?php echo htmlspecialchars($activity['description']); ?></h4>
                                        <p><?php echo date('d M Y H:i', strtotime($activity['activity_time'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>Tidak ada aktivitas terbaru</h4>
                                    <p>Belum ada aktivitas yang tercatat</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-bolt" style="margin-right: 0.5rem;"></i>
                            Aksi Cepat
                        </h3>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <button class="btn" onclick="window.location.href='dokter_dashboard.php'">
                                <i class="fas fa-user-md"></i>
                                Kelola Dokter
                            </button>
                            <button class="btn" onclick="window.location.href='rekammedis.php'">
                                <i class="fas fa-file-medical"></i>
                                Kelola Rekam Medis
                            </button>
                            <button class="btn" onclick="window.location.href='obat_dashboard.php'">
                                <i class="fas fa-pills"></i>
                                Kelola Obat
                            </button>
                            <button class="btn" onclick="window.location.href='pemesanan_dashboard.php'">
                                <i class="fas fa-shopping-cart"></i>
                                Kelola Pemesanan
                            </button>
                            <button class="btn btn-outline" onclick="window.location.href='laporan.php'">
                                <i class="fas fa-chart-bar"></i>
                                Lihat Laporan
                            </button>
                        </div>
                        
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                            <h4 style="color: #37705D; margin-bottom: 1rem;">
                                <i class="fas fa-bell" style="margin-right: 0.5rem;"></i>
                                Notifikasi Penting
                            </h4>
                            
                            <?php if (!empty($lowStockMedicines)): ?>
                                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <p style="color: #856404; font-size: 0.9rem; margin: 0;">
                                        <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                                        <strong>Stok Obat Menipis:</strong> <?php echo count($lowStockMedicines); ?> jenis obat perlu restok segera
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($pendingOrders)): ?>
                                <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <p style="color: #0c5460; font-size: 0.9rem; margin: 0;">
                                        <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
                                        <strong>Pemesanan Pending:</strong> <?php echo count($pendingOrders); ?> pemesanan menunggu konfirmasi
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 1rem;">
                                <p style="color: #155724; font-size: 0.9rem; margin: 0;">
                                    <i class="fas fa-database" style="margin-right: 0.5rem;"></i>
                                    <strong>Sistem Aktif:</strong> Database terhubung dengan baik. Terakhir update: <?php echo date('d M Y H:i'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="stats-grid" style="margin-top: 2rem;">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-change positive">+<?php echo $stats['pemesanan_hari_ini']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_pemesanan']); ?></div>
                    <div class="stat-label">Total Pemesanan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-change <?php echo $stats['obat_stok_menipis'] > 0 ? 'negative' : 'positive'; ?>">
                            <?php echo $stats['obat_stok_menipis'] > 0 ? '-' : '+'; ?><?php echo $stats['obat_stok_menipis']; ?>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_obat']); ?></div>
                    <div class="stat-label">Jenis Obat</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stat-change positive">+<?php echo $stats['total_admins']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_admins']); ?></div>
                    <div class="stat-label">Total Admin</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-medical-alt"></i>
                        </div>
                        <div class="stat-change positive">+<?php echo $stats['total_rekam_medis']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_rekam_medis']); ?></div>
                    <div class="stat-label">Total Rekam Medis</div>
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

        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.title = `Admin Dashboard - ${timeString}`;
        }

        setInterval(updateTime, 1000);
        updateTime();

        setInterval(function() {
            console.log('Auto refresh data...');
        }, 300000);
    </script>
</body>
</html>