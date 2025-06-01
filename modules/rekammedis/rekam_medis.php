<?php
include_once 'koneksi.php';
session_start();

// Fungsi untuk format tanggal Indonesia
if (!function_exists('formatTanggalIndo')) {
    function formatTanggalIndo($tanggal) {
        if (empty($tanggal)) return '-';
        
        $bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        if (count($pecahkan) == 3) {
            return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
        }
        return $tanggal;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                createRekamMedis();
                break;
            case 'update':
                updateRekamMedis();
                break;
            case 'delete':
                deleteRekamMedis();
                break;
            case 'add_resep':
                addResepObat();
                break;
        }
    }
}

// Fungsi CRUD
function createRekamMedis() {
    global $pdo, $success, $error;
   
    error_log("POST Data: " . print_r($_POST, true));
    
    if (empty($_POST['id_user']) || empty($_POST['id_dokter']) || 
        empty($_POST['diagnosa']) || empty($_POST['tanggal']) || 
        empty($_POST['pengobatan']) || empty($_POST['keluhan'])) {
        $error = "Semua field yang bertanda * wajib diisi!";
        return;
    }
    
    $id_user = intval($_POST['id_user']);
    $id_dokter = intval($_POST['id_dokter']);
    $diagnosa = sanitize($_POST['diagnosa']);
    $tanggal = sanitize($_POST['tanggal']);
    $pengobatan = sanitize($_POST['pengobatan']);
    $keluhan = sanitize($_POST['keluhan']);
    $tekanan_darah = !empty($_POST['tekanan_darah']) ? sanitize($_POST['tekanan_darah']) : null;
    $berat_badan = !empty($_POST['berat_badan']) ? floatval($_POST['berat_badan']) : null;
    $tinggi_badan = !empty($_POST['tinggi_badan']) ? floatval($_POST['tinggi_badan']) : null;
    
    if (empty($diagnosa) || empty($pengobatan) || empty($keluhan)) {
        $error = "Field Diagnosa, Pengobatan, dan Keluhan tidak boleh kosong setelah sanitasi!";
        return;
    }
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $error = "Format tanggal harus YYYY-MM-DD!";
        return;
    }
   
    try {
        $check_user = $pdo->prepare("SELECT ID_user FROM tb_login WHERE ID_user = ?");
        $check_user->execute([$id_user]);
        if (!$check_user->fetch()) {
            $error = "Pasien dengan ID $id_user tidak ditemukan!";
            return;
        }
        
        $check_dokter = $pdo->prepare("SELECT ID_dokter FROM dokter WHERE ID_dokter = ?");
        $check_dokter->execute([$id_dokter]);
        if (!$check_dokter->fetch()) {
            $error = "Dokter dengan ID $id_dokter tidak ditemukan!";
            return;
        }
        
    } catch(PDOException $e) {
        $error = "Error validasi: " . $e->getMessage();
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO rekam_medis (ID_user, ID_dokter, Diagnosa, Tanggal, Pengobatan, keluhan, tekanan_darah, berat_badan, tinggi_badan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $id_user, 
            $id_dokter, 
            $diagnosa, 
            $tanggal, 
            $pengobatan, 
            $keluhan, 
            $tekanan_darah, 
            $berat_badan, 
            $tinggi_badan
        ]);
        
        if ($result && $stmt->rowCount() > 0) {
            $success = "Data rekam medis berhasil ditambahkan dengan ID: " . $pdo->lastInsertId();
        } else {
            $error = "Gagal menyimpan data rekam medis! Row affected: " . $stmt->rowCount();
        }
        
    } catch(PDOException $e) {
        $error = "Error Database: " . $e->getMessage();
        error_log("Database Error: " . $e->getMessage());
    }
}

function updateRekamMedis() {
    global $pdo, $success, $error;
    
    if (empty($_POST['id_rkmmed']) || empty($_POST['id_user']) || empty($_POST['id_dokter']) || 
        empty($_POST['diagnosa']) || empty($_POST['tanggal']) || empty($_POST['pengobatan']) || 
        empty($_POST['keluhan'])) {
        $error = "Semua field yang bertanda * wajib diisi!";
        return;
    }
    
    $id_rkmmed = intval($_POST['id_rkmmed']);
    $id_user = intval($_POST['id_user']);
    $id_dokter = intval($_POST['id_dokter']);
    $diagnosa = sanitize($_POST['diagnosa']);
    $tanggal = sanitize($_POST['tanggal']);
    $pengobatan = sanitize($_POST['pengobatan']);
    $keluhan = sanitize($_POST['keluhan']);
    $tekanan_darah = !empty($_POST['tekanan_darah']) ? sanitize($_POST['tekanan_darah']) : null;
    $berat_badan = !empty($_POST['berat_badan']) ? floatval($_POST['berat_badan']) : null;
    $tinggi_badan = !empty($_POST['tinggi_badan']) ? floatval($_POST['tinggi_badan']) : null;
    
    if (empty($diagnosa) || empty($pengobatan) || empty($keluhan)) {
        $error = "Field Diagnosa, Pengobatan, dan Keluhan tidak boleh kosong!";
        return;
    }
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $error = "Format tanggal harus YYYY-MM-DD!";
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE rekam_medis SET ID_user=?, ID_dokter=?, Diagnosa=?, Tanggal=?, Pengobatan=?, keluhan=?, tekanan_darah=?, berat_badan=?, tinggi_badan=? WHERE ID_rkmmed=?");
        $result = $stmt->execute([$id_user, $id_dokter, $diagnosa, $tanggal, $pengobatan, $keluhan, $tekanan_darah, $berat_badan, $tinggi_badan, $id_rkmmed]);
        
        if ($result && $stmt->rowCount() > 0) {
            $success = "Data rekam medis berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate data rekam medis atau tidak ada perubahan data!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function deleteRekamMedis() {
    global $pdo, $success, $error;
    
    if (empty($_POST['id_rkmmed'])) {
        $error = "ID rekam medis tidak valid!";
        return;
    }
    
    $id_rkmmed = intval($_POST['id_rkmmed']);
    
    try {
        $stmt_resep = $pdo->prepare("DELETE FROM resep_obat WHERE ID_rkmmed=?");
        $stmt_resep->execute([$id_rkmmed]);
        
        
        $stmt = $pdo->prepare("DELETE FROM rekam_medis WHERE ID_rkmmed=?");
        $result = $stmt->execute([$id_rkmmed]);
        
        if ($result && $stmt->rowCount() > 0) {
            $success = "Data rekam medis berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data rekam medis atau data tidak ditemukan!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function addResepObat() {
    global $pdo, $success, $error;
    
    if (empty($_POST['id_rkmmed']) || empty($_POST['id_obat']) || empty($_POST['jumlah_obat']) || 
        empty($_POST['dosis']) || empty($_POST['cara_pakai'])) {
        $error = "Semua field resep obat yang bertanda * wajib diisi!";
        return;
    }
    
    $id_rkmmed = intval($_POST['id_rkmmed']);
    $id_obat = intval($_POST['id_obat']);
    $jumlah_obat = intval($_POST['jumlah_obat']);
    $dosis = sanitize($_POST['dosis']);
    $cara_pakai = sanitize($_POST['cara_pakai']);
    $catatan = !empty($_POST['catatan']) ? sanitize($_POST['catatan']) : null;
    
    if ($jumlah_obat <= 0) {
        $error = "Jumlah obat harus lebih dari 0!";
        return;
    }
    
    try {
        $check_stok = $pdo->prepare("SELECT stok_obat FROM obat WHERE ID_obat = ?");
        $check_stok->execute([$id_obat]);
        $obat_data = $check_stok->fetch();
        
        if (!$obat_data) {
            $error = "Obat tidak ditemukan!";
            return;
        }
        
        if ($obat_data['stok_obat'] < $jumlah_obat) {
            $error = "Stok obat tidak mencukupi! Stok tersedia: " . $obat_data['stok_obat'];
            return;
        }
        
        $stmt = $pdo->prepare("INSERT INTO resep_obat (ID_rkmmed, ID_obat, jumlah_obat, dosis, cara_pakai, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$id_rkmmed, $id_obat, $jumlah_obat, $dosis, $cara_pakai, $catatan]);
        
        if ($result) {
            $update_stok = $pdo->prepare("UPDATE obat SET stok_obat = stok_obat - ? WHERE ID_obat = ?");
            $update_stok->execute([$jumlah_obat, $id_obat]);
            
            $success = "Resep obat berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan resep obat!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$pasien = [];
$dokter = [];
$obat = [];
$rekam_medis = [];
$edit_data = null;

try {
    if (isset($pdo) && $pdo) {
        $pasien_query = "SELECT ID_user, username FROM tb_login WHERE pengguna != 'admin' AND ID_user IS NOT NULL ORDER BY username";
        $pasien_stmt = $pdo->query($pasien_query);
        if ($pasien_stmt) {
            $pasien = $pasien_stmt->fetchAll();
        }
        
        $dokter_query = "SELECT ID_dokter, Username, Pengguna_role, Spesialisasi FROM dokter WHERE ID_dokter IS NOT NULL ORDER BY Username";
        $dokter_stmt = $pdo->query($dokter_query);
        if ($dokter_stmt) {
            $dokter = $dokter_stmt->fetchAll();
        }
        
        $obat_query = "SELECT ID_obat, nama_obat, jenis_obat, harga_obat, stok_obat FROM obat WHERE stok_obat > 0 AND ID_obat IS NOT NULL ORDER BY nama_obat";
        $obat_stmt = $pdo->query($obat_query);
        if ($obat_stmt) {
            $obat = $obat_stmt->fetchAll();
        }

        $rekam_medis_query = "
            SELECT rm.*, 
                   tl.username as nama_pasien,
                   d.Username as nama_dokter,
                   d.Spesialisasi as spesialisasi_dokter
            FROM rekam_medis rm
            LEFT JOIN tb_login tl ON rm.ID_user = tl.ID_user
            LEFT JOIN dokter d ON rm.ID_dokter = d.ID_dokter
            WHERE rm.ID_rkmmed IS NOT NULL
            ORDER BY rm.Tanggal DESC, rm.ID_rkmmed DESC
        ";
        $rekam_medis_stmt = $pdo->query($rekam_medis_query);
        if ($rekam_medis_stmt) {
            $rekam_medis = $rekam_medis_stmt->fetchAll();
        }

        if (isset($_GET['edit']) && !empty($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $edit_stmt = $pdo->prepare("SELECT * FROM rekam_medis WHERE ID_rkmmed = ?");
            $edit_stmt->execute([$edit_id]);
            $edit_data = $edit_stmt->fetch();
        }
        
    } else {
        $error = "Koneksi database tidak tersedia. Pastikan file koneksi.php sudah benar.";
    }
} catch(PDOException $e) {
    $error = "Error mengambil data: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
} catch(Exception $e) {
    $error = "Error: " . $e->getMessage();
}

if (!empty($error)) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
    echo "<strong>Error:</strong> " . $error;
    echo "</div>";
}

if (!empty($success)) {
    echo "<div style='background: #e8f5e8; color: #2e7d32; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
    echo "<strong>Success:</strong> " . $success;
    echo "</div>";
}



?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis - DocTo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-section {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a5f 100%);
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
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a5f 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5aa0 100%);
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-clipboard-list me-2"></i>Rekam Medis</h1>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-home me-1"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['debug'])): ?>
            <div class="alert alert-info">
                <strong>Debug Info:</strong><br>
                Jumlah Pasien: <?php echo count($pasien); ?><br>
                Jumlah Dokter: <?php echo count($dokter); ?><br>
                Jumlah Obat: <?php echo count($obat); ?><br>
                Jumlah Rekam Medis: <?php echo count($rekam_medis); ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    <?php echo $edit_data ? 'Edit Rekam Medis' : 'Tambah Rekam Medis Baru'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_rkmmed" value="<?php echo $edit_data['ID_rkmmed']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pasien *</label>
                            <select name="id_user" class="form-select" required>
                                <option value="">Pilih Pasien</option>
                                <?php foreach ($pasien as $p): ?>
                                    <option value="<?php echo $p['ID_user']; ?>" <?php echo ($edit_data && $edit_data['ID_user'] == $p['ID_user']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dokter *</label>
                            <select name="id_dokter" class="form-select" required>
                                <option value="">Pilih Dokter</option>
                                <?php foreach ($dokter as $d): ?>
                                    <option value="<?php echo $d['ID_dokter']; ?>" <?php echo ($edit_data && $edit_data['ID_dokter'] == $d['ID_dokter']) ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($d['Username']); ?> - <?php echo htmlspecialchars($d['Spesialisasi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Pemeriksaan *</label>
                            <input type="date" name="tanggal" class="form-control" value="<?php echo $edit_data ? $edit_data['Tanggal'] : date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tekanan Darah</label>
                            <input type="text" name="tekanan_darah" class="form-control" placeholder="120/80 mmHg" value="<?php echo $edit_data ? htmlspecialchars($edit_data['tekanan_darah']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Berat Badan (kg)</label>
                            <input type="number" step="0.1" name="berat_badan" class="form-control" value="<?php echo $edit_data ? $edit_data['berat_badan'] : ''; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tinggi Badan (cm)</label>
                            <input type="number" step="0.1" name="tinggi_badan" class="form-control" value="<?php echo $edit_data ? $edit_data['tinggi_badan'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keluhan Pasien *</label>
                        <textarea name="keluhan" class="form-control" rows="3" required><?php echo $edit_data ? htmlspecialchars($edit_data['keluhan']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Diagnosa *</label>
                        <textarea name="diagnosa" class="form-control" rows="3" required><?php echo $edit_data ? htmlspecialchars($edit_data['Diagnosa']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pengobatan/Tindakan *</label>
                        <textarea name="pengobatan" class="form-control" rows="3" required><?php echo $edit_data ? htmlspecialchars($edit_data['Pengobatan']) : ''; ?></textarea>
                    </div>
                    
                    <div class="text-end">
                        <?php if ($edit_data): ?>
                            <a href="rekam_medis.php" class="btn btn-secondary me-2">Batal</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Rekam Medis</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Keluhan</th>
                                <th>Diagnosa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rekam_medis)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data rekam medis</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($rekam_medis as $rm): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo formatTanggalIndo($rm['Tanggal']); ?></td>
                                        <td><?php echo htmlspecialchars($rm['nama_pasien']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($rm['nama_dokter']); ?><br><small class="text-muted"><?php echo htmlspecialchars($rm['spesialisasi_dokter']); ?></small></td>
                                        <td><?php echo substr(htmlspecialchars($rm['keluhan']), 0, 50) . (strlen($rm['keluhan']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo substr(htmlspecialchars($rm['Diagnosa']), 0, 50) . (strlen($rm['Diagnosa']) > 50 ? '...' : ''); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $rm['ID_rkmmed']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="?edit=<?php echo $rm['ID_rkmmed']; ?>" class="btn btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resepModal<?php echo $rm['ID_rkmmed']; ?>">
                                                    <i class="fas fa-prescription-bottle-alt"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id_rkmmed" value="<?php echo $rm['ID_rkmmed']; ?>">
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

    <?php foreach ($rekam_medis as $rm): ?>
        <div class="modal fade" id="detailModal<?php echo $rm['ID_rkmmed']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Rekam Medis</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Pasien:</strong> <?php echo htmlspecialchars($rm['nama_pasien']); ?><br>
                                <strong>Dokter:</strong> Dr. <?php echo htmlspecialchars($rm['nama_dokter']); ?><br>
                                <strong>Spesialisasi:</strong> <?php echo htmlspecialchars($rm['spesialisasi_dokter']); ?><br>
                                <strong>Tanggal:</strong> <?php echo formatTanggalIndo($rm['Tanggal']); ?><br>
                            </div>
                            <div class="col-md-6">
                                <strong>Tekanan Darah:</strong> <?php echo $rm['tekanan_darah'] ? htmlspecialchars($rm['tekanan_darah']) : '-'; ?><br>
                                <strong>Berat Badan:</strong> <?php echo $rm['berat_badan'] ? htmlspecialchars($rm['berat_badan']) . ' kg' : '-'; ?><br>
                                <strong>Tinggi Badan:</strong> <?php echo $rm['tinggi_badan'] ? htmlspecialchars($rm['tinggi_badan']) . ' cm' : '-'; ?><br>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <strong>Keluhan:</strong><br>
                            <?php echo nl2br(htmlspecialchars($rm['keluhan'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Diagnosa:</strong><br>
                            <?php echo nl2br(htmlspecialchars($rm['Diagnosa'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Pengobatan:</strong><br>
                            <?php echo nl2br(htmlspecialchars($rm['Pengobatan'])); ?>
                        </div>
                        
                        <?php
                        try {
                            $resep = $pdo->prepare("
                                SELECT ro.*, o.nama_obat, o.jenis_obat 
                                FROM resep_obat ro 
                                LEFT JOIN obat o ON ro.ID_obat = o.ID_obat 
                                WHERE ro.ID_rkmmed = ?
                            ");
                            $resep->execute([$rm['ID_rkmmed']]);
                            $resep_data = $resep->fetchAll();
                        } catch(PDOException $e) {
                            $resep_data = [];
                        }
                        ?>
                        
                        <?php if (!empty($resep_data)): ?>
                            <hr>
                            <strong>Resep Obat:</strong>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Obat</th>
                                            <th>Jumlah</th>
                                            <th>Dosis</th>
                                            <th>Cara Pakai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resep_data as $r): ?>
                                            <tr>
                                                <td><?php echo $r['nama_obat']; ?> (<?php echo $r['jenis_obat']; ?>)</td>
                                                <td><?php echo $r['jumlah_obat']; ?></td>
                                                <td><?php echo $r['dosis']; ?></td>
                                                <td><?php echo $r['cara_pakai']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="resepModal<?php echo $rm['ID_rkmmed']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Tambah Resep Obat</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_resep">
                            <input type="hidden" name="id_rkmmed" value="<?php echo $rm['ID_rkmmed']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Jumlah Obat *</label>
                                <input type="number" name="jumlah_obat" class="form-control" min="1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dosis *</label>
                                <input type="text" name="dosis" class="form-control" placeholder="1 tablet 3x sehari" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Cara Pakai *</label>
                                <textarea name="cara_pakai" class="form-control" rows="2" placeholder="Diminum sesudah makan" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Tambah Resep</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 