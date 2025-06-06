<?php
include_once 'koneksi.php';
session_start();
 
$stats = [];
 
$stats['total_resep'] = $pdo->query("SELECT COUNT(*) FROM resep_obat")->fetchColumn();
 
$stats['total_obat'] = $pdo->query("SELECT COUNT(*) FROM obat")->fetchColumn();
 
$stats['total_dokter'] = $pdo->query("SELECT COUNT(*) FROM dokter")->fetchColumn();
 
$stats['total_pasien'] = $pdo->query("SELECT COUNT(*) FROM tb_login WHERE pengguna != 'user'")->fetchColumn();
 
$total_nilai = $pdo->query("
    SELECT SUM(ro.jumlah_obat * o.harga_obat) as total
    FROM resep_obat ro 
    LEFT JOIN obat o ON ro.ID_obat = o.ID_obat
")->fetchColumn();
$stats['total_nilai'] = $total_nilai ?: 0;
 
$stats['resep_hari_ini'] = $pdo->query("
    SELECT COUNT(*) FROM resep_obat 
    WHERE DATE(created_at) = CURDATE()
")->fetchColumn();
 
$obat_populer = $pdo->query("
    SELECT o.nama_obat, COUNT(*) as jumlah
    FROM resep_obat ro
    LEFT JOIN obat o ON ro.ID_obat = o.ID_obat
    GROUP BY ro.ID_obat
    ORDER BY jumlah DESC
    LIMIT 5
")->fetchAll();
 
$dokter_aktif = $pdo->query("
    SELECT d.Username, COUNT(*) as jumlah_resep
    FROM resep_obat ro
    LEFT JOIN rekam_medis rm ON ro.ID_rkmmed = rm.ID_rkmmed
    LEFT JOIN dokter d ON rm.ID_dokter = d.ID_dokter
    GROUP BY rm.ID_dokter
    ORDER BY jumlah_resep DESC
    LIMIT 5
")->fetchAll();
 
$recent_resep = $pdo->query("
    SELECT ro.*, 
           o.nama_obat,
           tl.username as nama_pasien,
           d.Username as nama_dokter
    FROM resep_obat ro
    LEFT JOIN obat o ON ro.ID_obat = o.ID_obat
    LEFT JOIN rekam_medis rm ON ro.ID_rkmmed = rm.ID_rkmmed
    LEFT JOIN tb_login tl ON rm.ID_user = tl.ID_user
    LEFT JOIN dokter d ON rm.ID_dokter = d.ID_dokter
    ORDER BY ro.created_at DESC
    LIMIT 10
")->fetchAll();
 
$monthly_stats = $pdo->query("
    SELECT 
        MONTH(ro.created_at) as bulan,
        YEAR(ro.created_at) as tahun,
        COUNT(*) as jumlah_resep,
        SUM(ro.jumlah_obat * o.harga_obat) as total_nilai
    FROM resep_obat ro
    LEFT JOIN obat o ON ro.ID_obat = o.ID_obat
    WHERE ro.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY YEAR(ro.created_at), MONTH(ro.created_at)
    ORDER BY tahun, bulan
")->fetchAll();

 
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
 
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4a9b8e;
            --secondary-color: #6c7b7f;
            --accent-color: #f8f9fa;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem;
            color: white !important;
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2c6e49 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            border: none;
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(33, 40, 50, 0.25);
        }

        .stats-card {
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,1) 100%);
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #2c6e49;
            transform: translateY(-2px);
        }

        .btn-light {
            border-radius: 8px;
            padding: 10px 16px;
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            transform: translateY(-2px);
        }

        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            border-left: 4px solid var(--primary-color);
            background: white;
            margin-bottom: 12px;
            padding: 18px;
            border-radius: 0 12px 12px 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .activity-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .chart-container {
            position: relative;
            height: 350px;
            padding: 20px;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-color);
            border-radius: 8px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(74, 155, 142, 0.05);
        }

        .badge {
            border-radius: 6px;
            padding: 6px 12px;
        }

        .popular-item {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .popular-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: rgba(74, 155, 142, 0.1);
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), #2c6e49);
            border-radius: 4px;
        }

        .stats-gradient-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-gradient-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stats-gradient-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stats-gradient-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stats-gradient-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .stats-gradient-6 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

        .dashboard-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .card-header {
            background: transparent;
            border-bottom: 2px solid rgba(74, 155, 142, 0.1);
            padding: 1.25rem 1.5rem;
        }

        .card-header h5 {
            color: var(--secondary-color);
            font-weight: 600;
        }

        .text-muted-custom {
            color: #6c7b7f !important;
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        @media (max-width: 768px) {
            .header-section .d-flex {
                flex-direction: column;
                text-align: center;
            }
            
            .header-section .btn {
                margin: 5px;
            }
            
            .dashboard-title {
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body> 
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="navbar-brand me-4">
                        <i class="fas fa-stethoscope me-2"></i>DocTo
                    </div>
                    <h1 class="dashboard-title"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
                </div>
                <div class="d-flex flex-wrap">
                    <a href="admin_users.php" class="btn btn-light me-2">
                        <i class="fas fa-users me-1"></i>Kelola User
                    </a>
                    <a href="resep_obat.php" class="btn btn-light me-2">
                        <i class="fas fa-prescription-bottle-alt me-1"></i>Resep Obat
                    </a>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4"> 
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-gradient-1 text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon text-white me-3">
                                <i class="fas fa-prescription-bottle-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0 opacity-75">Total Resep</h6>
                                <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_resep']); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-gradient-2 text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon text-white me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0 opacity-75">Total Nilai</h6>
                                <h4 class="mb-0 fw-bold" style="font-size: 1.4rem;"><?php echo formatRupiah($stats['total_nilai']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-gradient-3 text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon text-white me-3">
                                <i class="fas fa-pills"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0 opacity-75">Total Obat</h6>
                                <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_obat']); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-gradient-4 text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon text-white me-3">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0 opacity-75">Total Dokter</h6>
                                <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_dokter']); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stats-gradient-5 text-white">
                    <div class="card-body text-center py-4">
                        <div class="icon-circle bg-white bg-opacity-25 mx-auto mb-3">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                        <h5 class="opacity-75">Resep Hari Ini</h5>
                        <h2 class="fw-bold"><?php echo number_format($stats['resep_hari_ini']); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-gradient-6 text-dark">
                    <div class="card-body text-center py-4">
                        <div class="icon-circle bg-white bg-opacity-50 mx-auto mb-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <h5 class="text-muted-custom">Total Pasien</h5>
                        <h2 class="fw-bold text-primary"><?php echo number_format($stats['total_pasien']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistik Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Obat Populer</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($obat_populer)): ?>
                            <?php foreach ($obat_populer as $index => $obat): ?>
                                <div class="popular-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($obat['nama_obat']); ?></h6>
                                        <span class="badge bg-primary"><?php echo $obat['jumlah']; ?>x</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo ($obat['jumlah'] / $obat_populer[0]['jumlah']) * 100; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Belum ada data obat populer</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Dokter Teraktif</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dokter_aktif)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Dokter</th>
                                            <th class="text-center">Jumlah Resep</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dokter_aktif as $dokter): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                                                            <i class="fas fa-user-md text-primary"></i>
                                                        </div>
                                                        <span class="fw-medium"><?php echo htmlspecialchars($dokter['Username']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success"><?php echo $dokter['jumlah_resep']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Belum ada data dokter aktif</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="recent-activity">
                            <?php if (!empty($recent_resep)): ?>
                                <?php foreach ($recent_resep as $resep): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="activity-content">
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($resep['nama_obat']); ?></h6>
                                                <p class="mb-1 text-muted small">
                                                    Pasien: <span class="fw-medium"><?php echo htmlspecialchars($resep['nama_pasien']); ?></span><br>
                                                    Dokter: <span class="fw-medium"><?php echo htmlspecialchars($resep['nama_dokter']); ?></span>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo formatDateTime($resep['created_at']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-info"><?php echo $resep['jumlah_obat']; ?>x</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">Belum ada aktivitas terbaru</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="tambah_obat.php" class="btn btn-primary w-100 py-3">
                                    <i class="fas fa-plus-circle fa-2x mb-2 d-block"></i>
                                    Tambah Obat
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="tambah_dokter.php" class="btn btn-success w-100 py-3">
                                    <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                    Tambah Dokter
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="laporan.php" class="btn btn-info w-100 py-3">
                                    <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                    Lihat Laporan
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="pengaturan.php" class="btn btn-warning w-100 py-3">
                                    <i class="fas fa-cog fa-2x mb-2 d-block"></i>
                                    Pengaturan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script> 
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_stats); ?>;
        
        const labels = monthlyData.map(item => {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                          'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return months[item.bulan - 1] + ' ' + item.tahun;
        });
        
        const resepData = monthlyData.map(item => item.jumlah_resep);
        const nilaiData = monthlyData.map(item => item.total_nilai / 1000000); // komversi ke juta
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Resep',
                    data: resepData,
                    borderColor: '#4a9b8e',
                    backgroundColor: 'rgba(74, 155, 142, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Total Nilai (Juta Rp)',
                    data: nilaiData,
                    borderColor: '#f5576c',
                    backgroundColor: 'rgba(245, 87, 108, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
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
    </script>
</body>
</html>