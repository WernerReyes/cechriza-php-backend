<?php
require_once "app/AppController.php";
require_once "app/services/AuthService.php";
require_once "app/middlewares/AuthMiddleware.php";
require_once "app/dtos/auth/request/RegisterRequestDto.php";
require_once "app/dtos/auth/request/LoginRequestDto.php";
require_once "app/dtos/auth/request/UpdateUserProfileDto.php";
require_once "app/dtos/auth/request/ChangePasswordRequestDto.php";
class AuthController extends AppController
{
    private AuthService $authService;
    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register()
    {
        $data = $this->body();
        $dto = new RegisterRequestDto($data);
        $dtoValidated = $dto->validate();
        if (is_array($dtoValidated)) {
            throw AppException::validationError("Validation failed", $dtoValidated);
        }
        return AppResponse::success($this->authService->register($dtoValidated));
    }

    public function login()
    {
        $data = $this->body();
        $dto = new LoginRequestDto($data);
        $dtoValidated = $dto->validate();
        if (is_array($dtoValidated)) {
            throw AppException::validationError("Validation failed", $dtoValidated);
        }
        // Implement login logic here
        return AppResponse::success($this->authService->login($dtoValidated));
    }

    public function relogin()
    {
        return AppResponse::success($this->authService->relogin());
    }

    public function logout()
    {
        $this->authService->logout();
        return AppResponse::success();
    }

    public function me()
    {
        return AppResponse::success($this->authService->me());
    }

    public function updateProfile()
    {
        $data = $this->formData(["profileFile"]);
        $dto = new UpdateUserProfileDto($data);
        $dtoValidated = $dto->validate();
        if (is_array($dtoValidated)) {
            throw AppException::validationError("Validation failed", $dtoValidated);
        }
        return AppResponse::success($this->authService->updateProfile($dtoValidated), "Perfil actualizado correctamente");
    }

    public function updatePassword()
    {
        $data = $this->body();
        $dto = new ChangePasswordRequestDto($data);
        $dtoValidated = $dto->validate();
        if (is_array($dtoValidated)) {
            throw AppException::validationError("Validation failed", $dtoValidated);
        }
        return AppResponse::success($this->authService->updatePassword($dtoValidated), "La contraseña ha sido actualizada correctamente");
    }
}
?>