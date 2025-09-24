<?php
require_once "app/AppController.php";
require_once "app/services/AuthService.php";
require_once "app/models/User.php";
require_once "app/dtos/auth/request/RegisterRequestDto.php";
class AuthController extends AppController
{
    private AuthService $authService;
    public function __construct()
    {
        $this->authService = new AuthService(User::getInstance());
    }

    public function register()
    {
        $data = $this->body();
        $dto = new RegisterRequestDto($data);
        $dtoValidated = $dto->validate();
        if (is_string($dtoValidated)) {
            throw ApiException::validationError($dtoValidated);
        }

        $this->authService->register($dtoValidated);
    }
}
?>