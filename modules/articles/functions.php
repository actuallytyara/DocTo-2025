<?php

include 'koneksi.php';

/**
 * Fungsi untuk melakukan login pengguna
 * @param mysqli $conn Koneksi database
 * @param string $username Username pengguna
 * @param string $password Password pengguna
 * @return bool|array False jika gagal, data pengguna jika berhasil
 */
function userLogin($conn, $username, $password) {
    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            $_SESSION['is_admin'] = ($user['is_admin'] == 1) ? true : false;
            
            return $user;
        }
    }
    
    return false;
}

/**
 * Fungsi untuk memeriksa status admin
 * @return bool True jika pengguna adalah admin, false jika tidak
 */
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return (isset($_SESSION['username']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
}

/**
 * Fungsi untuk me-redirect user yang tidak memiliki hak admin
 * @param string $redirect_url URL tujuan redirect jika bukan admin
 */
function requireAdmin($redirect_url = 'index.php') {
    if (!isAdmin()) {
        header("Location: $redirect_url");
        exit();
    }
}

function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function getAllArticles($conn) {
    $query = "SELECT * FROM artikel ORDER BY tanggal_posting DESC";
    $result = mysqli_query($conn, $query);
    
    $articles = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $articles[] = $row;
        }
    }
    
    return $articles;
}

function getArticleById($conn, $id) {
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM artikel WHERE ID_artikel = '$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

function getArticleBySlug($conn, $slug) {
    $slug = mysqli_real_escape_string($conn, $slug);
    $query = "SELECT * FROM artikel WHERE slug = '$slug'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

function addArticle($conn, $data) {
    try {
        $judul = mysqli_real_escape_string($conn, $data['judul']);
        $slug = mysqli_real_escape_string($conn, createSlug($judul));
        $isi_artikel = mysqli_real_escape_string($conn, $data['isi_artikel']);
        $status = mysqli_real_escape_string($conn, $data['status']);

        $gambar = '';
        if (isset($data['gambar']) && $data['gambar']['error'] == 0) {
            $target_dir = "uploads/";
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($data['gambar']['name']);
            $target_file = $target_dir . $file_name;

            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $extensions_arr = ["jpg", "jpeg", "png", "gif"];
            
            if (in_array($imageFileType, $extensions_arr)) {
                if ($data['gambar']['size'] <= 5000000) {
                    if (move_uploaded_file($data['gambar']['tmp_name'], $target_file)) {
                        $gambar = $file_name;
                    } else {
                        throw new Exception("Gagal mengupload gambar");
                    }
                } else {
                    throw new Exception("Ukuran file gambar terlalu besar (maksimal 5MB)");
                }
            } else {
                throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau GIF");
            }
        }
        
        $query = "INSERT INTO artikel (judul, slug, isi_artikel, gambar, tanggal_posting, status) 
                  VALUES ('$judul', '$slug', '$isi_artikel', '$gambar', NOW(), '$status')";
        
        if (mysqli_query($conn, $query)) {
            return mysqli_insert_id($conn);
        } else {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        error_log("Error in addArticle: " . $e->getMessage());
        throw $e;
    }
}

function updateArticle($conn, $id, $data) {
    try {
        $id = mysqli_real_escape_string($conn, $id);
        $judul = mysqli_real_escape_string($conn, $data['judul']);
        $slug = mysqli_real_escape_string($conn, createSlug($judul));
        $isi_artikel = mysqli_real_escape_string($conn, $data['isi_artikel']);
        $status = mysqli_real_escape_string($conn, $data['status']);
        
        $current_article = getArticleById($conn, $id);
        if (!$current_article) {
            throw new Exception("Artikel tidak ditemukan");
        }
        
        $gambar = $current_article['gambar'];
        
        if (isset($data['gambar']) && $data['gambar']['error'] == 0) {
            $target_dir = "uploads/";
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($data['gambar']['name']);
            $target_file = $target_dir . $file_name;
            
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $extensions_arr = ["jpg", "jpeg", "png", "gif"];
            
            if (in_array($imageFileType, $extensions_arr)) {
                if ($data['gambar']['size'] <= 5000000) {
                    if (move_uploaded_file($data['gambar']['tmp_name'], $target_file)) {
                        if (!empty($current_article['gambar']) && file_exists($target_dir . $current_article['gambar'])) {
                            unlink($target_dir . $current_article['gambar']);
                        }
                        $gambar = $file_name;
                    } else {
                        throw new Exception("Gagal mengupload gambar");
                    }
                } else {
                    throw new Exception("Ukuran file gambar terlalu besar (maksimal 5MB)");
                }
            } else {
                throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau GIF");
            }
        }
        
        $query = "UPDATE artikel 
                  SET judul = '$judul', 
                      slug = '$slug', 
                      isi_artikel = '$isi_artikel', 
                      gambar = '$gambar', 
                      status = '$status' 
                  WHERE ID_artikel = '$id'";
        
        if (mysqli_query($conn, $query)) {
            return true;
        } else {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        error_log("Error in updateArticle: " . $e->getMessage());
        throw $e;
    }
}

function deleteArticle($conn, $id) {
    try {
        $id = mysqli_real_escape_string($conn, $id);
        
        $article = getArticleById($conn, $id);
        
        if (!$article) {
            throw new Exception("Artikel tidak ditemukan");
        }
        
        if (!empty($article['gambar']) && file_exists("uploads/" . $article['gambar'])) {
            unlink("uploads/" . $article['gambar']);
        }
        
        $query = "DELETE FROM artikel WHERE ID_artikel = '$id'";
        
        if (mysqli_query($conn, $query)) {
            return true;
        } else {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        error_log("Error in deleteArticle: " . $e->getMessage());
        throw $e;
    }
}
?>