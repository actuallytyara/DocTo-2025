<?php
include 'koneksidokter.php';
session_start();


$query_mysql = "SELECT * FROM dokter";
$result = mysqli_query($koneksi, $query_mysql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dokter</title>
    <link rel="stylesheet" href="../../assets/css/style_dokter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>Data Dokter</h1>
        <p>Silahkan Mengisi dengan Jujur</p>
        <a href="tambah_dokter.php" class="btn">Tambah Dokter Baru</a>
        
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Peran</th>
                    <th>Spesialisasi</th>
                    <th>Nomor Telepon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['ID_dokter']; ?></td>
                    <td><?= $row['Username']; ?></td>
                    <td><?= $row['Pengguna_role']; ?></td>
                    <td><?= $row['Spesialisasi']; ?></td>
                    <td><?= $row['Nomor_telepon']; ?></td>
                    <td>
                        <a href="editdokter.php?id=<?= $row['ID_dokter']; ?>" class="edit">EDIT</a>
                        <a href="hapus_dokter.php?id=<?= $row['ID_dokter']; ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">DELETE</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if(mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="6" align="center">Tidak ada data dokter</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <p><a href="index.php">Kembali ke Beranda</a></p>
    </div>
</body>
</html>