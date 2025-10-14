<?php

class UserResponseDto
{
    public $id_user;
    public $name;
    public $lastname;
    public $email;
    public $role;
    public $profile;

    public $created_at;

    public $updated_at;

    public function __construct(
        $user
    ) {
        $fileUploader = new FileUploader();
        $this->id_user = $user->id_user;
        $this->name = $user->name;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->profile = isset($user->profile) ? $fileUploader->getUrl($user->profile) : null;
        $this->created_at = $user->created_at;
        $this->updated_at = $user->updated_at;
    }

}