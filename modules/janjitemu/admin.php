<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['pengguna'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

function getAllJanjiTemu($conn) {
    $sql = "SELECT jt.*, 
                   d.Username as nama_dokter, 
                   d.Spesialisasi, 
                   d.Nomor_Telepon as telepon_dokter,
                   u.username as nama_user,
                   u.email as email_user
            FROM janji_temu jt 
            LEFT JOIN dokter d ON jt.ID_dokter = d.ID_dokter 
            LEFT JOIN tb_login u ON jt.ID_user = u.ID_user 
            ORDER BY jt.created_at DESC";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getJanjiTemuById($conn, $id) {
    $sql = "SELECT jt.*, 
                   d.Username as nama_dokter, 
                   d.Spesialisasi,
                   d.Nomor_Telepon as telepon_dokter,
                   u.username as nama_user,
                   u.email as email_user
            FROM janji_temu jt 
            LEFT JOIN dokter d ON jt.ID_dokter = d.ID_dokter 
            LEFT JOIN tb_login u ON jt.ID_user = u.ID_user 
            WHERE jt.ID_janji_temu = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getAllDokter($conn) {
    $sql = "SELECT * FROM dokter ORDER BY Username";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAllUsers($conn) {
    $sql = "SELECT * FROM tb_login WHERE pengguna = 'user' ORDER BY username";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getStatusDisplay($status) {
    switch(strtolower($status)) {
        case 'pending':
        case '':
        case null:
            return 'Menunggu';
        case 'dikonfirmasi':
            return 'Dikonfirmasi';
        case 'selesai':
            return 'Selesai';
        case 'dibatalkan':
            return 'Dibatalkan';
        default:
            return ucfirst($status);
    }
}

function getStatusClass($status) {
    switch(strtolower($status)) {
        case 'pending':
        case '':
        case null:
            return 'pending';
        case 'dikonfirmasi':
            return 'dikonfirmasi';
        case 'selesai':
            return 'selesai';
        case 'dibatalkan':
            return 'dibatalkan';
        default:
            return 'pending';
    }
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $id_user = $_POST['id_user'];
                $id_dokter = $_POST['id_dokter'];
                $tanggal = $_POST['tanggal'];
                $waktu = $_POST['waktu'];
                $keluhan = $_POST['keluhan'];
                $catatan = $_POST['catatan'] ?? '';
                $biaya = $_POST['biaya'] ?? 0;
                $status = $_POST['status'] ?? 'pending';
                
                $sql = "INSERT INTO janji_temu (ID_user, ID_dokter, Tanggal, Waktu, keluhan, catatan, biaya, Status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iissssds", $id_user, $id_dokter, $tanggal, $waktu, $keluhan, $catatan, $biaya, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Janji temu berhasil ditambahkan!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "error";
                }
                break;
                
            case 'update':
                $id_janji = $_POST['id_janji'];
                $id_user = $_POST['id_user'];
                $id_dokter = $_POST['id_dokter'];
                $tanggal = $_POST['tanggal'];
                $waktu = $_POST['waktu'];
                $keluhan = $_POST['keluhan'];
                $catatan = $_POST['catatan'] ?? '';
                $biaya = $_POST['biaya'] ?? 0;
                $status = $_POST['status'];
                
                $sql = "UPDATE janji_temu SET 
                        ID_user = ?, ID_dokter = ?, Tanggal = ?, Waktu = ?, 
                        keluhan = ?, catatan = ?, biaya = ?, Status = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                        WHERE ID_janji_temu = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iissssdsi", $id_user, $id_dokter, $tanggal, $waktu, $keluhan, $catatan, $biaya, $status, $id_janji);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Janji temu berhasil diperbarui!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "error";
                }
                break;
                
            case 'delete':
                $id_janji = $_POST['id_janji'];
                $sql = "DELETE FROM janji_temu WHERE ID_janji_temu = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id_janji);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Janji temu berhasil dihapus!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "error";
                }
                break;
                
            case 'update_status':
                $id_janji = $_POST['id_janji'];
                $status = $_POST['status'];
                
                $sql = "UPDATE janji_temu SET Status = ?, updated_at = CURRENT_TIMESTAMP WHERE ID_janji_temu = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $id_janji);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Status janji temu berhasil diperbarui!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "error";
                }
                break;
        }
        
        if ($message) {
            $_SESSION['message'] = $message;
            $_SESSION['messageType'] = $messageType;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

$janjiTemuList = getAllJanjiTemu($conn);
$dokterList = getAllDokter($conn);
$userList = getAllUsers($conn);

$editData = null;
$detailData = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editData = getJanjiTemuById($conn, $_GET['edit']);
}
if (isset($_GET['detail']) && is_numeric($_GET['detail'])) {
    $detailData = getJanjiTemuById($conn, $_GET['detail']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Janji Temu - Admin</title>
    <link rel="stylesheet" href="style_admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <i class="fas fa-stethoscope"></i>
                <h1>CRUD Janji Temu (Admin)</h1>
            </div>
            <nav class="nav">
                <a href="../articles/artikel.php"><i class="fas fa-newspaper"></i> Artikel</a>
                <a href="../pemesanan/katalog.php"><i class="fas fa-pills"></i> Katalog Obat</a>
                <a href="#" class="active"><i class="fas fa-calendar-check"></i> Janji Temu</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </header>

        <main class="main-content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="action-bar">
                <h2><i class="fas fa-calendar-check"></i> Manajemen Janji Temu</h2>
                <button class="btn btn-primary" onclick="openModal('createModal')">
                    <i class="fas fa-plus"></i> Tambah Janji Temu
                </button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($janjiTemuList, function($j) { return strtolower($j['Status']) === 'pending' || empty($j['Status']); })) ?></h3>
                        <p>Menunggu</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon dikonfirmasi">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($janjiTemuList, function($j) { return strtolower($j['Status']) === 'dikonfirmasi'; })) ?></h3>
                        <p>Dikonfirmasi</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon selesai">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($janjiTemuList, function($j) { return strtolower($j['Status']) === 'selesai'; })) ?></h3>
                        <p>Selesai</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon dibatalkan">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($janjiTemuList, function($j) { return strtolower($j['Status']) === 'dibatalkan'; })) ?></h3>
                        <p>Dibatalkan</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari janji temu..." onkeyup="searchTable()">
                    </div>
                    <div class="filter-bar">
                        <select id="statusFilter" onchange="filterTable()">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="dikonfirmasi">Dikonfirmasi</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($janjiTemuList)): ?>
                    <div class="no-data">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Tidak ada data janji temu</h3>
                        <p>Silakan tambah janji temu baru untuk memulai</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="data-table" id="janjiTemuTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pasien</th>
                                    <th>Dokter</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Biaya</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($janjiTemuList as $janji): ?>
                                    <?php 
                                    $statusClass = getStatusClass($janji['Status']);
                                    $statusDisplay = getStatusDisplay($janji['Status']);
                                    ?>
                                    <tr data-status="<?= $statusClass ?>">
                                        <td><?= $janji['ID_janji_temu'] ?></td>
                                        <td>
                                            <div class="user-info">
                                                <i class="fas fa-user"></i>
                                                <span><?= htmlspecialchars($janji['nama_user'] ?? 'Unknown') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doctor-info">
                                                <i class="fas fa-user-md"></i>
                                                <div>
                                                    <span class="doctor-name">Dr. <?= htmlspecialchars($janji['nama_dokter'] ?? 'Unknown') ?></span>
                                                    <small><?= htmlspecialchars($janji['Spesialisasi'] ?? '') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d/m/Y', strtotime($janji['Tanggal'])) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-clock"></i>
                                            <?= date('H:i', strtotime($janji['Waktu'])) ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $statusClass ?>">
                                                <?= $statusDisplay ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="biaya">
                                                Rp <?= number_format($janji['biaya'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-detail" onclick="showDetail(<?= $janji['ID_janji_temu'] ?>)" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-action btn-edit" onclick="editJanjiTemu(<?= $janji['ID_janji_temu'] ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn-action btn-status" title="Ubah Status">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                    <div class="dropdown-content">
                                                        <a href="#" onclick="updateStatus(<?= $janji['ID_janji_temu'] ?>, 'pending')">
                                                            <i class="fas fa-clock"></i> Menunggu
                                                        </a>
                                                        <a href="#" onclick="updateStatus(<?= $janji['ID_janji_temu'] ?>, 'dikonfirmasi')">
                                                            <i class="fas fa-check"></i> Dikonfirmasi
                                                        </a>
                                                        <a href="#" onclick="updateStatus(<?= $janji['ID_janji_temu'] ?>, 'selesai')">
                                                            <i class="fas fa-check-double"></i> Selesai
                                                        </a>
                                                        <a href="#" onclick="updateStatus(<?= $janji['ID_janji_temu'] ?>, 'dibatalkan')">
                                                            <i class="fas fa-times"></i> Dibatalkan
                                                        </a>
                                                    </div>
                                                </div>
                                                <button class="btn-action btn-delete" onclick="deleteJanjiTemu(<?= $janji['ID_janji_temu'] ?>)" title="Hapus">
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
        </main>
    </div>

    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> <span id="modalTitle">Tambah Janji Temu</span></h3>
                <span class="close" onclick="closeModal('createModal')">&times;</span>
            </div>
            <form id="janjiTemuForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id_janji" id="formIdJanji">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_user">Pasien <span class="required">*</span></label>
                        <select name="id_user" id="id_user" required>
                            <option value="">-- Pilih Pasien --</option>
                            <?php foreach ($userList as $user): ?>
                                <option value="<?= $user['ID_user'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_dokter">Dokter <span class="required">*</span></label>
                        <select name="id_dokter" id="id_dokter" required>
                            <option value="">-- Pilih Dokter --</option>
                            <?php foreach ($dokterList as $dokter): ?>
                                <option value="<?= $dokter['ID_dokter'] ?>">
                                    Dr. <?= htmlspecialchars($dokter['Username']) ?> - <?= htmlspecialchars($dokter['Spesialisasi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal <span class="required">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="waktu">Waktu <span class="required">*</span></label>
                        <input type="time" name="waktu" id="waktu" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="pending">Menunggu</option>
                            <option value="dikonfirmasi">Dikonfirmasi</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="biaya">Biaya (Rp)</label>
                        <input type="number" name="biaya" id="biaya" min="0" step="1000" placeholder="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="keluhan">Keluhan <span class="required">*</span></label>
                    <textarea name="keluhan" id="keluhan" rows="3" required placeholder="Masukkan keluhan pasien..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <textarea name="catatan" id="catatan" rows="3" placeholder="Catatan tambahan (opsional)..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span id="submitText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Detail Janji Temu</h3>
                <span class="close" onclick="closeModal('detailModal')">&times;</span>
            </div>
            <div class="detail-content" id="detailContent">
            </div>
        </div>
    </div>

    <script>
        
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
            if (modalId === 'createModal') {
                resetForm();
            }
        }
        
        function resetForm() {
            document.getElementById('janjiTemuForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('formIdJanji').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah Janji Temu';
            document.getElementById('submitText').textContent = 'Simpan';
        }
        
        function editJanjiTemu(id) {
            window.location.href = '?edit=' + id;
        }
        
        function showDetail(id) {
            fetch('get_detail.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                    openModal('detailModal');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail');
                });
        }
        
        function updateStatus(id, status) {
            if (confirm('Yakin ingin mengubah status janji temu ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_status';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_janji';
                idInput.value = id;
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = status;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                form.appendChild(statusInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteJanjiTemu(id) {
            if (confirm('Yakin ingin menghapus janji temu ini? Data yang dihapus tidak dapat dikembalikan.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_janji';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('janjiTemuTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }
        
        function filterTable() {
            const filter = document.getElementById('statusFilter').value;
            const table = document.getElementById('janjiTemuTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const status = rows[i].getAttribute('data-status');
                if (filter === '' || status === filter) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
        
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target === modal) {
                    closeModal(modal.id);
                }
            }
        }
        
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        <?php if ($editData): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formAction').value = 'update';
            document.getElementById('formIdJanji').value = '<?= $editData['ID_janji_temu'] ?>';
            document.getElementById('id_user').value = '<?= $editData['ID_user'] ?>';
            document.getElementById('id_dokter').value = '<?= $editData['ID_dokter'] ?>';
            document.getElementById('tanggal').value = '<?= $editData['Tanggal'] ?>';
            document.getElementById('waktu').value = '<?= $editData['Waktu'] ?>';
            document.getElementById('keluhan').value = '<?= htmlspecialchars($editData['keluhan']) ?>';
            document.getElementById('catatan').value = '<?= htmlspecialchars($editData['catatan']) ?>';
            document.getElementById('biaya').value = '<?= $editData['biaya'] ?>';
            document.getElementById('status').value = '<?= $editData['Status'] ?>';
            document.getElementById('modalTitle').textContent = 'Edit Janji Temu';
            document.getElementById('submitText').textContent = 'Update';
            
            openModal('createModal');
        });
        <?php endif; ?>
        
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal').setAttribute('min', today);
        });
        
        document.getElementById('biaya').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                e.target.value = parseInt(value);
            }
        });
        
        document.getElementById('janjiTemuForm').addEventListener('submit', function(e) {
            const tanggal = document.getElementById('tanggal').value;
            const waktu = document.getElementById('waktu').value;
            const keluhan = document.getElementById('keluhan').value.trim();
            
            if (!tanggal || !waktu || !keluhan) {
                e.preventDefault();
                alert('Harap lengkapi semua field yang wajib diisi!');
                return false;
            }
            
            const selectedDate = new Date(tanggal + 'T' + waktu);
            const now = new Date();
            
            if (selectedDate < now && document.getElementById('formAction').value === 'create') {
                e.preventDefault();
                alert('Tanggal dan waktu tidak boleh di masa lalu!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>