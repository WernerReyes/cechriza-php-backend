<?php
require_once "app/utils/ValidationEngine.php";
class CreateInternalMenuRequestDto extends CreateMenuRequestDto
{


    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("pageId")
            ->min("pageId", 1);



        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

}
?>