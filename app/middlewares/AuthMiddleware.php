<?php
// filepath: c:\xampp\htdocs\api\app\middleware\AuthMiddleware.php
require_once 'app/core/constants/AuthConst.php';
require_once 'app/utils/JwtUtil.php';
require_once 'app/utils/CookieUtil.php';
require_once 'app/AppException.php';




class AuthMiddleware
{

    public static function authenticate()
    {
        $token = CookieUtil::getJwtToken();

        if (!$token) {
            throw AppException::unauthorized("Authentication required");
        }

        try {
            $decoded = JwtUtil::verifyToken($token);

            // Hacer disponible los datos del usuario globalmente
            $GLOBALS[AuthConst::CURRENT_USER] = $decoded['data'];

            // Verificar si necesita refresh automático
            if (JwtUtil::shouldRefresh($token)) {
                // self::autoRefresh();
            }

            return $decoded['data'];

        } catch (Exception $e) {
            // Intentar refresh automático si el token expiró
            if (strpos($e->getMessage(), 'expired') !== false) {
                // return self::autoRefresh();
            }

            throw AppException::unauthorized("Invalid token: " . $e->getMessage());
        }
    }

    public static function requireRole($requiredRole)
    {
        $user = self::authenticate();

        if ($user['role'] !== $requiredRole) {
            throw AppException::forbidden("Insufficient permissions");
        }

        return $user;
    }

    private static function autoRefresh()
    {
        $refreshToken = CookieUtil::getRefreshTokenFromCookie();

        if (!$refreshToken) {
            throw AppException::unauthorized("Session expired, please login again");
        }

        try {
            require_once 'app/services/AuthService.php';
            require_once 'app/models/UserModel.php';

            $authService = new AuthService();
            $tokens = $authService->refreshToken();

            // Obtener datos del usuario del nuevo token
            $userPayload = JwtUtil::getUserFromToken($tokens->accessToken);
            $GLOBALS['current_user'] = $userPayload;

            return $userPayload;

        } catch (Exception $e) {
            CookieUtil::clearAuthCookies();
            throw AppException::unauthorized("Session expired, please login again");
        }
    }

    public static function getCurrentUser()
    {
        return $GLOBALS['current_user'] ?? null;
    }
}
?>