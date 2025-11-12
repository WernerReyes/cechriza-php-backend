<?php
require_once "app/dtos/user/response/UserResponseDto.php";

class LoginResponseDto
{
    public UserResponseDto $user;
    public string $accessToken;

    public ?string $refreshToken;


    public function __construct(
        $user,
        string $accessToken,
        ?string $refreshToken
    ) {

        $this->user = new UserResponseDto($user);
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

    }
}
?>