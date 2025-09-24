<?php
require_once "app/utils/ValidationEngine.php";
class RegisterRequestDto
{

    public string $name;

    public string $lastname;

    public string $email;
    public string $password;


    public function __construct(
        $data
    ) {
        $this->name = $data["name"] ?? '';
        $this->lastname = $data["lastname"] ?? '';
        $this->email = $data["email"] ?? '';
        $this->password = $data["password"] ?? '';

    }

    

    public function validate(): RegisterRequestDto | string
    {
        $validation = new ValidationEngine($this);
        $validation->required("name")
            ->min("name", 2)
            ->max("name", 100)
            ->required("lastname")
            ->min("lastname", 2)
            ->max("lastname", 100)
            ->required("email")
            ->email("email")
            ->required("password")
            ->min("password", 6);

        if ($validation->fails()) {
            return $validation->getErrors()[0];
        }

        return $this;
    }

    public function toArrayWithHashedPassword(): array {
        return [
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'password' => password_hash($this->password, PASSWORD_DEFAULT),
            'role' => 'USER'
        ];
    }
}
?>