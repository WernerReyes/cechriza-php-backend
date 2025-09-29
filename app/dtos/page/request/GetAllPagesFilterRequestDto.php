<?php
require_once "app/utils/ValidationEngine.php";
class GetAllPagesFilterRequestDto
{
    public $freePagesOnly = false;

    public function __construct($data)
    {
        $this->freePagesOnly = $data["freePagesOnly"] ?? false;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->boolean("free_pages");
        $validation->optional("free_pages");


        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toInsertDB(): array
    {
        return [
            boolval($this->freePagesOnly)
        ];
    }


}
?>
