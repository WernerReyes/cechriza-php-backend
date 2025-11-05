<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class CreateMenuRequestDto
{
    public $title;


    public $linkId;

    public $parentId;

   




    public function __construct($data)
    {
        $this->title = $data['title'] ?? '';
        $this->linkId = $data['linkId'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)

         
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


    public function toInsertDB(): array
    {
        return [
            "title" => $this->title,
            "link_id" => $this->linkId === null ? null : intval($this->linkId),
            "parent_id" => $this->parentId == null ? null : intval($this->parentId),
        ];
    }


}
?>