<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: berhasil_login.php");
    exit();
}

if (isset($_POST['submit'])) {
    
    include "../database/koneksi.php";
    
    if (!isset($conn) || $conn === null) {
        die("Error: Database connection failed. Please check your database configuration.");
    }
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || 
        empty($_POST['alamat']) || empty($_POST['telepon'])) {
        echo "<script>alert('Semua field harus diisi!');</script>";
    } else {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $password = trim($_POST['password']);
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $alamat = mysqli_real_escape_string($conn, trim($_POST['alamat']));
        $telepon = mysqli_real_escape_string($conn, trim($_POST['telepon']));
        $pengguna = 'user';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Format email tidak valid!');</script>";
        } else {
            
            $check_query = "SELECT * FROM tb_login WHERE username=? OR email=?";
            $check_stmt = $conn->prepare($check_query);
            
            if ($check_stmt) {
                $check_stmt->bind_param("ss", $username, $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    echo "<script>alert('Username atau Email sudah terdaftar! Silakan gunakan yang lain.');</script>";
                } else {
                    $insert_query = "INSERT INTO tb_login (username, password, pengguna, email, alamat, nomor_telepon) VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("ssssss", $username, $password, $pengguna, $email, $alamat, $telepon);
                        
                        if ($insert_stmt->execute()) {
                            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='login.php';</script>";
                            exit();
                        } else {
                            echo "<script>alert('Registrasi gagal! Silakan coba lagi. Error: " . $insert_stmt->error . "');</script>";
                        }
                        $insert_stmt->close();
                    } else {
                        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
                    }
                }
                $check_stmt->close();
            } else {
                echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Register</title>
    <link rel="stylesheet" href="../assets/css/style_login_register.css">
</head>

<body>
    <div class="container">
        <div class="kotak_register">
            <h1>Register</h1>
            <form action="Register.php" method="post" name="form1">
                <table>
                    <tr>
                        <td>Username</td>
                        <td><input type="text" name="username" placeholder="Isi Username anda" required maxlength="10"></td>
                    </tr>

                    <tr>
                        <td>Password</td>
                        <td><input type="password" name="password" placeholder="Isi Password anda" required maxlength="10"></td>
                    </tr>
                    
                    <tr>
                        <td>Email</td>
                        <td><input type="email" name="email" placeholder="Isi email anda" required maxlength="255"></td>
                    </tr>
                    
                    <tr>
                        <td>Alamat</td>
                        <td><input type="text" name="alamat" placeholder="Isi alamat anda" required maxlength="255"></td>
                    </tr>
                    
                    <tr>
                        <td>Nomor Telepon</td>
                        <td><input type="text" name="telepon" placeholder="Isi nomor telepon anda" required maxlength="15"></td>
                    </tr>
                    
                    <tr>
                        <td colspan="2">
                            <button class="tombol_login" name="submit" type="submit">Daftar</button>
                            <div style="display: flex; align-items: center; gap: 15px; margin-top: 5px;">
                                <a href="login.php" style="font-size: 14px; color: #2c6e63; text-decoration: none;">Sudah Punya Akun?</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</body>

</html>