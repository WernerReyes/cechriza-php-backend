<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class CreateExternalMenuRequestDto extends CreateMenuRequestDto
{


    public function __construct($data)
    {

        error_log("Constructing CreateMenuRequestDto with data: " . json_encode($data));
        parent::__construct($data);
    }

    public function validate()
    {
        // error_log("Constructing CreateMenuRequestDto with data: " . json_encode($this));
        // $validation = new ValidationEngine($this);
        // $validation->required("url")
        //     ->pattern("url", PatternsConst::$URL);



        // if ($validation->fails()) {
        //     return $validation->getErrors();
        // }

        // return $this;
    }

}
?>