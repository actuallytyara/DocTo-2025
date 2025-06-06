<?php
include("koneksi.php");


if (!isset($_GET['id'])) {
 header('Location: admin.php');
 exit();
}

if (isset($_POST['submit-data'])) {
    $user_id = $_POST['id_user'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $pengguna = $_POST['pengguna'];
    $email = $_POST['email'];

   
    $result = mysqli_query($mysqli, "UPDATE tb_login SET username='$username', password='$password', pengguna='$penggunas',email='$email' WHERE ID_user=$user_id");

    
    header('Location: admin.php');
} else {
    die("Akses dilarang...");
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    if (!empty($email)) {
        echo "email: " . htmlspecialchars($email);
    } else {
        echo "email tidak boleh kosong!";
    }
} else {
    echo "Form belum disubmit.";
}

