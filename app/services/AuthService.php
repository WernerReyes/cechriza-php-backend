<?php
require_once "app/models/User.php";
class AuthService
{
    private User $userModel;

    public function __construct(
        User $userModel
    ) {
        $this->userModel = $userModel;
    }

    public function register(RegisterRequestDto $registerRequestDto)
    {
        $existUser = $this->userModel->getByEmail($registerRequestDto->email);
        if ($existUser)
            throw ApiException::badRequest("El usuario ya existe");

        $userCreated = $this->userModel->create($registerRequestDto->toArrayWithHashedPassword());
        return $userCreated;
    }
}
?>