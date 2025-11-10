<?php
require_once 'app/utils/UuidUtil.php';
use WebPConvert\WebPConvert;
use enshrined\svgSanitize\Sanitizer;
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
            'video/mp4',
            'video/avi',
            'video/mov',
        ];
    }

    private function uploadDir(string $folder)
    {
        return __DIR__ . '/../../public/uploads/' . $folder . '/';
    }

    public function uploadImage2($file)
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







    public function uploadImage3($file)
    {
        try {
            // Crear directorio si no existe
            $targetDir = $this->uploadDir('images');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Nombre original y ruta temporal
            $fileName = $this->generateFileName($file);
            $targetPath = $targetDir . $fileName;

            // Mover archivo subido al destino
            if (is_uploaded_file($file['tmp_name'])) {
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception('Error al mover archivo subido');
                }
            } else {
                if (!copy($file['tmp_name'], $targetPath)) {
                    throw new Exception('Error al copiar archivo desde temporal');
                }
            }

            // Obtener extensión
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Si es SVG, no convertir, solo sanitizar
            if ($ext === 'svg') {
                $this->sanitizeSvg($targetPath);
                $this->optimizeSvg($targetPath);
                return [
                    'success' => true,
                    'filename' => $fileName,
                    'path' => "/uploads/images/$fileName",
                    'full_path' => $targetPath,
                    'size' => filesize($targetPath),
                    'url' => $this->getPublicUrl($fileName)
                ];
            }

            // Convertir a WebP
            $webpName = preg_replace('/\.[a-zA-Z]+$/', '.webp', $fileName);
            $webpPath = $targetDir . $webpName;

            WebPConvert::convert($targetPath, $webpPath, [
                'quality' => 85,
                'method' => 6,
                'max-quality' => 90,
                'fail' => 'throw',
                'convert' => 'cwebp', // usa el binario si está instalado
            ]);

            // Eliminar el archivo original
            unlink($targetPath);

            return [
                'success' => true,
                'filename' => $webpName,
                'path' => "/uploads/images/$webpName",
                'full_path' => $webpPath,
                'size' => filesize($webpPath),
                'url' => $this->getPublicUrl($webpName)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }



    public function uploadImage(array $file, $multiple = false): array
{
    try {
        $targetDir = $this->uploadDir('images');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generar nombre único y ruta final
        $fileName = $this->generateFileName($file);
        $targetPath = $targetDir . $fileName;

        // Mover archivo temporal
        if (is_uploaded_file($file['tmp_name'])) {
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Error al mover el archivo subido.');
            }
        } else {
            if (!copy($file['tmp_name'], $targetPath)) {
                throw new Exception('Error al copiar el archivo temporal.');
            }
        }

        // Obtener extensión
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Si es SVG, solo sanitizar y optimizar SVG
        if ($ext === 'svg') {
            $this->sanitizeSvg($targetPath);
            $this->optimizeSvg($targetPath);
            return [
                'success' => true,
                'filename' => $fileName,
                'path' => "/uploads/images/$fileName",
                'full_path' => $targetPath,
                'url' => $this->getPublicUrl($fileName)
            ];
        }

        /**
         * ✅ Si el upload es de una máquina
         * — Se encola para optimizar después (no convierte aún)
         */
        if ($multiple !== false) {
            return [
                'success' => true,
                'filename' => $fileName,
                'path' => "/uploads/images/$fileName",
                'full_path' => $targetPath,
                'url' => $this->getPublicUrl($fileName),
                'pending_optimization' => true
            ];
        }

        /**
         * ⚙️ Si NO es de máquina, convertir directamente a WebP
         */
        $webpName = preg_replace('/\.[a-zA-Z]+$/', '.webp', $fileName);
        $webpPath = $targetDir . $webpName;

        WebPConvert::convert($targetPath, $webpPath, [
            'quality' => 85,
            'method' => 6,
            'max-quality' => 90,
            'fail' => 'throw',
            'convert' => 'cwebp',
        ]);

        // Eliminar original
        unlink($targetPath);

        return [
            'success' => true,
            'filename' => $webpName,
            'path' => "/uploads/images/$webpName",
            'full_path' => $webpPath,
            'url' => $this->getPublicUrl($webpName),
            'pending_optimization' => false
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
                'image/png' => 'png',
                'image/gif' => 'gif',
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
            $uploadResult = $this->uploadImage($file);

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


    public function uploadVideo($file)
    {
        $this->maxFileSize = 20 * 1024 * 1024; // 20MB
        try {
            // Validar archivo
            $validation = $this->validateFile($file, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);
            if ($validation !== true) {
                throw new Exception($validation);
            }

            // Crear directorio si no existe
            $targetDir = $this->uploadDir('videos');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Generar nombre único
            $fileName = $this->generateFileName($file);
            $targetPath = $targetDir . $fileName;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], to: $targetPath)) {
                throw new Exception('Error al mover el video');
            }

            return [
                'success' => true,
                'filename' => $fileName,
                'path' => "/uploads/videos/$fileName",
                'full_path' => $targetPath,
                'size' => filesize($targetPath),
                'url' => $this->getPublicUrl($fileName, 'videos')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }



    public function getPathFromUrl($url, $folder = 'images')
    {

        $fileName = basename($url);
        return "/uploads/$folder/$fileName";
    }

    public function getFullPathFromUrl($url, $folder = 'images')
    {
        $fileName = basename($url);
        return $this->uploadDir($folder) . $fileName;
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
        $sanitizer = new Sanitizer();
        $sanitizer->minify(true); // Opcional: elimina espacios innecesarios

        $dirtySVG = file_get_contents($filePath);
        $cleanSVG = $sanitizer->sanitize($dirtySVG);

        if ($cleanSVG) {
            file_put_contents($filePath, $cleanSVG);
        } else {
            // Si el archivo está demasiado dañado o es peligroso
            unlink($filePath);
            throw new RuntimeException('SVG inválido o potencialmente peligroso');
        }
    }


    private function optimizeSvg(string $filePath): void
    {
        // Escapar la ruta para evitar inyección
        $escapedPath = escapeshellarg($filePath);

        // Verificar si svgo está disponible
        $svgoPath = trim(shell_exec('which svgo'));

        if (!trim(shell_exec('which svgo'))) {
            error_log('⚠️ Advertencia: SVGO no está instalado en el servidor.');
            return;
        }


        // Construir comando seguro
        $cmd = "$svgoPath --input={$escapedPath} --output={$escapedPath} --config='{\"multipass\": true}'";

        // Ejecutar comando y capturar salida
        exec($cmd . ' 2>&1', $output, $resultCode);

        // Si falla la ejecución, registrar pero no lanzar excepción
        if ($resultCode !== 0) {
            error_log("Error al optimizar SVG con SVGO: " . implode("\n", $output));
        }
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

        if ($mimeType === 'video/mp4' || $mimeType === 'video/avi' || $mimeType === 'video/mov') {
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

    public function deleteVideo($videoPath)
    {
        $fullPath = $this->uploadDir('videos') . ltrim(basename($videoPath), '/');

        if (file_exists($fullPath)) {
            return unlink(filename: $fullPath);
        }
        return false;
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