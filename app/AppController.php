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


    // protected function formData(array $fileNames)
    // {
    //     $formData = $_POST;
    //     $data = [];

    //     foreach ($formData as $key => $value) {
    //         if (is_array($value)) {
    //             // Si ya viene como array, convertir a enteros
    //             $data[$key] = array_map('intval', $value);
    //         } else {
    //             $trimmed = trim($value);

    //             // Detectar y convertir booleanos
    //             if (strcasecmp($trimmed, 'true') === 0) {
    //                 $data[$key] = true;
    //             } elseif (strcasecmp($trimmed, 'false') === 0) {
    //                 $data[$key] = false;
    //             }
    //             // Detectar y convertir numéricos
    //             elseif (is_numeric($trimmed)) {
    //                 // Si es un número entero
    //                 if (ctype_digit($trimmed)) {
    //                     $data[$key] = (int) $trimmed;
    //                 } else {
    //                     $data[$key] = (float) $trimmed;
    //                 }
    //             } else {
    //                 // Sanitizar string normal
    //                 $data[$key] = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
    //             }
    //         }
    //     }

    //     // Agregar archivos
    //     foreach ($fileNames as $fileName) {
    //         $data[$fileName] = $_FILES[$fileName] ?? null;
    //     }

    //     return $data;
    // }



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

    protected function formData(array $fileNames)
    {
        $formData = $_POST;
        $data = [];

        foreach ($formData as $key => $value) {

            // 🔹 Si es string y JSON válido → decodificar
            if (is_string($value) && $this->isJson($value)) {

                $data[$key] = json_decode($value, true);
                continue;
            }

            // 🔹 Si es array → procesar recursivamente
            if (is_array($value)) {
                $data[$key] = array_map(function ($item) use ($key) {
                  
                    if (is_string($item) && $this->isJson($item)) {
                        error_log("Parsing JSON field: " . $key . " Value: " . $item);
                        $item = json_decode($item, true);
                        // error_log("Parsed item: " . print_r($item, true));
                        // if (is_string($item) && ($tmp = json_decode($item, true)) !== null) {
                        //     $item = $tmp;
                        // }

                        return $item;

                    } elseif (is_array($item)) {
                        // ✅ Si es un subarray, decodificar internamente
                        return array_map(function ($subItem) {
                            if (is_string($subItem) && $this->isJson($subItem)) {
                                return json_decode($subItem, true);
                            }
                            return $subItem;
                        }, $item);
                    } elseif (is_numeric($item)) {
                        return ctype_digit($item) ? (int) $item : (float) $item;
                    } else {
                        return htmlspecialchars(trim($item), ENT_QUOTES, 'UTF-8');
                    }
                }, $value);
                continue;
            }

            // 🔹 Si es escalar
            $trimmed = trim($value);
            if (strcasecmp($trimmed, 'true') === 0) {
                $data[$key] = true;
            } elseif (strcasecmp($trimmed, 'false') === 0) {
                $data[$key] = false;
            } elseif (is_numeric($trimmed)) {
                $data[$key] = ctype_digit($trimmed) ? (int) $trimmed : (float) $trimmed;
            } else {
                $data[$key] = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
            }
        }

        // 🔹 Adjuntar archivos
       foreach ($fileNames as $fileName) {
    if (isset($_FILES[$fileName])) {
        $data[$fileName] = $this->normalizeFiles($_FILES[$fileName]);
    } else {
        $data[$fileName] = null;
    }
}

        return $data;
    }

    /**
     * Detecta si un string es JSON válido.
     */
    private function isJson($string)
    {
        if (!is_string($string))
            return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    private function normalizeFiles(array $files)
{
    // Si no hay archivos o el campo viene vacío
    if (empty($files) || !isset($files['name'])) {
        return [];
    }

    // Si es un solo archivo (no array)
    if (!is_array($files['name'])) {
        return [
            'name' => $files['name'],
            'type' => $files['type'],
            'tmp_name' => $files['tmp_name'],
            'error' => $files['error'],
            'size' => $files['size'],
        ];
    }

    // Si son múltiples archivos
    $normalized = [];
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        $normalized[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i],
        ];
    }

    return $normalized;
}



    protected function queryParam(string $key)
    {
        return $_GET[$key] ?? $_GET;
    }
}
?>