<?php
require_once 'app/AppException.php';
require_once 'app/AppResponse.php';

class ErrorHandler {
    
    public static function register() {
        // Manejador de errores PHP
        set_error_handler([self::class, 'handleError']);
        
        // Manejador de excepciones no capturadas
        set_exception_handler([self::class, 'handleException']);
        
        // Manejador de errores fatales
        register_shutdown_function([self::class, 'handleFatalError']);
    }
    
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorDetails = [
            'type' => 'PHP Error',
            'severity' => $severity,
            'file' => $file,
            'line' => $line
        ];
        
        AppResponse::error($message, 500, $errorDetails);
    }
    
    public static function handleException($exception) {

         if ($exception instanceof AppException) {
            AppResponse::error(
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getDetails()
            );
            return;
        }

        $errorDetails = [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        // Diferentes códigos según el tipo de excepción
        $code = 500;
        if ($exception instanceof InvalidArgumentException) {
            $code = 400;
        } elseif ($exception instanceof PDOException) {
            $code = 500;
            $errorDetails['type'] = 'Database Error';
        }
        
        AppResponse::error($exception->getMessage(), $code, $errorDetails);
    }
    
    public static function handleFatalError() {
        $error = error_get_last();
        
        if ($error !== null && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $errorDetails = [
                'type' => 'Fatal Error',
                'file' => $error['file'],
                'line' => $error['line']
            ];
            
            AppResponse::error($error['message'], 500, $errorDetails);
        }
    }
}
?>