<?php
class AppController
{
    public function __construct()
    {

    }

    protected function body()
    {
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw AppException::internalServer('Error decoding JSON: ' . json_last_error_msg());
        }
        return $data;
    }


    protected function formData(array $fileNames)
    {
        $formData = $_POST;
        $data = [];

        foreach ($formData as $key => $value) {
            if (is_array($value)) {
                // Si ya viene como array, convertir a enteros
                $data[$key] = array_map('intval', $value);
            } else {
                $trimmed = trim($value);

                // Detectar y convertir booleanos
                if (strcasecmp($trimmed, 'true') === 0) {
                    $data[$key] = true;
                } elseif (strcasecmp($trimmed, 'false') === 0) {
                    $data[$key] = false;
                }
                // Detectar y convertir numéricos
                elseif (is_numeric($trimmed)) {
                    // Si es un número entero
                    if (ctype_digit($trimmed)) {
                        $data[$key] = (int) $trimmed;
                    } else {
                        $data[$key] = (float) $trimmed;
                    }
                } else {
                    // Sanitizar string normal
                    $data[$key] = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        // Agregar archivos
        foreach ($fileNames as $fileName) {
            $data[$fileName] = $_FILES[$fileName] ?? null;
        }

        return $data;
    }



    /**
     * Convierte un campo específico de JSON string a array
     */
    protected function parseJsonField(array $data, string $fieldName)
    {
        if (isset($data[$fieldName]) && is_string($data[$fieldName])) {
            $decoded = json_decode($data[$fieldName], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[$fieldName] = $decoded;
            }
        }
        return $data;
    }



    protected function queryParam(string $key)
    {
        return $_GET[$key] ?? $_GET;
    }
}
?>