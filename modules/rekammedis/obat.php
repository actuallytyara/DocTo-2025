<?php
include_once 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                createObat();
                break;
            case 'update':
                updateObat();
                break;
            case 'delete':
                deleteObat();
                break;
        }
    }
}

function createObat() {
    global $pdo;
    
    $nama_obat = sanitize($_POST['nama_obat']);
    $jenis_obat = sanitize($_POST['jenis_obat']);
    $harga_obat = sanitize($_POST['harga_obat']);
    $stok_obat = sanitize($_POST['stok_obat']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $tanggal_kadaluarsa = sanitize($_POST['tanggal_kadaluarsa']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO obat (nama_obat, jenis_obat, harga_obat, stok_obat, deskripsi, tanggal_kadaluarsa) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama_obat, $jenis_obat, $harga_obat, $stok_obat, $deskripsi, $tanggal_kadaluarsa]);
        
        $success = "Data obat berhasil ditambahkan!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function updateObat() {
    global $pdo;
    
    $id_obat = sanitize($_POST['id_obat']);
    $nama_obat = sanitize($_POST['nama_obat']);
    $jenis_obat = sanitize($_POST['jenis_obat']);
    $harga_obat = sanitize($_POST['harga_obat']);
    $stok_obat = sanitize($_POST['stok_obat']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $tanggal_kadaluarsa = sanitize($_POST['tanggal_kadaluarsa']);
    
    try {
        $stmt = $pdo->prepare("UPDATE obat SET nama_obat=?, jenis_obat=?, harga_obat=?, stok_obat=?, deskripsi=?, tanggal_kadaluarsa=? WHERE ID_obat=?");
        $stmt->execute([$nama_obat, $jenis_obat, $harga_obat, $stok_obat, $deskripsi, $tanggal_kadaluarsa, $id_obat]);
        
        $success = "Data obat berhasil diupdate!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function deleteObat() {
    global $pdo;
    
    $id_obat = sanitize($_POST['id_obat']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM obat WHERE ID_obat=?");
        $stmt->execute([$id_obat]);
        
        $success = "Data obat berhasil dihapus!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$obat = $pdo->query("SELECT * FROM obat ORDER BY nama_obat ASC")->fetchAll();

$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = sanitize($_GET['edit']);
    $edit_data = $pdo->prepare("SELECT * FROM obat WHERE ID_obat = ?");
    $edit_data->execute([$edit_id]);
    $edit_data = $edit_data->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Obat - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        .badge-stok-rendah {
            background-color: #dc3545;
        }
        .badge-stok-normal {
            background-color: #28a745;
        }
        .badge-expired {
            background-color: #dc3545;
        }
        .badge-akan-expired {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-pills me-2"></i>Management Obat</h1>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-home me-1"></i>Kembali ke Beranda
                </a>
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

        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    <?php echo $edit_data ? 'Edit Obat' : 'Tambah Obat Baru'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_obat" value="<?php echo $edit_data['ID_obat']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Obat *</label>
                            <input type="text" name="nama_obat" class="form-control" value="<?php echo $edit_data ? $edit_data['nama_obat'] : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Obat *</label>
                            <select name="jenis_obat" class="form-select" required>
                                <option value="">Pilih Jenis Obat</option>
                                <option value="Tablet" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Tablet') ? 'selected' : ''; ?>>Tablet</option>
                                <option value="Kapsul" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Kapsul') ? 'selected' : ''; ?>>Kapsul</option>
                                <option value="Sirup" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Sirup') ? 'selected' : ''; ?>>Sirup</option>
                                <option value="Salep" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Salep') ? 'selected' : ''; ?>>Salep</option>
                                <option value="Tetes" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Tetes') ? 'selected' : ''; ?>>Tetes</option>
                                <option value="Injeksi" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Injeksi') ? 'selected' : ''; ?>>Injeksi</option>
                                <option value="Supositoria" <?php echo ($edit_data && $edit_data['jenis_obat'] == 'Supositoria') ? 'selected' : ''; ?>>Supositoria</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Obat (Rp) *</label>
                            <input type="number" name="harga_obat" class="form-control" min="0" step="0.01" value="<?php echo $edit_data ? $edit_data['harga_obat'] : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok Obat *</label>
                            <input type="number" name="stok_obat" class="form-control" min="0" value="<?php echo $edit_data ? $edit_data['stok_obat'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Kadaluarsa *</label>
                            <input type="date" name="tanggal_kadaluarsa" class="form-control" value="<?php echo $edit_data ? $edit_data['tanggal_kadaluarsa'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Obat</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?php echo $edit_data ? $edit_data['deskripsi'] : ''; ?></textarea>
                    </div>
                    
                    <div class="text-end">
                        <?php if ($edit_data): ?>
                            <a href="obat.php" class="btn btn-secondary me-2">Batal</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>
                            <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
       
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Obat</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Obat</th>
                                <th>Jenis</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Kadaluarsa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($obat)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data obat</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($obat as $o): ?>
                                    <?php
                                    // Cek status stok
                                    $stok_class = $o['stok_obat'] <= 10 ? 'badge-stok-rendah' : 'badge-stok-normal';
                                    $stok_text = $o['stok_obat'] <= 10 ? 'Stok Rendah' : 'Stok Normal';
                                    
                                    // Cek status kadaluarsa
                                    $today = new DateTime();
                                    $expired_date = new DateTime($o['tanggal_kadaluarsa']);
                                    $diff = $today->diff($expired_date);
                                    
                                    if ($expired_date < $today) {
                                        $expired_class = 'badge-expired';
                                        $expired_text = 'Expired';
                                    } elseif ($diff->days <= 30) {
                                        $expired_class = 'badge-akan-expired';
                                        $expired_text = 'Akan Expired';
                                    } else {
                                        $expired_class = 'badge-stok-normal';
                                        $expired_text = 'Normal';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo $o['nama_obat']; ?></strong>
                                            <?php if ($o['deskripsi']): ?>
                                                <br><small class="text-muted"><?php echo substr($o['deskripsi'], 0, 50) . (strlen($o['deskripsi']) > 50 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $o['jenis_obat']; ?></td>
                                        <td><?php echo formatRupiah($o['harga_obat']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $stok_class; ?>">
                                                <?php echo $o['stok_obat']; ?> unit
                                            </span>
                                            <br><small><?php echo $stok_text; ?></small>
                                        </td>
                                        <td><?php echo formatTanggalIndo($o['tanggal_kadaluarsa']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $expired_class; ?>">
                                                <?php echo $expired_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $o['ID_obat']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="?edit=<?php echo $o['ID_obat']; ?>" class="btn btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus obat ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id_obat" value="<?php echo $o['ID_obat']; ?>">
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($obat as $o): ?>
        <div class="modal fade" id="detailModal<?php echo $o['ID_obat']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Obat</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6"><strong>Nama Obat:</strong></div>
                            <div class="col-6"><?php echo $o['nama_obat']; ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6"><strong>Jenis Obat:</strong></div>
                            <div class="col-6"><?php echo $o['jenis_obat']; ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6"><strong>Harga:</strong></div>
                            <div class="col-6"><?php echo formatRupiah($o['harga_obat']); ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6"><strong>Stok:</strong></div>
                            <div class="col-6"><?php echo $o['stok_obat']; ?> unit</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6"><strong>Tanggal Kadaluarsa:</strong></div>
                            <div class="col-6"><?php echo formatTanggalIndo($o['tanggal_kadaluarsa']); ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6"><strong>Ditambahkan:</strong></div>
                            <div class="col-6"><?php echo formatTanggalIndo(date('Y-m-d', strtotime($o['created_at']))); ?></div>
                        </div>
                        <?php if ($o['deskripsi']): ?>
                            <hr>
                            <div><strong>Deskripsi:</strong></div>
                            <div><?php echo nl2br($o['deskripsi']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>