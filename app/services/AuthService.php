<?php
require_once "app/models/UserModel.php";
require_once "app/utils/JwtUtil.php";
require_once "app/utils/CookieUtil.php";
require_once "app/core/constants/AuthConst.php";
require_once "app/dtos/auth/response/LoginResponseDto.php";
require_once "app/dtos/user/response/UserResponseDto.php";
class AuthService
{

    private readonly FileUploader $fileUploader;

    public function __construct()
    {
        $this->fileUploader = new FileUploader();
    }

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
            throw AppException::unauthorized("Las credenciales son incorrectas");
        }
        // Generate JWT token or session here
        $tokenPayload = [
            'user_id' => $user['id_user'],
            'role' => $user['role']
        ];

        $token = JwtUtil::generateToken(payload: $tokenPayload);
        $refreshToken = JwtUtil::generateRefreshToken($tokenPayload);

        // Set in HttpOnly Cookie
        CookieUtil::setAuthCookies($token, $refreshToken);


        return new LoginResponseDto($user->setHidden(['password']), $token, $refreshToken);
    }

    public function relogin(): LoginResponseDto
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
        $user = UserModel::find($data['user_id']);
        
        if (!$user) {
            throw AppException::unauthorized("Las credenciales son incorrectas");
        }

        $token = CookieUtil::getJwtToken();
        $refreshToken = CookieUtil::getRefreshTokenFromCookie();

        return new LoginResponseDto($user->setHidden(['password']), $token, $refreshToken);
    }


    public function updateProfile(UpdateUserProfileDto $dto)
    {
        $id = $GLOBALS[AuthConst::CURRENT_USER]["user_id"];
        $user = UserModel::find($id);
        if (!$user) {
            throw AppException::notFound("User not found");
        }

        $profileUrl = $dto->currentProfileUrl;
        if ($dto->profileFile) {
            if ($user->profile) {
                // Eliminar la imagen anterior si existe
                $uploadResult = $this->fileUploader->deleteImage($user->profile);

            }

            $uploadResult = $this->fileUploader->uploadImage($dto->profileFile);
            $profileUrl = $uploadResult["path"];
        }

        $user->update($dto->toArray($profileUrl));

        return new UserResponseDto($user);
    }

    public function updatePassword(ChangePasswordRequestDto $dto) {
        $id = $GLOBALS[AuthConst::CURRENT_USER]["user_id"];
        $user = UserModel::find($id);
        if (!$user) {
            throw AppException::notFound("El usuario no fue encontrado");
        }

        // Verificar que la contraseña actual sea correcta
        if (!password_verify($dto->oldPassword, $user->password)) {
            throw AppException::unauthorized("La contraseña actual es incorrecta");
        }

        // Actualizar la contraseña
        $user->password = password_hash($dto->newPassword, PASSWORD_BCRYPT);
        $user->save();
    }
}
