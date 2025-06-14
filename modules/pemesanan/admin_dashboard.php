<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    try {
        $id_pemesanan = (int) $_POST['id_pemesanan'];
        $status_baru = $_POST['status_pemesanan'];
        
        $stmt = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = ? WHERE ID_pemesanan = ?");
        $result = $stmt->execute([$status_baru, $id_pemesanan]);
        
        if ($result) {
            $success_msg = "Status pesanan berhasil diperbarui!";
        } else {
            $error_msg = "Gagal memperbarui status pesanan!";
        }
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT p.*, u.username, u.email, p.nomor_telepon as user_phone
    FROM pemesanan p 
    JOIN tb_login u ON p.ID_user = u.ID_user 
    WHERE 1=1
";

$params = [];

if (!empty($status_filter)) {
    $query .= " AND p.status_pemesanan = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    $query .= " AND DATE(p.tanggal_pemesanan) = ?";
    $params[] = $date_filter;
}

if (!empty($search)) {
    $query .= " AND (p.nomor_pemesanan LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.tanggal_pemesanan DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        COUNT(CASE WHEN status_pemesanan = 'pending' THEN 1 END) as pending_orders,
        COUNT(CASE WHEN status_pemesanan = 'dikonfirmasi' THEN 1 END) as confirmed_orders,
        COUNT(CASE WHEN status_pemesanan = 'dikirim' THEN 1 END) as shipped_orders,
        COUNT(CASE WHEN status_pemesanan = 'selesai' THEN 1 END) as completed_orders,
        COUNT(CASE WHEN status_pemesanan = 'dibatalkan' THEN 1 END) as cancelled_orders,
        SUM(total_harga) as total_revenue,
        SUM(CASE WHEN status_pemesanan = 'selesai' THEN total_harga ELSE 0 END) as completed_revenue
    FROM pemesanan 
    WHERE DATE(tanggal_pemesanan) = CURDATE()
";

$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            color:rgb(255, 255, 255) !important;
        }
        .btn-primary {
            background-color: #37966f;
            border-color: #37966f;
        }
        .btn-primary:hover {
            background-color: #1e3f35;
            border-color: #1e3f35;
        }
        .navbar {
             background: linear-gradient(90deg, #356859, #37966f);
        }
        .stat-card {
            background: linear-gradient(135deg, #2c5f52 0%, #1e3f35 100%);
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .stat-card-light {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.8em;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .order-actions {
            display: flex;
            gap: 5px;
        }
        .order-actions .btn {
            padding: 5px 10px;
            font-size: 0.8em;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-md me-2"></i>DocTo Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="kelola_obat.php">
                    <i class="fas fa-pills me-1"></i>Kelola Obat
                </a>
                <a class="nav-link" href="kelola_user.php">
                    <i class="fas fa-users me-1"></i>Kelola User
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $stats['total_orders'] ?></div>
                    <div class="stat-label">
                        <i class="fas fa-shopping-cart me-1"></i>Total Pesanan Hari Ini
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $stats['pending_orders'] ?></div>
                    <div class="stat-label">
                        <i class="fas fa-clock me-1"></i>Menunggu Konfirmasi
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $stats['completed_orders'] ?></div>
                    <div class="stat-label">
                        <i class="fas fa-check-circle me-1"></i>Pesanan Selesai
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= formatRupiah($stats['completed_revenue'] ?? 0) ?></div>
                    <div class="stat-label">
                        <i class="fas fa-money-bill-wave me-1"></i>Pendapatan Hari Ini
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Pesanan</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status Pesanan</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="dikonfirmasi" <?= $status_filter == 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                        <option value="dikirim" <?= $status_filter == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                        <option value="selesai" <?= $status_filter == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="dibatalkan" <?= $status_filter == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="<?= $date_filter ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nomor pesanan atau nama user..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Pesanan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted">Tidak ada pesanan ditemukan</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($order['nomor_pemesanan']) ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($order['username']) ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($order['email']) ?><br>
                                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($order['user_phone']) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])) ?>
                                        </td>
                                        <td>
                                            <strong><?= formatRupiah($order['total_harga']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getStatusBadgeClass($order['status_pemesanan']) ?>">
                                                <?= ucfirst($order['status_pemesanan']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="order-actions">
                                                <button class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#orderModal<?= $order['ID_pemesanan'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_pemesanan" value="<?= $order['ID_pemesanan'] ?>">
                                                    <select name="status_pemesanan" class="form-select form-select-sm" 
                                                            style="width: auto; display: inline-block;"
                                                            onchange="this.form.submit()">
                                                        <option value="pending" <?= $order['status_pemesanan'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="dikonfirmasi" <?= $order['status_pemesanan'] == 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                                        <option value="dikirim" <?= $order['status_pemesanan'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                        <option value="selesai" <?= $order['status_pemesanan'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                        <option value="dibatalkan" <?= $order['status_pemesanan'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="orderModal<?= $order['ID_pemesanan'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-receipt me-2"></i>Detail Pesanan #<?= htmlspecialchars($order['nomor_pemesanan']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Informasi Pelanggan</h6>
                                                            <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($order['username']) ?></p>
                                                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                                                            <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($order['user_phone']) ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Informasi Pesanan</h6>
                                                            <p class="mb-1"><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])) ?></p>
                                                            <p class="mb-1"><strong>Status:</strong> 
                                                                <span class="badge bg-<?= getStatusBadgeClass($order['status_pemesanan']) ?>">
                                                                    <?= ucfirst($order['status_pemesanan']) ?>
                                                                </span>
                                                            </p>
                                                            <p class="mb-1"><strong>Total:</strong> <?= formatRupiah($order['total_harga']) ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Alamat Pengiriman</h6>
                                                            <p><?= htmlspecialchars($order['alamat_pengiriman']) ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($order['catatan'])): ?>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Catatan</h6>
                                                            <p><?= htmlspecialchars($order['catatan']) ?></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>