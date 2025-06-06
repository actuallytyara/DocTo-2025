<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styleadmin2.css">
</head>
<style>
       
        a {
            text-decoration: none;
            outline: none;
        }
        
       
        button {
            outline: none;
        }
        
       
        a:focus, button:focus {
            outline: none;
        }
        
        
        a:hover {
            text-decoration: none;
        }
    </style>

<h2 style="text-align: center; color: black; margin: 20px 0;">
  Dashboard Admin - Data User & Admin
</h2>

<body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <table border="1" class="table">
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Password</th>
                <th>Pengguna</th>
                <th>Email</th>
                <th colspan="2">Aksi</th>
            </tr>

           <?php
       
        $nomor = 1;
        include 'koneksi.php';
        $query_mysqli = mysqli_query($mysqli, "SELECT * FROM tb_login") or die(mysqli_error($mysqli));

        while ($data = mysqli_fetch_array($query_mysqli)) {
        ?>
            <tr>
                <td><?php echo $nomor++; ?></td>
                <td><?php echo htmlspecialchars($data['username']); ?></td>
                <td><?php echo htmlspecialchars($data['password']); ?></td>
                <td><?php echo htmlspecialchars($data['pengguna']); ?></td>
                <td><?php echo htmlspecialchars($data['email']); ?></td>
                <td style="text-align: center">
                    <a href="edit.php?id=<?php echo $data['ID_user']; ?>">
                        <button type="button" class="editButton">EDIT</button>
                    </a>
                    <a href="delete.php?id=<?php echo $data['ID_user']; ?>">
                        <button type="button" class="deleteButton" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"> DELETE</button>
                    </a>


                    
                </td>
            </tr>
        <?php } ?>
    </table>
</body>

</html>