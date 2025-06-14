<?php
include_once 'koneksi.php';
session_start();

$id_resep = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_resep) {
    header("Location: resep_obat.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_resep') {
    $id_rkmmed = sanitize($_POST['id_rkmmed']);
    $id_obat = sanitize($_POST['id_obat']);
    $jumlah_obat = sanitize($_POST['jumlah_obat']);
    $dosis = sanitize($_POST['dosis']);
    $instruksi = sanitize($_POST['instruksi']);
    
    try {
        $stmt = $pdo->prepare("UPDATE resep_obat SET ID_rkmmed=?, ID_obat=?, jumlah_obat=?, dosis=?, instruksi=?, updated_at=NOW() WHERE ID_resep=?");
        $stmt->execute([$id_rkmmed, $id_obat, $jumlah_obat, $dosis, $instruksi, $id_resep]);
        
        $success = "Resep obat berhasil diperbarui!";
        header("Location: resep_obat.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("
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
    WHERE ro.ID_resep = ?
");
$stmt->execute([$id_resep]);
$current_resep = $stmt->fetch();

if (!$current_resep) {
    header("Location: resep_obat.php");
    exit();
}

$rekam_medis = $pdo->query("
    SELECT rm.*, tl.username as nama_pasien, d.Username as nama_dokter
    FROM rekam_medis rm
    LEFT JOIN tb_login tl ON rm.ID_user = tl.ID_user
    LEFT JOIN dokter d ON rm.ID_dokter = d.ID_dokter
    ORDER BY rm.Tanggal DESC
")->fetchAll();

$obat = $pdo->query("SELECT * FROM obat ORDER BY nama_obat")->fetchAll();

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resep Obat - DocTo</title>
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
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        .obat-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .current-info {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-edit me-2"></i>Edit Resep Obat</h1>
                <div>
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

        <div class="current-info">
            <h6><i class="fas fa-info-circle me-2"></i>Informasi Resep Saat Ini</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>Pasien:</strong><br>
                    <?php echo htmlspecialchars($current_resep['nama_pasien']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Dokter:</strong><br>
                    <?php echo htmlspecialchars($current_resep['nama_dokter']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Obat:</strong><br>
                    <?php echo htmlspecialchars($current_resep['nama_obat']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Tanggal:</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($current_resep['created_at'])); ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2"></i>Form Edit Resep Obat</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="resepForm">
                    <input type="hidden" name="action" value="update_resep">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_rkmmed" class="form-label">Rekam Medis <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_rkmmed" name="id_rkmmed" required>
                                    <option value="">Pilih Rekam Medis</option>
                                    <?php foreach ($rekam_medis as $rm): ?>
                                        <option value="<?php echo $rm['ID_rkmmed']; ?>" 
                                                <?php echo ($rm['ID_rkmmed'] == $current_resep['ID_rkmmed']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rm['nama_pasien']); ?> - 
                                            <?php echo htmlspecialchars($rm['nama_dokter']); ?> - 
                                            <?php echo date('d/m/Y', strtotime($rm['Tanggal'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_obat" class="form-label">Obat <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_obat" name="id_obat" required onchange="showObatInfo()">
                                    <option value="">Pilih Obat</option>
                                    <?php foreach ($obat as $ob): ?>
                                        <option value="<?php echo $ob['ID_obat']; ?>" 
                                                data-nama="<?php echo htmlspecialchars($ob['nama_obat']); ?>"
                                                data-jenis="<?php echo htmlspecialchars($ob['jenis_obat']); ?>"
                                                data-harga="<?php echo $ob['harga_obat']; ?>"
                                                <?php echo ($ob['ID_obat'] == $current_resep['ID_obat']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ob['nama_obat']); ?> - <?php echo formatRupiah($ob['harga_obat']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <div id="obat-info" class="obat-info">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Nama Obat:</strong>
                                            <p id="info-nama" class="mb-1"><?php echo htmlspecialchars($current_resep['nama_obat']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Jenis:</strong>
                                            <p id="info-jenis" class="mb-1"><?php echo htmlspecialchars($current_resep['jenis_obat']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Harga:</strong>
                                            <p id="info-harga" class="mb-1 text-success"><?php echo formatRupiah($current_resep['harga_obat']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jumlah_obat" class="form-label">Jumlah Obat <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah_obat" name="jumlah_obat" 
                                       min="1" value="<?php echo $current_resep['jumlah_obat']; ?>" required onchange="calculateTotal()">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dosis" class="form-label">Dosis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dosis" name="dosis" 
                                       value="<?php echo htmlspecialchars($current_resep['dosis']); ?>"
                                       placeholder="Contoh: 3x1 sehari" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="instruksi" class="form-label">Instruksi Penggunaan</label>
                        <textarea class="form-control" id="instruksi" name="instruksi" rows="3" 
                                  placeholder="Contoh: Diminum setelah makan, jangan dikunyah"><?php echo htmlspecialchars($current_resep['instruksi']); ?></textarea>
                    </div>

                    <div id="total-display" class="alert alert-info">
                        <strong>Total Harga: </strong><span id="total-harga" class="text-success fs-5">
                            <?php echo formatRupiah($current_resep['jumlah_obat'] * $current_resep['harga_obat']); ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="resep_obat.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Resep
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showObatInfo() {
            const select = document.getElementById('id_obat');
            const selectedOption = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('obat-info');
            
            if (selectedOption.value) {
                document.getElementById('info-nama').textContent = selectedOption.dataset.nama;
                document.getElementById('info-jenis').textContent = selectedOption.dataset.jenis;
                document.getElementById('info-harga').textContent = formatRupiah(selectedOption.dataset.harga);
                infoDiv.style.display = 'block';
                calculateTotal();
            } else {
                infoDiv.style.display = 'none';
                document.getElementById('total-display').style.display = 'none';
            }
        }

        function calculateTotal() {
            const select = document.getElementById('id_obat');
            const selectedOption = select.options[select.selectedIndex];
            const jumlah = document.getElementById('jumlah_obat').value;
            
            if (selectedOption.value && jumlah) {
                const harga = parseInt(selectedOption.dataset.harga);
                const total = harga * parseInt(jumlah);
                document.getElementById('total-harga').textContent = formatRupiah(total);
                document.getElementById('total-display').style.display = 'block';
            }
        }

        function formatRupiah(angka) {
            return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', function() {
            showObatInfo();
        });

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