<?php
class UpdateUserProfileDto
{
    public $firstName;
    public $lastName;
    public $email;
    public $profileFile;

    public $currentProfileUrl;

    public function __construct($body)
    {
        $this->firstName = $body['firstName'] ?? null;
        $this->lastName = $body['lastName'] ?? null;
        $this->email = $body['email'] ?? null;
        $this->profileFile = $body['profileFile'] ?? null;
        $this->currentProfileUrl = $body['currentProfileUrl'] ?? null;
    }

    public function validate(): UpdateUserProfileDto|array
    {
        $validation = new ValidationEngine($this);
        $validation
            ->minLength("firstName", 2)
            ->maxLength("firstName", 100)
            ->optional("firstName")

            ->minLength("lastName", 2)
            ->maxLength("lastName", 100)
            ->optional("lastName")

            ->email("email")
            ->optional("email")

            ->files("profileFile")
            ->optional("profileFile");

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toArray(?string $profileUrl = null): array
    {
        return [
            'name' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'profile' => $profileUrl,
        ];
    }


}