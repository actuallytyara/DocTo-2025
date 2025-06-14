<?php
include("koneksi.php");

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: user_dashboard.php');
    exit();
}

$user_id = $_GET['id'];

if(!is_numeric($user_id)) {
    header('Location: user_dashboard.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT gambar FROM tb_login WHERE ID_user = ?");
    $stmt->execute([$user_id]);
    
    if($stmt->rowCount() === 0) {
        echo "<script>
                alert('Data user tidak ditemukan!');
                window.location.href = 'user_dashboard.php';
              </script>";
        exit();
    }
    
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $gambar = $user_data['gambar'];
    
    $delete_stmt = $pdo->prepare("DELETE FROM tb_login WHERE ID_user = ?");
    
    if($delete_stmt->execute([$user_id])) {
        if(!empty($gambar) && file_exists($gambar)) {
            unlink($gambar);
        }
        
        echo "<script>
                alert('Data user berhasil dihapus!');
                window.location.href = 'user_dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Error: Gagal menghapus data user!');
                window.location.href = 'user_dashboard.php';
              </script>";
    }
    
} catch(PDOException $e) {
    echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href = 'user_dashboard.php';
          </script>";
}
?>