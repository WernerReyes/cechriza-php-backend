<?php
require_once "app/utils/ValidationEngine.php";
class LoginRequestDto
{
    public string $email;
    public string $password;

    public function __construct(
        $data
    ) {
        $this->email = $data["email"] ?? '';
        $this->password = $data["password"] ?? '';

    }

    public function validate(): LoginRequestDto | array
    {
        $validation = new ValidationEngine($this);
        $validation->required("email")
            ->email("email")
            ->required("password")
            ->minLength("password", 6);

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }



}

?>