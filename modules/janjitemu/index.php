<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || 
   ($_SESSION['pengguna'] !== 'user' && $_SESSION['pengguna'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

function getDokterList($conn) {
    $sql = "SELECT ID_dokter, Username, Spesialisasi FROM dokter ORDER BY Username";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getJanjiTemuUser($conn, $user_id) {
    $sql = "SELECT jt.*, d.Username as nama_dokter, d.Spesialisasi 
            FROM janji_temu jt 
            LEFT JOIN dokter d ON jt.ID_dokter = d.ID_dokter 
            WHERE jt.ID_user = ?
            ORDER BY jt.Tanggal DESC, jt.Waktu DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getStatusDisplay($status) {
    switch(strtolower($status)) {
        case 'pending':
            return 'Menunggu';
        case 'dikonfirmasi':
            return 'Dikonfirmasi';
        case 'selesai':
            return 'Selesai';
        case 'dibatalkan':
            return 'Dibatalkan';
        case '':
        case null:
            return 'Menunggu';
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
function getBookedSlots($conn, $tanggal, $dokter_id) {
    $sql = "SELECT Waktu FROM janji_temu WHERE Tanggal = ? AND ID_dokter = ? AND Status NOT IN ('dibatalkan', 'selesai')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $tanggal, $dokter_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booked_slots = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $booked_slots[] = $row['Waktu'];
    }
    return $booked_slots;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $id_dokter = $_POST['id_dokter'];
                $waktu = $_POST['waktu'];
                $tanggal = $_POST['tanggal'];
                $keluhan = $_POST['keluhan'];
                $catatan = $_POST['catatan'] ?? '';
                
                if ($tanggal < date('Y-m-d')) {
                    $error = "Tanggal tidak boleh di masa lalu!";
                    break;
                }
                
                $booked_slots = getBookedSlots($conn, $tanggal, $id_dokter);
                if (in_array($waktu, $booked_slots)) {
                    $error = "Slot waktu sudah terboking! Silakan pilih waktu lain.";
                    break;
                }
                
                $sql = "INSERT INTO janji_temu (ID_user, ID_dokter, Waktu, Tanggal, keluhan, catatan, Status, biaya) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending', 0.00)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iissss", $current_user_id, $id_dokter, $waktu, $tanggal, $keluhan, $catatan);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Janji temu berhasil dibuat! Menunggu konfirmasi dokter.";
                } else {
                    $error = "Gagal membuat janji temu: " . mysqli_error($conn);
                }
                break;
                
            case 'cancel':
                $id_janji = $_POST['id_janji'];
                
                $sql = "UPDATE janji_temu SET Status = 'dibatalkan' WHERE ID_janji_temu = ? AND ID_user = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $id_janji, $current_user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_affected_rows($conn) > 0) {
                        $success = "Janji temu berhasil dibatalkan!";
                    } else {
                        $error = "Janji temu tidak ditemukan atau bukan milik Anda!";
                    }
                } else {
                    $error = "Gagal membatalkan janji temu: " . mysqli_error($conn);
                }
                break;
                
            case 'get_available_slots':
                header('Content-Type: application/json');
                $tanggal = $_POST['tanggal'];
                $dokter_id = $_POST['dokter_id'];
                
                $booked_slots = getBookedSlots($conn, $tanggal, $dokter_id);
                $all_slots = ['08:00:00', '09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];
                $available_slots = array_diff($all_slots, $booked_slots);
                
                echo json_encode(['available_slots' => array_values($available_slots)]);
                exit;
        }
    }
}

$dokterList = getDokterList($conn);
$janjiTemuList = getJanjiTemuUser($conn, $current_user_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocTo - Janji Temu</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-dikonfirmasi {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-selesai {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .status-dibatalkan {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .appointment-card.pending {
            border-left: 4px solid #d97706;
        }

        .appointment-card.dikonfirmasi {
            border-left: 4px solid #16a34a;
        }

        .appointment-card.selesai {
            border-left: 4px solid #2563eb;
        }

        .appointment-card.dibatalkan {
            border-left: 4px solid #dc2626;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <i class="fas fa-stethoscope"></i>
                <h1>Janji Temu</h1>
            </div>
            <nav class="nav">
                <a href="../articles/artikel.php"><i class="fas fa-newspaper"></i> Artikel</a>
                <a href="../pemesanan/katalog.php"><i class="fas fa-pills"></i> Katalog Obat</a>
                <a href="#" class="active"><i class="fas fa-calendar-check"></i> Janji Temu</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </header>

        <main class="main-content">
            <div class="janji-temu-container">
                <div class="form-section">
                    <div class="form-card">
                        <h2><i class="fas fa-plus-circle"></i> Buat Janji Temu Baru</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $success ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="janji-form" id="janjiForm">
                            <input type="hidden" name="action" value="create">

                            <div class="form-group">
                                <label for="id_dokter">Pilih Dokter:</label>
                                <select id="id_dokter" name="id_dokter" required>
                                    <option value="">-- Pilih Dokter --</option>
                                    <?php foreach ($dokterList as $dokter): ?>
                                        <option value="<?= $dokter['ID_dokter'] ?>" data-spesialisasi="<?= $dokter['Spesialisasi'] ?>">
                                            Dr. <?= $dokter['Username'] ?> - <?= $dokter['Spesialisasi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal:</label>
                                    <input type="date" id="tanggal" name="tanggal" required min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="waktu">Waktu:</label>
                                    <select id="waktu" name="waktu" required disabled>
                                        <option value="">-- Pilih Dokter dan Tanggal Dulu --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="keluhan">Keluhan:</label>
                                <textarea id="keluhan" name="keluhan" required placeholder="Jelaskan keluhan Anda..." rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="catatan">Catatan Tambahan:</label>
                                <textarea id="catatan" name="catatan" placeholder="Catatan tambahan (opsional)" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Buat Janji Temu
                            </button>
                        </form>
                    </div>
                </div>

                <div class="calendar-section">
                    <div class="calendar-card">
                        <h3><i class="fas fa-calendar-alt"></i> Kalender</h3>
                        <div class="calendar-header-nav">
                            <button id="prevMonth" class="btn-nav"><i class="fas fa-chevron-left"></i></button>
                            <span id="monthYear"></span>
                            <button id="nextMonth" class="btn-nav"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-header">
                                <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                            </div>
                            <div class="calendar-body" id="calendarBody">
                            </div>
                        </div>
                    </div>

                    <div class="time-slots-card">
                        <h3><i class="fas fa-clock"></i> Slot Waktu Tersedia</h3>
                        <div class="time-grid" id="timeGrid">
                            <p class="no-slots">Pilih dokter dan tanggal untuk melihat slot waktu tersedia</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-appointments">
                <h2><i class="fas fa-calendar-check"></i> Janji Temu Saya</h2>
                <div class="appointments-grid">
                    <?php if (empty($janjiTemuList)): ?>
                        <div class="no-appointments">
                            <i class="fas fa-calendar-times"></i>
                            <p>Belum ada janji temu</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($janjiTemuList as $janji): ?>
                            <?php 
                            $statusDisplay = getStatusDisplay($janji['Status']);
                            $statusClass = getStatusClass($janji['Status']);
                            ?>
                            <div class="appointment-card <?= $statusClass ?>">
                                <div class="appointment-header">
                                    <h4>Dr. <?= htmlspecialchars($janji['nama_dokter'] ?? 'Unknown') ?></h4>
                                    <span class="status-badge status-<?= $statusClass ?>">
                                        <?= htmlspecialchars($statusDisplay) ?>
                                    </span>
                                </div>
                                <div class="appointment-details">
                                    <p><i class="fas fa-stethoscope"></i> <?= htmlspecialchars($janji['Spesialisasi'] ?? 'Spesialisasi tidak tersedia') ?></p>
                                    <p><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($janji['Tanggal'])) ?></p>
                                    <p><i class="fas fa-clock"></i> <?= date('H:i', strtotime($janji['Waktu'])) ?></p>
                                    <p><i class="fas fa-comment-medical"></i> <?= htmlspecialchars($janji['keluhan']) ?></p>
                                    <?php if (!empty($janji['catatan'])): ?>
                                        <p><i class="fas fa-sticky-note"></i> <?= htmlspecialchars($janji['catatan']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($janji['biaya'] > 0): ?>
                                        <p><i class="fas fa-money-bill-wave"></i> Biaya: Rp <?= number_format($janji['biaya'], 0, ',', '.') ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (strtolower($janji['Status']) === 'pending' || $janji['Status'] === '' || $janji['Status'] === null): ?>
                                    <div class="appointment-actions">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin membatalkan janji temu ini?')">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="id_janji" value="<?= $janji['ID_janji_temu'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Batalkan
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentDate = new Date();
        let selectedDate = null;
        let selectedDokter = null;

        function generateCalendar(year, month) {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startDay = firstDay.getDay();

            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            document.getElementById('monthYear').textContent = `${monthNames[month]} ${year}`;

            const calendarBody = document.getElementById('calendarBody');
            calendarBody.innerHTML = '';

            for (let i = 0; i < startDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'calendar-day empty';
                calendarBody.appendChild(emptyCell);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-day';
                dayCell.textContent = day;

                const cellDate = new Date(year, month, day);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (cellDate < today) {
                    dayCell.classList.add('disabled');
                } else {
                    dayCell.addEventListener('click', () => selectDate(year, month, day));
                }

                if (cellDate.toDateString() === today.toDateString()) {
                    dayCell.classList.add('today');
                }

                calendarBody.appendChild(dayCell);
            }
        }

        function selectDate(year, month, day) {
            document.querySelectorAll('.calendar-day.selected').forEach(cell => {
                cell.classList.remove('selected');
            });

            event.target.classList.add('selected');

            selectedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            document.getElementById('tanggal').value = selectedDate;

            updateTimeSlots();
        }

        function updateTimeSlots() {
            if (!selectedDate || !selectedDokter) {
                document.getElementById('timeGrid').innerHTML = '<p class="no-slots">Pilih dokter dan tanggal untuk melihat slot waktu tersedia</p>';
                document.getElementById('waktu').disabled = true;
                document.getElementById('waktu').innerHTML = '<option value="">-- Pilih Dokter dan Tanggal Dulu --</option>';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'get_available_slots');
            formData.append('tanggal', selectedDate);
            formData.append('dokter_id', selectedDokter);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const timeGrid = document.getElementById('timeGrid');
                const waktuSelect = document.getElementById('waktu');
                
                timeGrid.innerHTML = '';
                waktuSelect.innerHTML = '<option value="">-- Pilih Waktu --</option>';

                if (data.available_slots.length === 0) {
                    timeGrid.innerHTML = '<p class="no-slots">Tidak ada slot waktu tersedia</p>';
                    waktuSelect.disabled = true;
                } else {
                    waktuSelect.disabled = false;
                    
                    data.available_slots.forEach(slot => {
                        const timeSlot = document.createElement('div');
                        timeSlot.className = 'time-slot available';
                        timeSlot.textContent = slot.substring(0, 5); 
                        timeSlot.addEventListener('click', () => selectTimeSlot(slot, timeSlot));
                        timeGrid.appendChild(timeSlot);

                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = slot.substring(0, 5);
                        waktuSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('timeGrid').innerHTML = '<p class="no-slots">Error loading time slots</p>';
            });
        }

        function selectTimeSlot(time, element) {
            document.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });

            element.classList.add('selected');
            document.getElementById('waktu').value = time;
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        document.getElementById('id_dokter').addEventListener('change', function() {
            selectedDokter = this.value;
            updateTimeSlots();
        });

        document.getElementById('tanggal').addEventListener('change', function() {
            selectedDate = this.value;
            updateTimeSlots();
            
            if (selectedDate) {
                const date = new Date(selectedDate);
                if (date.getFullYear() !== currentDate.getFullYear() || 
                    date.getMonth() !== currentDate.getMonth()) {
                    currentDate = new Date(date);
                    generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
                }
            }
        });

        generateCalendar(currentDate.getFullYear(), currentDate.getMonth());

        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>