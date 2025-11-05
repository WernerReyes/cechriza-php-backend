<?php
require_once "app/utils/ValidationEngine.php";
class UpdateMenuRequestDto
{

    public $id;
    public $title;


    public $linkId;

    public $parentId;




    public function __construct($data, $id)
    {
        $this->id = $id;
        $this->title = $data['title'] ?? null;
        $this->linkId = $data['linkId'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
        $this->active = $data['active'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("id")
            ->integer("id")
            ->min("id", 1)

            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->optional("title")

            ->integer("linkId")
            ->min("linkId", 1)
            ->optional("linkId")

            ->integer("parentId")
            ->min("parentId", 1)
            ->optional("parentId");

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;
    }


    public function toUpdateDB(): array
    {
        return [
            "title" => $this->title === null ? null : $this->title,
            "link_id" => $this->linkId === null ? null : intval($this->linkId),
            "parent_id" => $this->parentId === null ? null : intval($this->parentId),
        ];
        
    }

}
?>