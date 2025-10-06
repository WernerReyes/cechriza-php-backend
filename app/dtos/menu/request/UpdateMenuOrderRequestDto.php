<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class UpdateMenuOrderRequestDto
{
    public $menuOrderArray = [];

    public function __construct($data)
    {
        $this->menuOrderArray = $data['menuOrderArray'] ?? [];
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required('menuOrderArray')
            ->array('menuOrderArray')
            ->minItems('menuOrderArray', 1)
            ->fieldsMatchInArray(['id', 'order', 'parentId'], $this->menuOrderArray);
       

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;
    }


    

    

}
?>