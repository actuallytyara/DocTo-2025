<?php
session_start();

?>


<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Login berhasil!</title>
</head>
<body>
    <div class="container-logout">
        <form action="logout.php" method="POST" class="login-email">
            <h1>AYAM 🐔</h1>
            <h1>Selamat datang, 
                <?php echo $_SESSION['username']; ?>
                !</h1>

                <h1>HAAAIII</h1>
            <div class="input-group">
                <button type="submit" class="btn">Logout</button>
            </div>
        </form>
    </div>
</body>
</html>