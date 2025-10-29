<?php
require_once 'app/utils/UuidUtil.php';
class FileUploader
{
    private $uploadDir;
    private $allowedExtensions;
    private $maxFileSize;
    private $allowedMimeTypes;

    public function __construct()
    {
        // $this->uploadDir = __DIR__ . '/../../public/uploads/images/';
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'text/html',
            'application/pdf',
        ];
    }

    private function uploadDir(string $folder) {
        return __DIR__ . '/../../public/uploads/' . $folder . '/';
    }

    public function uploadImage($file, $fromUrl = false)
    {
        try {
            // // Validar archivo
            // $validation = $this->validateFile($file);
            // if ($validation !== true) {
            //     throw new Exception($validation);
            // }

            // Crear directorio si no existe
            $targetDir = $this->uploadDir('images');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Generar nombre único
            $fileName = $this->generateFileName($file);
            $targetPath = $targetDir . $fileName;

            // Mover archivo
            // Mover archivo
if (is_uploaded_file($file['tmp_name'])) {
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Error al mover archivo subido');
    }
} else {
    // TODO: Check if there is an error with the uploaded file
    // En Windows, usar copy + unlink en lugar de rename
    if (!copy($file['tmp_name'], $targetPath)) {
        throw new Exception('Error al copiar archivo desde temporal');
    }
 
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

    public function uploadImageFromUrl($url)
{
    try {
        // Descargar imagen temporalmente
        $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            throw new Exception('No se pudo descargar la imagen desde la URL: ' . $url);
        }
        file_put_contents($tempFile, $imageData);

        // Detectar MIME real del archivo descargado
        $mime = mime_content_type($tempFile);

        // Asignar extensión según tipo MIME
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];
        $ext = $mimeToExt[$mime] ?? 'jpg';

        // Generar nombre seguro
        $safeName = uniqid('img_', true) . '.' . $ext;

        // Crear estructura tipo $_FILES
        $file = [
            'name' => $safeName,
            'type' => $mime,
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile),
        ];

       
        // Subir usando tu método existente
        $uploadResult = $this->uploadImage($file, true);

        // Eliminar archivo temporal
        unlink($tempFile);

        return $uploadResult;

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}


    public function getPathFromUrl($url, $folder = 'images')
    {
        
        $fileName = basename($url);
        return "/uploads/$folder/$fileName";
    }


    public function uploadFile($file)
    {
        try {
            // Validar archivo
            $validation = $this->validateFile($file, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt']);
            if ($validation !== true) {
                throw new Exception($validation);
            }

            // Crear directorio si no existe
            $targetDir = $this->uploadDir('files');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Generar nombre único
            $fileName = $this->generateFileName($file);
            $targetPath = $targetDir . $fileName;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], to: $targetPath)) {
                throw new Exception('Error al mover el archivo');
            }

            return [
                'success' => true,
                'filename' => $fileName,
                'path' => "/uploads/files/$fileName",
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


    


    

    public function validateFile($file, $allowExtensions = [])
    {

        error_log('Validating file: ' . json_encode($file));
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

        error_log('viendo: ' . $mimeType . '' . $file['name']);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return 'Tipo de archivo no válido';
        }

        if ($mimeType === 'text/html' || $mimeType === 'application/pdf') {
            return true;
        }

        // Verificar que sea una imagen real
        if ($mimeType !== 'image/svg+xml' && !getimagesize($file['tmp_name'])) {
            return 'El archivo no es una imagen válida';
        }

        return true;
    }

    private function generateFileName($file)
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Nombre único basado en timestamp y hash
        return UuidUtil::v4() . '.' . $extension;
    }

    private function optimizeImage($imagePath)
    {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo)
            return false;

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

        if (!$image)
            return false;

        $width = imagesx($image);
        $height = imagesy($image);

        // Redimensionar si es necesario
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int) ($width * $ratio);
            $newHeight = (int) ($height * $ratio);

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

    private function getUploadError($errorCode)
    {
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

    private function getPublicUrl($fileName, $folder = 'images')
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $path = $_ENV['BASE_PATH'] ?? '';
        return $protocol . $host . "$path/public/uploads/$folder/$fileName";
    }

    public function getUrl($imagePath, $folder = 'images')
    {
        if (empty($imagePath))
            return null;

        // Si ya es URL completa, devolverla
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        // Construir URL pública
        $fileName = basename($imagePath);
        return $this->getPublicUrl($fileName, $folder);
    }

    public function deleteImage($imagePath)
    {
        $fullPath = $this->uploadDir('images') . ltrim(basename($imagePath), '/');

        if (file_exists($fullPath)) {
            return unlink(filename: $fullPath);
        }

        return false;
    }

    public function deleteFile($filePath)
    {
        $fullPath = $this->uploadDir('files') . ltrim(basename($filePath), '/');

        if (file_exists($fullPath)) {
            return unlink(filename: $fullPath);
        }

        return false;
    }

    // Generar thumbnail
    public function createThumbnail($imagePath, $width = 300, $height = 300)
    {
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo)
            return false;

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