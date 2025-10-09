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


    // ✅ NUEVO: Método para FormData
    protected function formData(array $fileNames)
    {
        $formData = $_POST;

        // Obtener campos de texto del FormData
        foreach ($formData as $key => $value) {
            $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }

        // return [...$data, $fileName => $_FILES[$fileName] ?? null];
        foreach ($fileNames as $fileName) {
            $data[$fileName] = $_FILES[$fileName] ?? null;
        }

        return $data;
    }

    

    protected function queryParam(string $key)
    {
        return $_GET[$key] ?? $_GET;
    }
}
?>