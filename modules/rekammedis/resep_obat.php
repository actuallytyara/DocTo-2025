<?php
include_once 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_resep') {
    $id_resep = sanitize($_POST['id_resep']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM resep_obat WHERE ID_resep=?");
        $stmt->execute([$id_resep]);
        
        $success = "Resep obat berhasil dihapus!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$resep_obat = $pdo->query("
    SELECT ro.*, 
           o.nama_obat, o.jenis_obat, o.harga_obat,
           rm.Tanggal, rm.Diagnosa,
           tl.username as nama_pasien,
           d.Username as nama_dokter, d.Spesialisasi
    FROM resep_obat ro
    LEFT JOIN obat o ON ro.ID_obat = o.ID_obat
    LEFT JOIN rekam_medis rm ON ro.ID_rkmmed = rm.ID_rkmmed
    LEFT JOIN tb_login tl ON rm.ID_user = tl.ID_user
    LEFT JOIN dokter d ON rm.ID_dokter = d.ID_dokter
    ORDER BY ro.created_at DESC
")->fetchAll();

$resep_totals = [];
foreach ($resep_obat as $resep) {
    $total = $resep['jumlah_obat'] * $resep['harga_obat'];
    $resep_totals[$resep['ID_resep']] = $total;
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Obat - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-section {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            padding: 2rem 0;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            border: none;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
        }
        .resep-card {
            border-left: 4px solid #6f42c1;
        }
        .badge-obat {
            font-size: 0.8em;
        }
        .resep-item {
            transition: all 0.3s ease;
        }
        .resep-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 2rem 0 rgba(33, 40, 50, 0.2);
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-prescription-bottle-alt me-2"></i>Resep Obat</h1>
                <div>
                    <a href="rekam_medis.php" class="btn btn-light me-2">
                        <i class="fas fa-clipboard-list me-1"></i>Rekam Medis
                    </a>
                    <a href="admin_dashboard.php" class="btn btn-light">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title">Total Resep</h6>
                                <h3 class="mb-0"><?php echo count($resep_obat); ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-prescription-bottle-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title">Total Nilai</h6>
                                <h3 class="mb-0"><?php echo formatRupiah(array_sum($resep_totals)); ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title">Resep Hari Ini</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $today_resep = array_filter($resep_obat, function($r) {
                                        return date('Y-m-d', strtotime($r['created_at'])) == date('Y-m-d');
                                    });
                                    echo count($today_resep);
                                    ?>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title">Rata-rata Nilai</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $avg = count($resep_totals) > 0 ? array_sum($resep_totals) / count($resep_totals) : 0;
                                    echo formatRupiah($avg);
                                    ?>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Resep Obat</h5>
                <a href="tambah_resep.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Tambah Resep
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($resep_obat)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-prescription-bottle-alt fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada resep obat</h5>
                        <p class="text-muted">Mulai dengan menambahkan resep obat pertama</p>
                        <a href="tambah_resep.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Tambah Resep
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pasien</th>
                                    <th>Dokter</th>
                                    <th>Obat</th>
                                    <th>Jumlah</th>
                                    <th>Dosis</th>
                                    <th>Total Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($resep_obat as $resep): ?>
                                    <tr class="resep-item">
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($resep['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($resep['nama_pasien']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($resep['nama_dokter']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($resep['Spesialisasi']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($resep['nama_obat']); ?></strong>
                                                <br><span class="badge bg-secondary badge-obat"><?php echo htmlspecialchars($resep['jenis_obat']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $resep['jumlah_obat']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($resep['dosis']); ?></td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatRupiah($resep_totals[$resep['ID_resep']]); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="detail_resep.php?id=<?php echo $resep['ID_resep']; ?>" 
                                                   class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_resep.php?id=<?php echo $resep['ID_resep']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?php echo $resep['ID_resep']; ?>)" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus resep obat ini?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_resep">
                        <input type="hidden" name="id_resep" id="delete_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            document.getElementById('delete_id').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>