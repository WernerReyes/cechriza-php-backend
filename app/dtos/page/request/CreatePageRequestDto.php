<?php
require_once "app/utils/ValidationEngine.php";
class CreatePageRequestDto
{
    public $title;
    public $description;

    public $menuId;

    public function __construct($data)
    {
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->menuId = $data['menuId'] ?? 0;

    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)

            ->minLength("description", 10)
            ->optional("description")

            ->min("menuId", 1)
            ->integer("menuId");



        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toInsertDB(): array
    {
        return [
            $this->title,
            $this->description,
            intval($this->menuId)
        ];
    }


}
?>
