<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit user</title>
    <link rel="stylesheet" href="styleedit1.css">
</head>

<body>
    <?php
    include("koneksi.php");
    
  
    if (!isset($_GET['id'])) {
        header('Location: admin.php');
        exit();
    }
    
    $user_id = $_GET['id'];
    
    
    $stmt = $mysqli->prepare("SELECT * FROM tb_login WHERE ID_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 0) {
        
        header('Location: admin.php');
        exit();
    }
    
    $user_data = $result->fetch_assoc();
    $username = $user_data['username'];
    $password = $user_data['password'];
    $pengguna = $user_data['pengguna'];
    $email = $user_data['email'];
    $stmt->close();
    ?>
    
    <form method="POST" action="edit2.php">
        <div class="form-edit-box">
            <div class="form-edit-box-username">
                <label for="username">Username : </label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>
            </div>
            <div class="form-edit-box-password">
                <label for="password">Password : </label>
                <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required><br>
            </div>
            <div class="form-edit-box-level">
                <label for="pengguna">Pengguna : </label>
                <input type="radio" name="pengguna" value="admin" <?php echo ($pengguna == 'admin') ? 'checked' : ''; ?>> admin
                <input type="radio" name="pengguna" value="user" <?php echo ($pengguna == 'user') ? 'checked' : ''; ?>> user <br>
            </div>
            <div class="form-edit-box-email">
                <label for="email">Email : </label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required> <br>
            </div>
            <div class="form-edit-box-button">
                <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                <input type="submit" name="submit-data" value="Submit">
            </div>
        </div>
    </form>
</body>

</html>