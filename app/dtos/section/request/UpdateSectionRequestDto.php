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

    public $extraTextButton;

    public $fileImage;

    public $imageUrl;

    public $currentImageUrl;

    public $pageId;

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


    public $additionalInfoList;

    public $fileVideo;

    public $currentVideoUrl;

    public function __construct($data, $id)
    {
        $this->id = $id;
        $this->type = $data["type"] ?? '';
        $this->title = $data["title"] ?? null;
        $this->active = $data["active"] ?? null;
        $this->subtitle = $data["subtitle"] ?? null;
        $this->description = $data["description"] ?? null;
        $this->textButton = $data["textButton"] ?? null;
        $this->extraTextButton = $data["extraTextButton"] ?? null;
        $this->linkId = $data["linkId"] ?? null;
        $this->extraLinkId = $data["extraLinkId"] ?? null;
        $this->fileImage = $data["fileImage"] ?? null;
        $this->imageUrl = $data["imageUrl"] ?? null;
        $this->currentImageUrl = $data["currentImageUrl"] ?? null;
        $this->menusIds = $data["menusIds"] ?? null;
        $this->pageId = $data["pageId"] ?? 0;
        $this->mode = $data["mode"] ?? '';
        $this->machinesIds = $data["machinesIds"] ?? null;
        $this->fileIcon = $data['fileIcon'] ?? null;
        $this->fileIconUrl = $data['fileIconUrl'] ?? null;
        $this->icon = $data['icon'] ?? null;
        $this->iconType = $data['iconType'] ?? null;
        $this->additionalInfoList = $data['additionalInfoList'] ?? null;
        $this->fileVideo = $data['fileVideo'] ?? null;
        $this->currentVideoUrl = $data['currentVideoUrl'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("id")
            ->integer("id")
            ->min("id", 1)

            // ->required("pageId")
            ->integer("pageId")
            ->min("pageId", 1)
            ->optional("pageId")

            ->required("type")
            ->enum("type", SectionType::class)

            ->minLength("title", 2)
            ->maxLength("title", 200)
            ->optional("title")

            ->boolean("active")
            ->optional("active")

            ->files("fileImage")
            ->optional("fileImage")

            ->pattern("imageUrl", PatternsConst::$URL)
            ->optional("imageUrl")

            ->array("menusIds")
            ->optional("menusIds")

            ->array("machinesIds")
            ->optional("machinesIds")

            ->files("fileIcon", ['svg'])
            ->optional("fileIcon")

            ->pattern("fileIconUrl", PatternsConst::$URL)
            ->optional("fileIconUrl")


            ->enum("inputType", InputType::class)
            ->optional("inputType")

            ->enum("iconType", IconType::class)
            ->optional("iconType")

            // ->files("fileVideo", ['mp4', 'mov', 'avi'])
            ->optional("fileVideo")

            ->pattern("currentVideoUrl", PatternsConst::$URL)
            ->optional("currentVideoUrl")

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


        return $this;
    }

    public function toUpdateDB($image = null, $fileIconUrl = null, $fileVideoUrl = null): array
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
            "subtitle" => $this->subtitle,
            "image" => $image,
            "description" => $this->description,
            "text_button" => $this->textButton,
            "extra_text_button" => $this->extraTextButton,
            "link_id" => $this->linkId,
            "extra_link_id" => $this->extraLinkId,
            "icon_url" => $fileIconUrl,
            "video" => $fileVideoUrl,
            "icon_type" => $this->iconType,
            "icon" => $this->icon ? json_encode($this->icon) : null,
            "additional_info_list" =>  $this->additionalInfoList ? json_encode(array_map(function ($info) {
                return [
                    'id' => UuidUtil::v4(),
                    'label' => $info['label'],
                ];
            }, $this->additionalInfoList)) : null,

        ];
    }



}
?>