<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class UpdateOrderRequestDto
{
    public $orderArray = [];

    public function __construct($data)
    {
        $this->orderArray = $data['orderArray'] ?? [];
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required('orderArray')
            ->array('orderArray')
            ->minItems('orderArray', 1)
            ->fieldsMatchInArray(['id', 'order', 'idPage'], $this->orderArray);


        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

}