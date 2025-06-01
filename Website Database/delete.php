<?php

include_once("koneksi.php");


if(isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
 
    $stmt = $mysqli->prepare("DELETE FROM tb_login WHERE ID_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    
    header("Location: admin.php");
    exit();
} else {
    
    header("Location: admin.php");
    exit();
}
?>