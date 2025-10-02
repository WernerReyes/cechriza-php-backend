<?php
require_once "app/models/UserModel.php";
require_once "app/utils/JwtUtil.php";
require_once "app/utils/CookieUtil.php";
require_once "app/core/constants/AuthConst.php";
require_once "app/dtos/auth/response/LoginResponseDto.php";
class AuthService
{


    public function register(RegisterRequestDto $registerRequestDto)
    {
        $existUser = UserModel::where('email', $registerRequestDto->email)->first();
        if ($existUser)
            throw AppException::badRequest("El usuario ya existe");

        $userCreated = UserModel::create($registerRequestDto->toInsertDB());
        return $userCreated;
    }

    public function login(LoginRequestDto $loginRequestDto): LoginResponseDto
    {
        // $user = $this->userModel->findByField(UserSearchField::EMAIL, $loginRequestDto->email);
        $user = UserModel::where('email', $loginRequestDto->email)->first();
        if (!$user || !password_verify($loginRequestDto->password, $user['password'])) {
            throw AppException::unauthorized("Invalid email or password");
        }
        // Generate JWT token or session here
        $tokenPayload = [
            'user_id' => $user['id_user'],
            'role' => $user['role']
        ];

        $token = JwtUtil::generateToken($tokenPayload);
        $refreshToken = JwtUtil::generateRefreshToken($tokenPayload);

        // Set in HttpOnly Cookie
        CookieUtil::setAuthCookies($token, $refreshToken);

        error_log("user: $user");

        return new LoginResponseDto($user->setHidden(['password']), $token, $refreshToken);
    }

    public function refreshToken(): LoginResponseDto
    {
        $refreshToken = CookieUtil::getRefreshTokenFromCookie();

        if (!$refreshToken) {
            throw AppException::unauthorized("No refresh token found");
        }

        try {
            $decoded = JwtUtil::verifyToken($refreshToken);

            // Verificar que sea un refresh token
            if (!isset($decoded['type']) || $decoded['type'] !== 'refresh') {
                throw AppException::unauthorized("Invalid refresh token");
            }

            $userPayload = $decoded['data'];

            // Verificar que el usuario aún existe
            $user = UserModel::find($userPayload['user_id'])->setHidden(['password']);
            if (!$user) {
                throw AppException::unauthorized("User no longer exists");
            }

            // Generar nuevos tokens
            $newAccessToken = JwtUtil::generateToken($userPayload);
            $newRefreshToken = JwtUtil::generateRefreshToken($userPayload);

            // Actualizar cookies
            CookieUtil::setAuthCookies($newAccessToken, $newRefreshToken);

            return new LoginResponseDto($user, $newAccessToken, $newRefreshToken);

        } catch (Exception $e) {
            // Limpiar cookies si el refresh token es inválido
            CookieUtil::clearAuthCookies();
            throw AppException::unauthorized("Invalid refresh token: " . $e->getMessage());
        }
    }

    public function logout()
    {
        CookieUtil::clearAuthCookies();
    }

    public function me()
    {
        $data = $GLOBALS[AuthConst::CURRENT_USER];
        $user = UserModel::find($data['user_id'])->setHidden(['password']);
        if (!$user) {
            throw AppException::unauthorized("User no longer exists");
        }
        return $user;
    }
}
?>