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


    public $machinesIds;

    public $mode; //* CUSTOM | LAYOUT

    public function __construct($data)
    {
        $this->title = $data["title"] ?? '';
        $this->type = $data["type"] ?? '';
        $this->pageId = $data["pageId"] ?? null;
        $this->active = $data["active"] ? boolval($data["active"]) : true;
        $this->subtitle = $data["subtitle"] ?? null;
        $this->description = $data["description"] ?? null;
        $this->textButton = $data["textButton"] ?? null;
        $this->linkId = $data["linkId"] ?? null;
        $this->fileImage = $data["fileImage"] ?? null;
        $this->imageUrl = $data["imageUrl"] ?? null;
        $this->menusIds = $data["menusIds"] ?? null;
        $this->machinesIds = $data["machinesIds"] ?? null;
        $this->mode = $data["mode"] ?? '';
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


            ->integer("pageId")
            ->min("pageId", 1)
            ->optional("pageId")

            ->files("fileImage")
            ->optional("fileImage")

            ->pattern("imageUrl", PatternsConst::$URL)
            ->optional("imageUrl")

            ->array("menusIds")
            ->optional("menusIds")

            ->array("machinesIds")
            ->optional("machinesIds")
            
            ->required("mode")
            ->enum("mode", SectionMode::class)
            ;




        if ($validation->fails()) {
            return $validation->getErrors();
        }

        if ($this->mode === SectionMode::LAYOUT->value) {
            $this->pageId = null;
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
                $this->machinesIds = null;
                break;

            case SectionType::WHY_US->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null;
                $this->machinesIds = null;
                break;

            case SectionType::CASH_PROCESSING_EQUIPMENT->value:
                $this->subtitle = null;
                $this->description = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null;
                // $this->machinesIds = null;
                break;

            case SectionType::CLIENT->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->subtitle = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->menusIds = null;
                $this->machinesIds = null;
                break;

            case SectionType::VALUE_PROPOSITION->value:
                $this->fileImage = null;
                $this->imageUrl = null;
                $this->description = null;
                $this->textButton = null;
                $this->linkId = null;
                $this->subtitle = null;
                $this->menusIds = null;
                $this->machinesIds = null;
                break;

            case SectionType::OUR_COMPANY->value:
                $this->textButton = null;
                $this->linkId = null;
                $this->subtitle = null;
                $this->menusIds = null;
                $this->machinesIds = null;
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
            "subtitle" => $this->subtitle,
            "image"=> $imageUrl,
            "description" => $this->description,
            "text_button" => $this->textButton,
            "link_id" => $this->linkId
        ];
    }



}
?>