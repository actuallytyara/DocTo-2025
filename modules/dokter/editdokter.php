<?php
include 'koneksidokter.php';
session_start();


 
if(!isset($_GET['id'])){
    header("Location: indexdokter.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
 
if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    $spesialisasi = mysqli_real_escape_string($koneksi, $_POST['spesialisasi']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    
    $query = "UPDATE dokter SET 
              Username = '$username',
              Pengguna_role = '$role',
              Spesialisasi = '$spesialisasi',
              Nomor_telepon = '$telepon'
              WHERE ID_dokter = '$id'";
    
    if(mysqli_query($koneksi, $query)){
        header("Location: indexdokter.php");
        exit;
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}
 
$query = "SELECT * FROM dokter WHERE ID_dokter = '$id'";
$result = mysqli_query($koneksi, $query);

if(mysqli_num_rows($result) == 0){
    header("Location: indexdokter.php");
    exit;
}

$dokter = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokter</title>
    <link rel="stylesheet" href="../../assets/css/style_dokter.css">
</head>
<body>
    <div class="container">
        <h1>Edit Data Dokter</h1>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?= $dokter['Username'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="role">Peran:</label>
                <input type="text" name="role" id="role" value="<?= $dokter['Pengguna_role'] ?>">
            </div>
            
            <div class="form-group">
                <label for="spesialisasi">Spesialisasi:</label>
                <input type="text" name="spesialisasi" id="spesialisasi" value="<?= $dokter['Spesialisasi'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telepon">Nomor Telepon:</label>
                <input type="text" name="telepon" id="telepon" value="<?= $dokter['Nomor_telepon'] ?>" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="submit">Update</button>
                <a href="indexdokter.php">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>