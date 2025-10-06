<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class UpdatePageRequestDto
{
    public $id;
    public $title;
    public $description;

    public $slug;

    public function __construct($data, $id)
    {
        $this->id = $id;    
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->slug = $data['slug'] ?? null;

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

            ->pattern("slug", PatternsConst::$SLUG)
            ->minLength("slug", 2)
            ->maxLength("slug", 100)
            ->optional("slug")

            ->minLength("description", 10)
            ->optional("description");


        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toUpdateDB(): array
    {
        return array_filter([
            "title" => $this->title !== null ? $this->title : null,
            "description" => $this->description !== null ? $this->description : null,
            "slug" => $this->slug !== null ? $this->slug : null,
        ], function ($value) {
            return $value !== null;
        });
    }


}
?>