<?php
class UserEntity
{

    public int $id;
    public string $firstName;
    public string $lastName;
    public string $email;

    public string $role;

    function __construct($data)
    {
        $this->id = $data['id_user'] ?? null;
        $this->firstName = $data['name'] ?? '';
        $this->lastName = $data['lastname'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->role = $data['role'] ?? 'USER';
    }


}
?>