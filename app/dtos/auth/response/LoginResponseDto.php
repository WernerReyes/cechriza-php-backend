<?php

class LoginResponseDto
{
    public $user;
    public string $accessToken;

    public string $refreshToken;


    public function __construct(
        $user,
        string $accessToken,
        string $refreshToken
    ) {

        $this->user = $user;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

    }
}
?>