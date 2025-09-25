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



    public function validate(): RegisterRequestDto | array
    {
        $validation = new ValidationEngine($this);
        $validation->required("name")
            ->minLength("name", 2)
            ->maxLength("name", 100)
            ->required("lastname")
            ->minLength("lastname", 2)
            ->maxLength("lastname", 100)
            ->required("email")
            ->email("email")
            ->required("password")
            ->minLength("password", 6);

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toInsertDB(): array
    {
        return [
            $this->name,
            $this->lastname,
            $this->email,
            password_hash($this->password, PASSWORD_DEFAULT),
            'USER'
        ];
    }
}
?>