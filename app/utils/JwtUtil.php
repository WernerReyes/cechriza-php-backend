<?php
// filepath: c:\xampp\htdocs\api\app\utils\JwtHelper.php
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtUtil
{
    private static $secretKey;
    private static $algorithm = 'HS256';
    private static $expiry = 3600; // 1 hora por defecto

    public static function init()
    {
        self::$secretKey = $_ENV['JWT_SECRET'];
        self::$expiry = $_ENV['JWT_EXPIRY'];
    }

    /**
     * Generar JWT token
     */
    public static function generateToken($payload)
    {
        self::init();

        $issuedAt = time();
        $expire = $issuedAt + self::$expiry;

        error_log('Expired: ' . $expire);

        $tokenPayload = [
            'iat' => $issuedAt,           // Issued at
            'exp' => $expire,             // Expiration time
            'nbf' => $issuedAt,           // Not before
            'iss' => $_SERVER['HTTP_HOST'] ?? 'localhost', // Issuer
            'data' => $payload            // Datos del usuario
        ];


        return JWT::encode($tokenPayload, self::$secretKey, self::$algorithm);
    }

    /**
     * Verificar JWT token
     */
    public static function verifyToken($token)
    {
        try {
            self::init();

            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));

            // Convertir objeto a array
            return json_decode(json_encode($decoded), true);

        } catch (ExpiredException $e) {
            throw new Exception('Token expired: ' . $e->getMessage());
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid token signature: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }

    /**
     * Obtener datos del payload sin verificar expiración (para refresh)
     */
    public static function decodeWithoutVerify($token)
    {
        try {
            self::init();

            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            return $payload;

        } catch (Exception $e) {
            throw new Exception('Cannot decode token: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si el token está próximo a expirar (para refresh automático)
     */
    public static function shouldRefresh($token, $refreshThreshold = 300)
    { // 5 minutos
        try {
            $payload = self::decodeWithoutVerify($token);
            $now = time();
            $expiration = $payload['exp'];

            return ($expiration - $now) <= $refreshThreshold;

        } catch (Exception $e) {
            return true; // Si no puede decodificar, necesita refresh
        }
    }

    /**
     * Extraer datos del usuario del token
     */
    public static function getUserFromToken($token)
    {
        $decoded = self::verifyToken($token);
        return $decoded['data'] ?? null;
    }

    /**
     * Generar refresh token (más duradero)
     */
    public static function generateRefreshToken($payload)
    {
        self::init();

       $issuedAt = time();
       $extraTime = 60; // 1 minuto extra
        $expire = $issuedAt + self::$expiry + $extraTime;

        $tokenPayload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'nbf' => $issuedAt,
            'iss' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'type' => 'refresh',
            'data' => $payload
        ];

        return JWT::encode($tokenPayload, self::$secretKey, self::$algorithm);
    }
}
?>