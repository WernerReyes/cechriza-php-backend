<?php
require_once "app/dtos/sectionItem/request/CreateSectionItemRequestDto.php";
class CreateSectionHeroRequestDto extends CreateSectionItemRequestDto
{

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->minLength("title", 2)
            ->optional("title")

            ->minLength("subtitle", 2)
            ->optional("subtitle")

            ->minLength("description", 2)
            ->optional("description")

            ->minLength("image", 2)
            ->optional("image")

            ->minLength("textButton", 2)
            ->optional("textButton")

            ->minLength("linkButton", 2)
            ->optional("linkButton")

            ->minLength("backgroundImage", 2)
            ->optional("backgroundImage")

        ;

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        $this->icon = null;
        $this->functionMachineId = null;

        return $this;

    }

}
