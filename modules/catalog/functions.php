<?php

$catalogs = getAllCatalog(); 


require_once('koneksi.php');


function sanitizeInput($item)
{
    return htmlspecialchars(strip_tags(trim($item)));
}


function getAllCatalog() {
    global $koneksi;
    $query = "SELECT * FROM catalog ORDER BY ID_katalog";
    $result = mysqli_query($koneksi, $query);
    
    $catalogs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $catalogs[] = $row;
    }
    
    return $catalogs;
}



function getCatalogById($id) {
    global $koneksi;
    $query = "SELECT * FROM catalog WHERE ID_katalog = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}


function addCatalog($nama_barang, $harga_barang, $jenis_barang, $user_id) {
    global $koneksi;
    
    $query = "INSERT INTO catalog (Nama_barang, Harga_barang, Jenis_barang, ID_user) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sdsi", $nama_barang, $harga_barang, $jenis_barang, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($koneksi);
    } else {
        return false;
    }
}

/*fungsi barang*/
function updateCatalog($id, $nama_barang, $harga_barang, $jenis_barang) {
    global $koneksi;
    
    $query = "UPDATE catalog SET Nama_barang = ?, Harga_barang = ?, Jenis_barang = ? WHERE ID_katalog = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sdsi", $nama_barang, $harga_barang, $jenis_barang, $id);
    
    return mysqli_stmt_execute($stmt);
}


function deleteCatalog($id) {
    global $koneksi;
    
    $query = "DELETE FROM catalog WHERE ID_katalog = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    return mysqli_stmt_execute($stmt);
}


function searchCatalog($keyword) {
    global $koneksi;
    
    $keyword = "%" . $keyword . "%";
    $query = "SELECT * FROM catalog WHERE Nama_barang LIKE ? OR Jenis_barang LIKE ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $keyword, $keyword);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $catalogs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $catalogs[] = $row;
    }
    
    return $catalogs;
}
?>
