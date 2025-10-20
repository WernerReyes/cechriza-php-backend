<?php

class CookieUtil
{

    private static $expiry = 3600; // 1 hora por defecto

    public static function init()
    {
        self::$expiry = $_ENV['JWT_EXPIRY'];
    }

    /**
     * Configurar cookie segura para JWT
     */
    public static function setJwtCookie($token, $name = 'access_token')
    {
        self::init();
        $options = [
            'expires' => time() + self::$expiry,
            'path' => '/',
            'domain' => '', // Dejar vacío para el dominio actual
            'secure' => self::isHttps(), // Solo HTTPS en producción
            'httponly' => true, // No accesible desde JavaScript
            'samesite' => 'Strict' // Protección CSRF
        ];

        return setcookie($name, $token, $options);
    }

    /**
     * Configurar refresh token cookie (más duradero)
     */
    public static function setRefreshTokenCookie($refreshToken, $name = 'refresh_token')
    {
        self::init();
        $extraTime = 60; // 1 minuto extra
        $options = [
            'expires' => time() + self::$expiry + $extraTime,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ];

        return setcookie($name, $refreshToken, $options);
    }

    /**
     * Obtener JWT desde cookie
     */
    public static function getJwtFromCookie($name = 'access_token')
    {
        error_log('JWT from cookie: ' . json_encode($_COOKIE));
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Obtener refresh token desde cookie
     */
    public static function getRefreshTokenFromCookie($name = 'refresh_token')
    {
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Eliminar cookie JWT
     */
    public static function clearJwtCookie($name = 'access_token')
    {
        $options = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ];

        return setcookie($name, '', $options);
    }

    /**
     * Eliminar refresh token cookie
     */
    public static function clearRefreshTokenCookie($name = 'refresh_token')
    {
        $options = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ];

        return setcookie($name, '', $options);
    }

    /**
     * Verificar si la conexión es HTTPS
     */
    private static function isHttps()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Obtener JWT desde cookie o header Authorization
     */
    public static function getJwtToken()
    {
        // Prioridad: Cookie primero, luego Authorization header
        $token = self::getJwtFromCookie();

        if (!$token) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        return $token;
    }

    /**
     * Configurar múltiples cookies de autenticación
     */
    public static function setAuthCookies($accessToken, $refreshToken)
    {
        $result = [];

        $result['access'] = self::setJwtCookie($accessToken, 'access_token');
        $result['refresh'] = self::setRefreshTokenCookie($refreshToken, 'refresh_token');

        return $result;
    }

    /**
     * Limpiar todas las cookies de autenticación
     */
    public static function clearAuthCookies()
    {
        self::clearJwtCookie('access_token');
        self::clearJwtCookie('access_token');
        self::clearRefreshTokenCookie('refresh_token');
    }
}
?>