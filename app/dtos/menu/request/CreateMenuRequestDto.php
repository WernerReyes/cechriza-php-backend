<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class CreateMenuRequestDto
{
    public $title;


    public $linkId;

    public $parentId;

    public $active;





    public function __construct($data)
    {
        $this->title = $data['title'] ?? '';
        $this->linkId = $data['linkId'] ?? 0;
        $this->parentId = $data['parentId'] ?? null;
        $this->active = $data['active'] ?? true;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->required("linkId")
            ->integer("linkId")
            ->min("linkId", 1)

            ->integer("parentId")
            ->min("parentId", 1)
            ->optional("parentId")

            ->boolean("active")
            ->optional("active");

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;
    }


    public function toInsertDB(): array
    {
        return [
            "title" => $this->title,
            "link_id" => intval($this->linkId),
            "parent_id" => $this->parentId == null ? null : intval($this->parentId),
            "active" => boolval($this->active) ? 1 : 0,
        ];
    }


}
?>