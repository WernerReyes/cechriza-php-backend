<?php
class FileUploader {
    private $uploadDir;
    private $allowedExtensions;
    private $maxFileSize;
    private $allowedMimeTypes;

    public function __construct() {
        $this->uploadDir = __DIR__ . '/../../public/uploads/images/';
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ];
    }

    public function uploadImage($file, $customName = null) {
        try {
            // // Validar archivo
            // $validation = $this->validateFile($file);
            // if ($validation !== true) {
            //     throw new Exception($validation);
            // }

            // Crear directorio si no existe
            $targetDir = $this->uploadDir;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Generar nombre único
            $fileName = $this->generateFileName($file, $customName);
            $targetPath = $targetDir . $fileName;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Error al mover el archivo');
            }

            // Si es SVG, sanitizar
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'svg') {

                $this->sanitizeSvg($targetPath);
            }


            // Optimizar imagen
            $this->optimizeImage($targetPath);

            return [
                'success' => true,
                'filename' => $fileName,
                'path' => "/uploads/images/$fileName",
                'full_path' => $targetPath,
                'size' => filesize($targetPath),
                'url' => $this->getPublicUrl($fileName)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }


     /**
     * Limpia un archivo SVG para evitar código malicioso.
     */
    private function sanitizeSvg(string $filePath): void
    {
        $content = file_get_contents($filePath);

        // Eliminar etiquetas <script> y <foreignObject>
        $content = preg_replace('/<script.*?<\/script>/is', '', $content);
        $content = preg_replace('/<foreignObject.*?<\/foreignObject>/is', '', $content);

        // Eliminar cualquier evento como onload, onclick, etc.
        $content = preg_replace('/on\w+="[^"]*"/i', '', $content);

        file_put_contents($filePath, $content);
    }


     public function updateImage($file, $oldImagePath) {
        try {
            $customName = null;
            
            // Si quiere mantener el nombre original, extraerlo
            if ($oldImagePath) {
                $pathInfo = pathinfo($oldImagePath);
                $customName = pathinfo($pathInfo['filename'], PATHINFO_FILENAME);
                
                // Remover timestamp si existe
                $customName = preg_replace('/_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_[a-f0-9]{8}$/', '', $customName);
            }

            return $this->replaceImage($file, $oldImagePath, $customName);

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function replaceImage($file, $oldImagePath, $customName = null) {
        try {
            // 1. Subir nueva imagen
            $uploadResult = $this->uploadImage($file, $customName);
            
            if (!$uploadResult['success']) {
                return $uploadResult;
            }

            // 2. Eliminar imagen anterior (solo si la subida fue exitosa)
            if ($oldImagePath) {
                $this->deleteImage($oldImagePath);
                
                // También eliminar thumbnail si existe
                $this->deleteThumbnail($oldImagePath);
            }

            return $uploadResult;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }


    /**
     * ✅ Eliminar thumbnail
     */
    private function deleteThumbnail($imagePath) {
        if (empty($imagePath)) return false;

        $fullPath = $this->buildFullPath($imagePath);
        $pathInfo = pathinfo($fullPath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        
        if (file_exists($thumbnailPath)) {
            $deleted = unlink($thumbnailPath);
            error_log("Deleted thumbnail: $thumbnailPath - " . ($deleted ? 'SUCCESS' : 'FAILED'));
            return $deleted;
        }
        
        return false;
    }


    /**
     * ✅ Construir ruta completa desde path relativo
     */
    private function buildFullPath($imagePath) {
        // Si ya es ruta completa, devolverla
        if (strpos($imagePath, $this->uploadDir) === 0) {
            return $imagePath;
        }

        // Si empieza con /uploads/images/, remover para evitar duplicación
        $relativePath = ltrim($imagePath, '/');
        if (strpos($relativePath, 'uploads/images/') === 0) {
            $relativePath = substr($relativePath, strlen('uploads/images/'));
        }

        return $this->uploadDir . $relativePath;
    }

    public function validateFile($file, $allowExtensions = []) {
        if (empty(($file))) {
            return 'No se subió ningún archivo';
        }
        
        // Verificar errores de PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->getUploadError($file['error']);
        }

        // Verificar tamaño
        if ($file['size'] > $this->maxFileSize) {
            return 'El archivo excede el tamaño máximo de ' . ($this->maxFileSize / 1024 / 1024) . 'MB';
        }

        // Verificar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowExtensions)) {
            return 'Extensión no permitida. Extensiones válidas: ' . implode(', ', $allowExtensions);
        }

        // Verificar MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return 'Tipo de archivo no válido';
        }

        // Verificar que sea una imagen real
        if ($mimeType !== 'image/svg+xml' && !getimagesize($file['tmp_name'])) {
            return 'El archivo no es una imagen válida';
        }

        return true;
    }

    private function generateFileName($file, $customName = null) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($customName) {
            // Limpiar nombre personalizado
            $customName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $customName);
            return $customName . '_' . time() . '.' . $extension;
        }

        // Nombre único basado en timestamp y hash
        $hash = substr(md5($file['name'] . time()), 0, 8);
        return date('Y-m-d_H-i-s') . '_' . $hash . '.' . $extension;
    }

    private function optimizeImage($imagePath) {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return false;

        $mime = $imageInfo['mime'];
        $maxWidth = 1200;
        $maxHeight = 1200;
        $quality = 85;

        // Cargar imagen según el tipo
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                return true; // No optimizar GIFs animados
            case 'image/webp':
                $image = imagecreatefromwebp($imagePath);
                break;
            default:
                return false;
        }

        if (!$image) return false;

        $width = imagesx($image);
        $height = imagesy($image);

        // Redimensionar si es necesario
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Mantener transparencia para PNG
            if ($mime === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Guardar imagen optimizada
            switch ($mime) {
                case 'image/jpeg':
                    imagejpeg($resized, $imagePath, $quality);
                    break;
                case 'image/png':
                    imagepng($resized, $imagePath, 9);
                    break;
                case 'image/webp':
                    imagewebp($resized, $imagePath, $quality);
                    break;
            }

            imagedestroy($resized);
        }

        imagedestroy($image);
        return true;
    }

    private function getUploadError($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por PHP';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo del formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta el directorio temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo';
            case UPLOAD_ERR_EXTENSION:
                return 'Extensión de PHP bloqueó la subida';
            default:
                return 'Error desconocido en la subida';
        }
    }

    private function getPublicUrl($fileName) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $path = $_ENV['BASE_PATH'] ?? '';
        return $protocol . $host . "$path/public/uploads/images/$fileName";
    }

    public function deleteImage($imagePath) {
        $fullPath = $this->uploadDir . ltrim($imagePath, '/');
        
        if (file_exists($fullPath)) {
            return unlink(filename: $fullPath);
        }
        
        return false;
    }

    // Generar thumbnail
    public function createThumbnail($imagePath, $width = 300, $height = 300) {
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return false;
        
        $mime = $imageInfo['mime'];
        
        // Cargar imagen original
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($imagePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($imagePath);
                break;
            default:
                return false;
        }
        
        $originalWidth = imagesx($source);
        $originalHeight = imagesy($source);
        
        // Crear thumbnail
        $thumbnail = imagecreatetruecolor($width, $height);
        
        // Mantener transparencia para PNG
        if ($mime === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        
        // Guardar thumbnail
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case 'image/png':
                imagepng($thumbnail, $thumbnailPath, 9);
                break;
            case 'image/webp':
                imagewebp($thumbnail, $thumbnailPath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return $thumbnailPath;
    }
}
?>