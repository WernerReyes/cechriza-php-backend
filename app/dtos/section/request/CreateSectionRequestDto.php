<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/models/SectionModel.php";
class CreateSectionRequestDto
{
    public $title;

    public $subtitle;

    public $pageId;

    public $description;

    public $type;
    public $textButton;

    public $linkId;

    public $active;

    public function __construct($data, $image = null)
    {
        $this->title = $data["title"] ?? '';
        $this->type = $data["type"] ?? '';
        $this->pageId = $data["pageId"] ?? 0;
        $this->active = $data["active"] ?? true;
        $this->subtitle = $data["subtitle"] ?? null;
        $this->description = $data["description"] ?? null;
        $this->textButton = $data["textButton"] ?? null;
        $this->linkId = $data["linkId"] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)

            ->required("type")
            ->enum("type", SectionType::class)

            ->required("active")
            ->boolean("active")

            ->required("pageId")
            ->integer("pageId")
            ->min("pageId", 1);


        if ($validation->fails()) {
            return $validation->getErrors();
        }

        $this->setFieldForSectionType();

        return $this;
    }


    private function setFieldForSectionType()
    {
        switch ($this->type) {
            case SectionType::HERO->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->description = null;
                $this->subtitle = null;
                break;

            case SectionType::WHY_US->value:
                $this->textButton = null;
                $this->linkId = null;
                break;

            case SectionType::CASH_PROCESSING_EQUIPMENT->value:
                $this->subtitle = null;
                $this->description = null;
                break;

            default:
                # code...
                break;
        }
    }

    public function toInsertDB(): array
    {
        return [
            "title" => $this->title,
            "type" => $this->type,
            "active" => $this->active,
            "page_id" => $this->pageId,
            "subtitle" => $this->subtitle,
            "description" => $this->description,
            "text_button" => $this->textButton,
            "link_id" => $this->linkId
        ];
    }



}
?>