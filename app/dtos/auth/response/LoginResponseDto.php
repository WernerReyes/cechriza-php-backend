<?php
require_once "app/entities/UserEntity.php";
class LoginResponseDto
{
    public UserEntity $user;
    public string $accessToken;

    public string $refreshToken;


    public function __construct(
        array $user,
        string $accessToken,
        string $refreshToken
    ) {

        $this->user = new UserEntity($user);
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

    }
}
?>