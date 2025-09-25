<?php
require_once "app/models/UserModel.php";
require_once "app/entities/UserEntity.php";
require_once "app/utils/JwtUtil.php";
require_once "app/utils/CookieUtil.php";
require_once "app/core/constants/AuthConst.php";
require_once "app/dtos/auth/response/LoginResponseDto.php";
class AuthService
{
    private UserModel $userModel;

    public function __construct(

    ) {
        $this->userModel = UserModel::getInstance();
    }

    public function register(RegisterRequestDto $registerRequestDto): UserEntity
    {
        $existUser = $this->userModel->findByField(UserSearchField::EMAIL, $registerRequestDto->email);
        if ($existUser)
            throw AppException::badRequest("El usuario ya existe");

        $userCreated = $this->userModel->create($registerRequestDto->toInsertDB());
        return new UserEntity($userCreated);
    }

    public function login(LoginRequestDto $loginRequestDto): LoginResponseDto
    {
        $user = $this->userModel->findByField(UserSearchField::EMAIL, $loginRequestDto->email);
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

        return new LoginResponseDto($user, $token, $refreshToken);
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
            $user = $this->userModel->findByField(UserSearchField::ID, $userPayload['user_id']);
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
        $user = $this->userModel->findByField(UserSearchField::ID, $data['user_id']);
        if (!$user) {
            throw AppException::unauthorized("User no longer exists");
        }
        return new UserEntity($user);
    }
}
?>