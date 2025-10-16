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

    public $fileImage;

    public $imageUrl;

    public $linkId;

    public $active;

    public $menusIds;

    public function __construct($data)
    {
        $this->title = $data["title"] ?? '';
        $this->type = $data["type"] ?? '';
        $this->pageId = $data["pageId"] ?? 0;
        $this->active = $data["active"] ? boolval($data["active"]) : true;
        $this->subtitle = $data["subtitle"] ?? null;
        $this->description = $data["description"] ?? null;
        $this->textButton = $data["textButton"] ?? null;
        $this->linkId = $data["linkId"] ?? null;
        $this->fileImage = $data["fileImage"] ?? null;
        $this->imageUrl = $data["imageUrl"] ?? null;
        $this->menusIds = $data["menusIds"] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 200)

            ->required("type")
            ->enum("type", SectionType::class)

            ->required("active")
            ->boolean("active")

            ->required("pageId")
            ->integer("pageId")
            ->min("pageId", 1)

            ->files("fileImage")
            ->optional("fileImage")

            ->pattern("imageUrl", PatternsConst::$URL)
            ->optional("imageUrl")

            ->array("menusIds")
            ->optional("menusIds");




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
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null; 
                break;

            case SectionType::WHY_US->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null;
                break;

            case SectionType::CASH_PROCESSING_EQUIPMENT->value:
                $this->subtitle = null;
                $this->description = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null;
                break;

            case SectionType::CLIENT->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->subtitle = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null;
                break;

            case SectionType::VALUE_PROPOSITION->value:
                $this->fileImage = null;
                $this->imageUrl = null;
                $this->description = null;
                $this->textButton = null;
                $this->linkId = null;
                $this->subtitle = null;
                $this->menusIds = null;
                break;

            case SectionType::OUR_COMPANY->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->subtitle = null;
                $this->menusIds = null;
                break;


            default:
                # code...
                break;
        }
    }

    public function toInsertDB($imageUrl = null): array
    {
        return [
            "title" => $this->title,
            "type" => $this->type,
            "active" => $this->active,
            "page_id" => $this->pageId,
            "subtitle" => $this->subtitle,
            "image"=> $imageUrl,
            "description" => $this->description,
            "text_button" => $this->textButton,
            "link_id" => $this->linkId
        ];
    }



}
?>