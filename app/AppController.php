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
            throw ApiException::internalServer('Error decoding JSON: ' . json_last_error_msg());
        }
        return $data;
    }
}
?>