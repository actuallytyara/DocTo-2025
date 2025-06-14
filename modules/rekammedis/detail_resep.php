<?php
include_once 'koneksi.php';
session_start();

$id_resep = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_resep <= 0) {
    header("Location: resep_obat.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM resep_obat WHERE ID_resep = ?");
$stmt->execute([$id_resep]);
$resep_basic = $stmt->fetch();

if (!$resep_basic) {
    echo "Resep tidak ditemukan dengan ID: " . $id_resep;
    exit();
}

$obat = null;
if (!empty($resep_basic['ID_obat'])) {
    $stmt_obat = $pdo->prepare("SELECT * FROM obat WHERE ID_obat = ?");
    $stmt_obat->execute([$resep_basic['ID_obat']]);
    $obat = $stmt_obat->fetch();
}

$rekam_medis = null;
if (!empty($resep_basic['ID_rkmmed'])) {
    $stmt_rm = $pdo->prepare("SELECT * FROM rekam_medis WHERE ID_rkmmed = ?");
    $stmt_rm->execute([$resep_basic['ID_rkmmed']]);
    $rekam_medis = $stmt_rm->fetch();
}

$pasien = null;
if ($rekam_medis && !empty($rekam_medis['ID_user'])) {
    $stmt_pasien = $pdo->prepare("SELECT * FROM tb_login WHERE ID_user = ?");
    $stmt_pasien->execute([$rekam_medis['ID_user']]);
    $pasien = $stmt_pasien->fetch();
}

$dokter = null;
if ($rekam_medis && !empty($rekam_medis['ID_dokter'])) {
    $stmt_dokter = $pdo->prepare("SELECT * FROM dokter WHERE ID_dokter = ?");
    $stmt_dokter->execute([$rekam_medis['ID_dokter']]);
    $dokter = $stmt_dokter->fetch();
}

$harga_obat = $obat ? $obat['harga_obat'] : 0;
$jumlah_obat = $resep_basic['jumlah_obat'] ?? 0;
$total_harga = $jumlah_obat * $harga_obat;

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    if (empty($tanggal)) return 'N/A';
    return date('d F Y', strtotime($tanggal));
}

function formatDateTime($datetime) {
    if (empty($datetime)) return 'N/A';
    return date('d F Y, H:i', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Resep Obat - DocTo</title>
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
        .btn-primary {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
        }
        .detail-card {
            border-left: 4px solid #6f42c1;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9em;
        }
        .info-value {
            font-size: 1.1em;
            margin-bottom: 1rem;
        }
        .prescription-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .price-highlight {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .badge-custom {
            font-size: 0.9em;
            padding: 8px 12px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            
            .header-section {
                background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%) !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            
            .btn {
                border: 1px solid #333 !important;
            }
        }
    </style>
</head>
<body>
    <div class="header-section no-print">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-file-medical me-2"></i>Detail Resep Obat</h1>
                <div>
                    <button onclick="window.print()" class="btn btn-light me-2">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <a href="resep_obat.php" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="prescription-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1">Resep #<?php echo str_pad($resep_basic['ID_resep'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar me-1"></i>
                        Dibuat pada <?php 
                        if (isset($resep_basic['created_at']) && !empty($resep_basic['created_at'])) {
                            echo formatDateTime($resep_basic['created_at']);
                        } elseif ($rekam_medis && isset($rekam_medis['Tanggal'])) {
                            echo formatTanggal($rekam_medis['Tanggal']);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="price-highlight">
                        <h4 class="mb-0">Total</h4>
                        <h3 class="mb-0"><?php echo formatRupiah($total_harga); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card detail-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Pasien</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-label">Nama Pasien</div>
                        <div class="info-value"><?php echo $pasien ? htmlspecialchars($pasien['username']) : 'N/A'; ?></div>
                        
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo $pasien ? htmlspecialchars($pasien['email']) : 'N/A'; ?></div>
                        
                        <div class="info-label">Tanggal Pemeriksaan</div>
                        <div class="info-value"><?php echo $rekam_medis ? formatTanggal($rekam_medis['Tanggal']) : 'N/A'; ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card detail-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Informasi Dokter</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-label">Nama Dokter</div>
                        <div class="info-value"><?php echo $dokter ? htmlspecialchars($dokter['Username']) : 'N/A'; ?></div>
                        
                        <div class="info-label">Spesialisasi</div>
                        <div class="info-value">
                            <span class="badge bg-primary badge-custom">
                                <?php echo $dokter ? htmlspecialchars($dokter['Spesialisasi']) : 'N/A'; ?>
                            </span>
                        </div>
                        
                        <div class="info-label">No. Telepon</div>
                        <div class="info-value"><?php echo $dokter ? htmlspecialchars($dokter['Nomor_telepon']) : 'N/A'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($rekam_medis): ?>
        <div class="card detail-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Informasi Rekam Medis</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Keluhan</div>
                        <div class="info-value"><?php echo htmlspecialchars($rekam_medis['keluhan'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Diagnosa</div>
                        <div class="info-value"><?php echo htmlspecialchars($rekam_medis['Diagnosa'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card detail-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2"></i>Detail Resep Obat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Nama Obat</div>
                        <div class="info-value">
                            <strong><?php echo $obat ? htmlspecialchars($obat['nama_obat']) : 'N/A'; ?></strong>
                        </div>
                        
                        <div class="info-label">Jenis Obat</div>
                        <div class="info-value">
                            <span class="badge bg-secondary badge-custom">
                                <?php echo $obat ? htmlspecialchars($obat['jenis_obat']) : 'N/A'; ?>
                            </span>
                        </div>
                        
                        <div class="info-label">Harga per Unit</div>
                        <div class="info-value text-success">
                            <strong><?php echo formatRupiah($harga_obat); ?></strong>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-label">Jumlah Obat</div>
                        <div class="info-value">
                            <span class="badge bg-primary badge-custom fs-6">
                                <?php echo $jumlah_obat; ?> Unit
                            </span>
                        </div>
                        
                        <div class="info-label">Dosis</div>
                        <div class="info-value">
                            <strong><?php echo htmlspecialchars($resep_basic['dosis'] ?? 'N/A'); ?></strong>
                        </div>
                        
                        <div class="info-label">Total Harga</div>
                        <div class="info-value text-success">
                            <h5 class="mb-0"><?php echo formatRupiah($total_harga); ?></h5>
                        </div>
                    </div>
                </div>

                <?php if (!empty($resep_basic['instruksi'])): ?>
                    <hr>
                    <div class="info-label">Instruksi Penggunaan</div>
                    <div class="info-value">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo nl2br(htmlspecialchars($resep_basic['instruksi'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($obat && !empty($obat['deskripsi'])): ?>
                    <div class="info-label">Deskripsi Obat</div>
                    <div class="info-value">
                        <?php echo nl2br(htmlspecialchars($obat['deskripsi'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4 no-print">
            <div>
                <a href="resep_obat.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
                </a>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-info me-2">
                    <i class="fas fa-print me-1"></i>Cetak Resep
                </button>
                <a href="edit_resep.php?id=<?php echo $resep_basic['ID_resep']; ?>" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-1"></i>Edit Resep
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $resep_basic['ID_resep']; ?>)">
                    <i class="fas fa-trash me-1"></i>Hapus Resep
                </button>
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
                    <div class="alert alert-warning">
                        <strong>Resep:</strong> <?php echo $obat ? htmlspecialchars($obat['nama_obat']) : 'N/A'; ?><br>
                        <strong>Pasien:</strong> <?php echo $pasien ? htmlspecialchars($pasien['username']) : 'N/A'; ?><br>
                        <strong>Total:</strong> <?php echo formatRupiah($total_harga); ?>
                    </div>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="resep_obat.php" style="display: inline;">
                        <input type="hidden" name="action" value="delete_resep">
                        <input type="hidden" name="id_resep" value="<?php echo $resep_basic['ID_resep']; ?>">
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
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>