<?php

class ApiException extends Exception {
    protected $statusCode;
    protected $details;

    private function __construct($message = "", $statusCode = 500, $details = null) {
        $this->statusCode = $statusCode;
        $this->details = $details;
        parent::__construct($message);
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getDetails() {
        return $this->details;
    }

    // Métodos estáticos para diferentes tipos de errores
    public static function badRequest($message = "Bad Request", $details = null) {
        return new self($message, 400, $details);
    }

    public static function unauthorized($message = "Unauthorized", $details = null) {
        return new self($message, 401, $details);
    }

    public static function forbidden($message = "Forbidden", $details = null) {
        return new self($message, 403, $details);
    }

    public static function notFound($message = "Not Found", $details = null) {
        return new self($message, 404, $details);
    }

    public static function validationError($message = "Validation Error", $details = null) {
        return new self($message, 422, $details);
    }

    public static function internalServer($message = "Internal Server Error", $details = null) {
        return new self($message, 500, $details);
    }
}
?>