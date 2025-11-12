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

    public $extraTextButton;

    public $fileImage;

    public $imageUrl;

    public $linkId;

    public $extraLinkId;

    public $active;

    public $menusIds;

    public $machinesIds;

    public $mode; //* CUSTOM | LAYOUT

    public $icon;

    public $iconType;

    public $fileIcon;

    public $fileIconUrl;

    public $inputType;

    public $additionalInfoList;

    public $fileVideo;


    public function __construct($data)
    {
        $this->title = $data["title"] ?? '';
        $this->type = $data["type"] ?? '';
        $this->pageId = $data["pageId"] ?? null;
        $this->active = $data["active"] ? boolval($data["active"]) : true;
        $this->subtitle = $data["subtitle"] ?? null;
        $this->description = $data["description"] ?? null;
        $this->textButton = $data["textButton"] ?? null;
        $this->extraTextButton = $data["extraTextButton"] ?? null;
        $this->linkId = $data["linkId"] ?? null;
        $this->extraLinkId = $data["extraLinkId"] ?? null;
        $this->fileImage = $data["fileImage"] ?? null;
        $this->imageUrl = $data["imageUrl"] ?? null;
        $this->menusIds = $data["menusIds"] ?? null;
        $this->machinesIds = $data["machinesIds"] ?? null;
        $this->mode = $data["mode"] ?? '';
        $this->icon = $data['icon'] ?? null;
        $this->iconType = $data['iconType'] ?? null;
        $this->fileIcon = $data['fileIcon'] ?? null;
        $this->fileIconUrl = $data['fileIconUrl'] ?? null;
        $this->inputType = $data['inputType'] ?? null;
        $this->additionalInfoList = $data['additionalInfoList'] ?? null;
        $this->fileVideo = $data['fileVideo'] ?? null;
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

            ->enum("iconType", IconType::class)
            ->optional("iconType")

            ->files("fileIcon", ['svg'])
            ->optional("fileIcon")

            ->pattern("fileIconUrl", PatternsConst::$URL)
            ->optional("fileIconUrl")

            // ->files("fileVideo", ['mp4', 'mov', 'avi'])
            ->optional("fileVideo")

            ->enum("inputType", InputType::class)
            ->optional("inputType")

            ->array("additionalInfoList")
            ->fieldsMatchInArray(['label'], $this->additionalInfoList)
            ->optional("additionalInfoList");
        ;




        if ($validation->fails()) {
            return $validation->getErrors();
        }

        if ($this->mode === SectionMode::LAYOUT->value) {
            $this->pageId = null;
        }

        $this->setFieldForSectionType();

        if ($this->iconType === IconType::IMAGE->value) {
            $this->icon = null;
        } else if ($this->iconType === IconType::LIBRARY->value) {
            $this->fileIcon = null;
            $this->fileIconUrl = null;
        }

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
                // $this->subtitle = null;
                $this->menusIds = null;
                $this->machinesIds = null;
                break;


            default:
                # code...
                break;
        }
    }

    public function toInsertDB($imageUrl = null, $fileIconUrl = null, $fileVideoUrl = null): array
    {
        return [
            "title" => $this->title,
            "type" => $this->type,
            "subtitle" => $this->subtitle,
            "image" => $imageUrl,
            "description" => $this->description,
            "text_button" => $this->textButton,
            "extra_text_button" => $this->extraTextButton,
            "link_id" => $this->linkId,
            "extra_link_id" => $this->extraLinkId,
            "icon_url" => $fileIconUrl,
            "icon_type" => $this->iconType,
            "icon" => $this->icon ? json_encode($this->icon) : null,
            "video" => $fileVideoUrl,
            "additional_info_list" => $this->additionalInfoList ? json_encode(array_map(function ($info) {
                return [
                    'id' => UuidUtil::v4(),
                    'label' => $info['label'],
                ];
            }, $this->additionalInfoList)) : null,
        ];
    }



}
?>