<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_resep') {
    $id_rkmmed = sanitize($_POST['id_rkmmed']);
    $id_obat = sanitize($_POST['id_obat']);
    $jumlah_obat = sanitize($_POST['jumlah_obat']);
    $dosis = sanitize($_POST['dosis']);
    $instruksi = isset($_POST['instruksi']) ? sanitize($_POST['instruksi']) : '';
    
    if (empty($id_rkmmed) || empty($id_obat) || empty($jumlah_obat) || empty($dosis)) {
        $error = "Semua field yang wajib harus diisi!";
    } elseif (!is_numeric($jumlah_obat) || $jumlah_obat <= 0) {
        $error = "Jumlah obat harus berupa angka positif!";
    } else {
        try {
            $check_rm = $pdo->prepare("SELECT ID_rkmmed FROM rekam_medis WHERE ID_rkmmed = ?");
            $check_rm->execute([$id_rkmmed]);
            if (!$check_rm->fetch()) {
                throw new Exception("Rekam medis tidak ditemukan!");
            }
            
            $check_obat = $pdo->prepare("SELECT ID_obat FROM obat WHERE ID_obat = ?");
            $check_obat->execute([$id_obat]);
            if (!$check_obat->fetch()) {
                throw new Exception("Obat tidak ditemukan!");
            }
            
            $stmt = $pdo->prepare("INSERT INTO resep_obat (ID_rkmmed, ID_obat, jumlah_obat, dosis, instruksi, tanggal_resep) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$id_rkmmed, $id_obat, $jumlah_obat, $dosis, $instruksi]);
            
            if ($result) {
                $success = "Resep obat berhasil ditambahkan!";
                header("Location: resep_obat.php?success=1");
                exit();
            } else {
                throw new Exception("Gagal menyimpan resep obat!");
            }
            
        } catch(PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

try {
    $rekam_medis = $pdo->query("
        SELECT rm.*, tl.username as nama_pasien, d.Username as nama_dokter
        FROM rekam_medis rm
        LEFT JOIN tb_login tl ON rm.ID_user = tl.ID_user
        LEFT JOIN dokter d ON rm.ID_dokter = d.ID_dokter
        ORDER BY rm.Tanggal DESC
    ")->fetchAll();
} catch(PDOException $e) {
    $rekam_medis = [];
    $error = "Error mengambil data rekam medis: " . $e->getMessage();
}

try {
    $obat = $pdo->query("SELECT * FROM obat ORDER BY nama_obat")->fetchAll();
} catch(PDOException $e) {
    $obat = [];
    $error = "Error mengambil data obat: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Resep Obat - DocTo</title>
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
            display: none;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-plus-circle me-2"></i>Tambah Resep Obat</h1>
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
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['debug'])): ?>
            <div class="alert alert-info">
                <strong>Debug Info:</strong><br>
                Rekam Medis Count: <?php echo count($rekam_medis); ?><br>
                Obat Count: <?php echo count($obat); ?><br>
                PDO Connection: <?php echo isset($pdo) ? 'Connected' : 'Not Connected'; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2"></i>Form Tambah Resep Obat</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="resepForm">
                    <input type="hidden" name="action" value="create_resep">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_rkmmed" class="form-label">Rekam Medis <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_rkmmed" name="id_rkmmed" required>
                                    <option value="">Pilih Rekam Medis</option>
                                    <?php if (!empty($rekam_medis)): ?>
                                        <?php foreach ($rekam_medis as $rm): ?>
                                            <option value="<?php echo htmlspecialchars($rm['ID_rkmmed']); ?>">
                                                <?php echo htmlspecialchars($rm['nama_pasien'] ?? 'Unknown'); ?> - 
                                                <?php echo htmlspecialchars($rm['nama_dokter'] ?? 'Unknown'); ?> - 
                                                <?php echo date('d/m/Y', strtotime($rm['Tanggal'])); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada data rekam medis</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_obat" class="form-label">Obat <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_obat" name="id_obat" required onchange="showObatInfo()">
                                    <option value="">Pilih Obat</option>
                                    <?php if (!empty($obat)): ?>
                                        <?php foreach ($obat as $ob): ?>
                                            <option value="<?php echo htmlspecialchars($ob['ID_obat']); ?>" 
                                                    data-nama="<?php echo htmlspecialchars($ob['nama_obat']); ?>"
                                                    data-jenis="<?php echo htmlspecialchars($ob['jenis_obat'] ?? ''); ?>"
                                                    data-harga="<?php echo $ob['harga_obat']; ?>">
                                                <?php echo htmlspecialchars($ob['nama_obat']); ?> - <?php echo formatRupiah($ob['harga_obat']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada data obat</option>
                                    <?php endif; ?>
                                </select>
                                
                                <div id="obat-info" class="obat-info">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Nama Obat:</strong>
                                            <p id="info-nama" class="mb-1"></p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Jenis:</strong>
                                            <p id="info-jenis" class="mb-1"></p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Harga:</strong>
                                            <p id="info-harga" class="mb-1 text-success"></p>
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
                                       min="1" required onchange="calculateTotal()">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dosis" class="form-label">Dosis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dosis" name="dosis" 
                                       placeholder="Contoh: 3x1 sehari" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="instruksi" class="form-label">Instruksi Penggunaan</label>
                        <textarea class="form-control" id="instruksi" name="instruksi" rows="3" 
                                  placeholder="Contoh: Diminum setelah makan, jangan dikunyah"></textarea>
                    </div>

                    <div id="total-display" class="alert alert-info" style="display: none;">
                        <strong>Total Harga: </strong><span id="total-harga" class="text-success fs-5"></span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="resep_obat.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Resep
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
            } else {
                document.getElementById('total-display').style.display = 'none';
            }
        }

        function formatRupiah(angka) {
            return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
        }

        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        document.getElementById('resepForm').addEventListener('submit', function(e) {
            const requiredFields = ['id_rkmmed', 'id_obat', 'jumlah_obat', 'dosis'];
            let isValid = true;
            
            requiredFields.forEach(function(fieldId) {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
            }
        });
    </script>
</body>
</html>