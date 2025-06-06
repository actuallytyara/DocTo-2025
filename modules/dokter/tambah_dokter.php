<?php
include 'koneksidokter.php';
session_start();


 
if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    $spesialisasi = mysqli_real_escape_string($koneksi, $_POST['spesialisasi']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    
    $query_mysql = "INSERT INTO dokter (Username, Pengguna_role, Spesialisasi, Nomor_telepon) 
              VALUES ('$username', '$role', '$spesialisasi', '$telepon')";
    
    if(mysqli_query($koneksi, $query_mysql)){
        header("Location: indexdokter.php");
        exit;
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dokter</title>
    <link rel="stylesheet" href="../../assets/css/style_dokter.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Dokter Baru</h1>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Masukan Username anda"
                autocomplete="off" required>
            </div>
            
            <div class="form-group">
                <label for="role">Peran:</label>
                <input type="text" name="role" id="role" value="dokter">
            </div>
            
            <div class="form-group">
                <label for="spesialisasi">Spesialisasi:</label>
                <input type="text" name="spesialisasi" id="spesialisasi" placeholder="Apa spesialisasi anda?"
                autocomplete="off" required>
            </div>
            
            <div class="form-group">
                <label for="telepon">Nomor Telepon:</label>
                <input type="text" name="telepon" id="telepon" placeholder="+62 000 00000"
                autocomplete="off" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="submit">Simpan</button>
                <a href="indexdokter.php">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>