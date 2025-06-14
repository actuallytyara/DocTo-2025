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

$stmt = $pdo->query("SELECT ID_user, username FROM tb_login WHERE pengguna = 'user' ORDER BY username");
$pasien_list = $stmt->fetchAll();

$stmt = $pdo->query("SELECT ID_dokter, Username FROM dokter ORDER BY Username");
$dokter_list = $stmt->fetchAll();

if ($_POST) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO rekam_medis (
                ID_user, ID_dokter, Diagnosa, keluhan, 
                tekanan_darah, berat_badan, tinggi_badan, 
                Tanggal, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_POST['id_pasien'],
            $_POST['id_dokter'],
            $_POST['diagnosa'],
            $_POST['keluhan'],
            $_POST['tekanan_darah'],
            $_POST['berat_badan'],
            $_POST['tinggi_badan'],
            $_POST['tanggal']
        ]);
        
        $success_message = "Rekam medis berhasil ditambahkan!";
        $new_id = $pdo->lastInsertId();
        
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rekam Medis</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #37705D, #4a8a6f);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            opacity: 0.9;
        }

        .form-container {
            padding: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #37705D;
            background: white;
            box-shadow: 0 0 0 3px rgba(55, 112, 93, 0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .btn {
            background: linear-gradient(135deg, #37705D, #4a8a6f);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(55, 112, 93, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #37705D;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #2d5a4a;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .vitals-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }

        .required {
            color: #dc3545;
        }

        .input-group {
            position: relative;
        }

        .input-group .fas {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 1;
        }

        .input-group .form-control {
            padding-left: 45px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plus-circle"></i> Tambah Rekam Medis</h1>
            <p>Buat rekam medis baru untuk pasien</p>
        </div>

        <div class="form-container">
            <a href="rekammedis.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                    <br><small>ID Rekam Medis: RM<?php echo str_pad($new_id, 3, '0', STR_PAD_LEFT); ?></small>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_pasien">Pasien <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <select name="id_pasien" id="id_pasien" class="form-control" required>
                                <option value="">Pilih Pasien</option>
                                <?php foreach($pasien_list as $pasien): ?>
                                    <option value="<?php echo $pasien['ID_user']; ?>" 
                                            <?php echo (isset($_POST['id_pasien']) && $_POST['id_pasien'] == $pasien['ID_user']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pasien['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_dokter">Dokter <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-user-md"></i>
                            <select name="id_dokter" id="id_dokter" class="form-control" required>
                                <option value="">Pilih Dokter</option>
                                <?php foreach($dokter_list as $dokter): ?>
                                    <option value="<?php echo $dokter['ID_dokter']; ?>"
                                            <?php echo (isset($_POST['id_dokter']) && $_POST['id_dokter'] == $dokter['ID_dokter']) ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($dokter['Username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal Pemeriksaan <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" 
                               value="<?php echo isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="keluhan">Keluhan Pasien <span class="required">*</span></label>
                    <textarea name="keluhan" id="keluhan" class="form-control" 
                              placeholder="Deskripsikan keluhan yang dialami pasien..." required><?php echo isset($_POST['keluhan']) ? htmlspecialchars($_POST['keluhan']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="diagnosa">Diagnosis <span class="required">*</span></label>
                    <textarea name="diagnosa" id="diagnosa" class="form-control" 
                              placeholder="Hasil diagnosis dokter..." required><?php echo isset($_POST['diagnosa']) ? htmlspecialchars($_POST['diagnosa']) : ''; ?></textarea>
                </div>

                <h3 style="margin: 2rem 0 1rem 0; color: #37705D; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem;">
                    <i class="fas fa-heartbeat"></i> Tanda Vital
                </h3>

                <div class="vitals-grid">
                    <div class="form-group">
                        <label for="tekanan_darah">Tekanan Darah</label>
                        <div class="input-group">
                            <i class="fas fa-tint"></i>
                            <input type="text" name="tekanan_darah" id="tekanan_darah" class="form-control" 
                                   placeholder="120/80" value="<?php echo isset($_POST['tekanan_darah']) ? htmlspecialchars($_POST['tekanan_darah']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="berat_badan">Berat Badan (kg)</label>
                        <div class="input-group">
                            <i class="fas fa-weight"></i>
                            <input type="number" name="berat_badan" id="berat_badan" class="form-control" 
                                   placeholder="70" step="0.1" value="<?php echo isset($_POST['berat_badan']) ? $_POST['berat_badan'] : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tinggi_badan">Tinggi Badan (cm)</label>
                        <div class="input-group">
                            <i class="fas fa-ruler-vertical"></i>
                            <input type="number" name="tinggi_badan" id="tinggi_badan" class="form-control" 
                                   placeholder="170" value="<?php echo isset($_POST['tinggi_badan']) ? $_POST['tinggi_badan'] : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="rekammedis.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Simpan Rekam Medis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('id_pasien').focus();

        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e1e5e9';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
            }
        });

        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = '#dc3545';
                } else {
                    this.style.borderColor = '#e1e5e9';
                }
            });
        });
    </script>
</body>
</html>