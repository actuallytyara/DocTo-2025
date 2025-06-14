<?php
$host = 'localhost';
$dbname = 'login';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rekam_medis");
$stats['total_rekam_medis'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rekam_medis WHERE DATE(Tanggal) = CURDATE()");
$stats['rekam_medis_hari_ini'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT rm.ID_user) as total FROM rekam_medis rm 
                     JOIN tb_login u ON rm.ID_user = u.ID_user WHERE u.pengguna = 'user'");
$stats['total_pasien'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM dokter");
$stats['dokter_aktif'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT 
        rm.ID_rkmmed,
        rm.Diagnosa,
        rm.Tanggal,
        rm.keluhan,
        rm.tekanan_darah,
        rm.berat_badan,
        rm.tinggi_badan,
        u.username as nama_pasien,
        u.ID_user,
        d.Username as nama_dokter,
        CASE 
            WHEN rm.Tanggal >= CURDATE() THEN 'Aktif'
            WHEN rm.Tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 'Follow-up'
            ELSE 'Selesai'
        END as status
    FROM rekam_medis rm
    JOIN tb_login u ON rm.ID_user = u.ID_user
    JOIN dokter d ON rm.ID_dokter = d.ID_dokter
    ORDER BY rm.Tanggal DESC, rm.created_at DESC
    LIMIT 10
");
$stmt->execute();
$rekam_medis_data = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT 
        DATE(Tanggal) as tanggal,
        COUNT(*) as jumlah
    FROM rekam_medis 
    WHERE Tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(Tanggal)
    ORDER BY tanggal ASC
");
$weekly_stats = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT 
        Diagnosa,
        COUNT(*) as jumlah
    FROM rekam_medis
    GROUP BY Diagnosa
    ORDER BY jumlah DESC
    LIMIT 5
");
$popular_diagnosis = $stmt->fetchAll();

$stmt = $pdo->query("SELECT ID_dokter, Username FROM dokter ORDER BY Username");
$dokter_list = $stmt->fetchAll();

function getAge($userId) {
    $ages = [25, 30, 35, 40, 28, 32, 45, 38];
    return $ages[$userId % count($ages)];
}

function getGender($nama) {
    $female_names = ['Siti', 'Maya', 'Tyara', 'Lita', 'Nayla'];
    foreach($female_names as $female) {
        if(stripos($nama, $female) !== false) return 'P';
    }
    return 'L';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Rekam Medis</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/rekammedis.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>
                    <span class="icon"><i class="fas fa-file-medical"></i></span>
                    Rekam Medis
                </h2>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="../dashboard.php" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-users"></i></span>
                        Dashboard User
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-user-cog"></i></span>
                        Dashboard Admin
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-user-md"></i></span>
                        Dashboard Dokter
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link active">
                        <span class="nav-icon"><i class="fas fa-file-medical-alt"></i></span>
                          Dashboard Rekam Medis
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                        Dashboard Janji Temu
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-pills"></i></span>
                        Dashboard Obat
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-prescription-bottle-alt"></i></span>
                        Dashboard Resep Obat
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                        Laporan
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="welcome-section">
                    <h1><i class="fas fa-file-medical me-2"></i>Dashboard Rekam Medis</h1>
                    <p>Kelola dan pantau rekam medis pasien secara komprehensif</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div class="user-details">
                        <h3>Admin Rekam Medis</h3>
                        <p>Super Administrator</p>
                    </div>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-file-medical"></i></div>
                        <div class="stat-change positive">+<?php echo $stats['rekam_medis_hari_ini']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_rekam_medis']); ?></div>
                    <div class="stat-label">Total Rekam Medis</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                        <div class="stat-change positive">+<?php echo $stats['rekam_medis_hari_ini']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo $stats['rekam_medis_hari_ini']; ?></div>
                    <div class="stat-label">Rekam Medis Hari Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-user-injured"></i></div>
                        <div class="stat-change positive">+<?php echo $stats['total_pasien']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_pasien']); ?></div>
                    <div class="stat-label">Total Pasien</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-stethoscope"></i></div>
                        <div class="stat-change positive">+<?php echo $stats['dokter_aktif']; ?></div>
                    </div>
                    <div class="stat-number"><?php echo $stats['dokter_aktif']; ?></div>
                    <div class="stat-label">Dokter Aktif</div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <input type="text" class="search-input" placeholder="Cari pasien, dokter, atau diagnosis..." id="searchInput">
                <select class="filter-select" id="filterDokter">
                    <option value="">Semua Dokter</option>
                    <?php foreach($dokter_list as $dokter): ?>
                        <option value="<?php echo $dokter['ID_dokter']; ?>">Dr. <?php echo htmlspecialchars($dokter['Username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" id="filterTanggal">
                    <option value="">Semua Tanggal</option>
                    <option value="hari-ini">Hari Ini</option>
                    <option value="minggu-ini">Minggu Ini</option>
                    <option value="bulan-ini">Bulan Ini</option>
                </select>
                <button class="btn" onclick="tambahRekamMedis()">
                    <i class="fas fa-plus"></i>
                    Tambah Rekam Medis
                </button>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Data Rekam Medis -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Data Rekam Medis Terbaru</h3>
                        <button class="btn btn-outline" onclick="exportData()">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pasien</th>
                                        <th>Dokter</th>
                                        <th>Diagnosis</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="rekamMedisTable">
                                    <?php foreach($rekam_medis_data as $rm): ?>
                                    <tr>
                                        <td>RM<?php echo str_pad($rm['ID_rkmmed'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($rm['nama_pasien']); ?></strong><br>
                                                <small class="text-muted"><?php echo getGender($rm['nama_pasien']); ?>, <?php echo getAge($rm['ID_user']); ?> tahun</small>
                                            </div>
                                        </td>
                                        <td>Dr. <?php echo htmlspecialchars($rm['nama_dokter']); ?></td>
                                        <td><?php echo htmlspecialchars($rm['Diagnosa']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rm['Tanggal'])); ?></td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            switch($rm['status']) {
                                                case 'Aktif': $status_class = 'status-active'; break;
                                                case 'Follow-up': $status_class = 'status-pending'; break;
                                                case 'Selesai': $status_class = 'status-completed'; break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $rm['status']; ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm" onclick="viewRecord(<?php echo $rm['ID_rkmmed']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editRecord(<?php echo $rm['ID_rkmmed']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistik dan Aktivitas -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Chart Card -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-line"></i> Statistik Mingguan</h3>
                        </div>
                        <div class="card-content">
                            <div class="chart-container">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-clock"></i> Aktivitas Terbaru</h3>
                        </div>
                        <div class="card-content">
                            <?php 
                            $recent_activities = array_slice($rekam_medis_data, 0, 3);
                            foreach($recent_activities as $index => $activity): 
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas <?php echo $index == 0 ? 'fa-plus' : ($index == 1 ? 'fa-edit' : 'fa-file-export'); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo $index == 0 ? 'Rekam medis baru dibuat' : ($index == 1 ? 'Rekam medis diperbarui' : 'Rekam medis dilihat'); ?></h4>
                                    <p>RM<?php echo str_pad($activity['ID_rkmmed'], 3, '0', STR_PAD_LEFT); ?> - <?php echo htmlspecialchars($activity['nama_pasien']); ?> oleh Dr. <?php echo htmlspecialchars($activity['nama_dokter']); ?><br>
                                    <small class="text-muted"><?php 
                                        $diff = time() - strtotime($activity['Tanggal']);
                                        if($diff < 3600) echo floor($diff/60) . ' menit lalu';
                                        elseif($diff < 86400) echo floor($diff/3600) . ' jam lalu';
                                        else echo floor($diff/86400) . ' hari lalu';
                                    ?></small></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Diagnosis Terpopuler -->
            <div class="content-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Diagnosis Terpopuler</h3>
                </div>
                <div class="card-content">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <?php foreach(array_slice($popular_diagnosis, 0, 5) as $diagnosis): ?>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                            <div style="background: #37705D; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem;">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <h4 style="color: #37705D;"><?php echo htmlspecialchars($diagnosis['Diagnosa']); ?></h4>
                            <p style="color: #666; margin: 0;"><?php echo $diagnosis['jumlah']; ?> kasus</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const weeklyData = <?php echo json_encode($weekly_stats); ?>;
        
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeklyData.map(item => {
                    const date = new Date(item.tanggal);
                    return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Rekam Medis',
                    data: weeklyData.map(item => item.jumlah),
                    borderColor: '#37705D',
                    backgroundColor: 'rgba(55, 112, 93, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        }

        function viewRecord(id) {
            alert('Melihat rekam medis ID: ' + id);
        }

        function editRecord(id) {
            alert('Edit rekam medis ID: ' + id);
        }

        function tambahRekamMedis() {
            alert('Form tambah rekam medis akan dibuka');
        }

        function exportData() {
            alert('Export data rekam medis');
        }
        document.getElementById('searchInput').addEventListener('input', function() {
            console.log('Searching for:', this.value);
        });

        document.getElementById('filterDokter').addEventListener('change', function() {
            console.log('Filter dokter:', this.value);
        });

        document.getElementById('filterTanggal').addEventListener('change', function() {
            console.log('Filter tanggal:', this.value);
        });
    </script>
</body>
</html>