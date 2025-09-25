<?php

class AppResponse
{
    public static function success($data = null, $message = "Success", $code = 200)
    {
        http_response_code($code);

        $response = [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    public static function error($message = "Error", $code = 500, $details = null)
    {
        http_response_code($code);

        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        // Log del error
        error_log("API Error [$code]: $message" . ($details ? " - Details: " . json_encode($details) : ""));

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}
?>