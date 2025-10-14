<?php
class ChangePasswordRequestDto
{
    public $oldPassword;
    public $newPassword;

    public function __construct($body)
    {
        $this->oldPassword = $body["oldPassword"] ?? '';
        $this->newPassword = $body["newPassword"] ?? '';
    }

    public function validate(): ChangePasswordRequestDto|array
    {
        $validation = new ValidationEngine($this);
        $validation->
            required("oldPassword")
            ->minLength("oldPassword", 6)

            ->required("newPassword")
            ->minLength("newPassword", 6);

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }


}