<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/models/SectionModel.php";
class UpdateSectionRequestDto
{ 
    public $id;
    public $title;

    public $subtitle;


    public $description;

    public $type;
    public $textButton;

    public $linkId;

    public $active;

    public function __construct($data, $id)
    {
        $this->id = $id;
        $this->type = $data["type"] ?? '';
        $this->title = $data["title"] ?? null;
        $this->active = $data["active"] ?? null;
        $this->subtitle = $data["subtitle"] ?? null;
        $this->description = $data["description"] ?? null;
        $this->textButton = $data["textButton"] ?? null;
        $this->linkId = $data["linkId"] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("id")
            ->integer("id")
            ->min("id", 1)

            ->required("type")
            ->enum("type", SectionType::class)

            ->minLength("title", 2)
            ->maxLength("title", 200)
            ->optional("title")

            ->boolean("active")
            ->optional("active")
        ;

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toUpdateDB(): array
    {

        // return array_filter([
        //     "title" => $this->title !== null ? $this->title : null,
        //     "type" => $this->type,
        //     "active" => $this->active !== null ? $this->active : null,
        //     "subtitle" => $this->subtitle !== null ? $this->subtitle : null,
        //     "description" => $this->description !== null ? $this->description : null,
        //     "text_button" => $this->textButton !== null ? $this->textButton : null,
        //     "link_id" => $this->linkId !== null ? $this->linkId : null,
        // ], function ($value) {
        //     return $value !== null;
        // });

        return [
            "title" => $this->title,
            "type" => $this->type,
            "active" => $this->active,
            "subtitle" => $this->subtitle,
            "description" => $this->description,
            "text_button" => $this->textButton,
            "link_id" => $this->linkId
        ];
    }



}
?>