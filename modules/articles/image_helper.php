<?php
/**
 * Image Helper Functions untuk DocTo
 * Mengelola tampilan gambar artikel dari berbagai folder
 */

class ImageHelper {
    
    /**
     * Mendapatkan path gambar yang valid
     * @param string $imageName nama file gambar
     * @return string|null path gambar yang valid atau null jika tidak ditemukan
     */
    public static function getValidImagePath($imageName) {
        if (empty($imageName)) {
            return null;
        }
        
        $possiblePaths = [
            'uploads/' . $imageName,          
            'images/' . $imageName,           
            '../images/' . $imageName,         
            '../../images/' . $imageName,      
            'assets/images/' . $imageName,     
            '../assets/images/' . $imageName, 
            '../../assets/images/' . $imageName, 
            'img/' . $imageName,             
            '../img/' . $imageName,           
            '../../img/' . $imageName         
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Generate HTML img tag dengan fallback
     * @param string $imageName nama file gambar
     * @param string $altText alt text untuk gambar
     * @param string $cssClass CSS class untuk gambar
     * @param string $defaultImage URL gambar default jika tidak ditemukan
     * @return string HTML img tag
     */
    public static function generateImageTag($imageName, $altText = '', $cssClass = '', $defaultImage = null) {
        $imagePath = self::getValidImagePath($imageName);
        
        if (!$defaultImage) {
            $defaultImage = 'https://via.placeholder.com/350x200/37966f/ffffff?text=No+Image';
        }
        
        if ($imagePath) {
            return sprintf(
                '<img src="%s" class="%s" alt="%s" onerror="this.onerror=null; this.src=\'%s\'; this.classList.add(\'image-placeholder\');">',
                htmlspecialchars($imagePath),
                htmlspecialchars($cssClass),
                htmlspecialchars($altText),
                htmlspecialchars($defaultImage)
            );
        } else {
            return sprintf(
                '<div class="%s image-placeholder"><i class="fas fa-image"></i></div>',
                htmlspecialchars($cssClass)
            );
        }
    }
    
    /**
     * Cek apakah gambar valid (format yang diizinkan)
     * @param string $imageName nama file gambar
     * @return bool true jika format valid
     */
    public static function isValidImageFormat($imageName) {
        if (empty($imageName)) {
            return false;
        }
        
        $allowedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        
        return in_array($extension, $allowedFormats);
    }
    
    /**
     * Resize gambar untuk optimasi (jika diperlukan)
     * @param string $sourcePath path gambar sumber
     * @param string $destPath path tujuan
     * @param int $maxWidth lebar maksimal
     * @param int $maxHeight tinggi maksimal
     * @return bool true jika berhasil
     */
    public static function resizeImage($sourcePath, $destPath, $maxWidth = 800, $maxHeight = 600) {
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($originalWidth, $originalHeight, $imageType) = $imageInfo;
        
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($imageType == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($newImage, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($newImage, $destPath, 8);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($newImage, $destPath);
                break;
            default:
                $result = false;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $result;
    }
    
    /**
     * Upload dan proses gambar
     * @param array $fileData data dari $_FILES
     * @param string $uploadDir direktori upload
     * @param string $newFileName nama file baru (opsional)
     * @return array hasil upload [success => bool, filename => string, error => string]
     */
    public static function uploadImage($fileData, $uploadDir = 'uploads/', $newFileName = null) {
        $result = [
            'success' => false,
            'filename' => '',
            'error' => ''
        ];
        
        if (!isset($fileData['error']) || $fileData['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'Error saat upload file';
            return $result;
        }
        
        $maxSize = 5 * 1024 * 1024; 
        if ($fileData['size'] > $maxSize) {
            $result['error'] = 'Ukuran file terlalu besar (maksimal 5MB)';
            return $result;
        }
        
        if (!self::isValidImageFormat($fileData['name'])) {
            $result['error'] = 'Format file tidak didukung';
            return $result;
        }
        
        if (!$newFileName) {
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $newFileName = 'img_' . time() . '_' . uniqid() . '.' . $extension;
        }
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
            $result['success'] = true;
            $result['filename'] = $newFileName;
        } else {
            $result['error'] = 'Gagal memindahkan file';
        }
        
        return $result;
    }
}

/**
 * Fungsi bantuan untuk backward compatibility
 */
function getImagePath($imageName) {
    return ImageHelper::getValidImagePath($imageName);
}

function displayArticleImage($imageName, $altText = '', $cssClass = 'article-image') {
    return ImageHelper::generateImageTag($imageName, $altText, $cssClass);
}
?>