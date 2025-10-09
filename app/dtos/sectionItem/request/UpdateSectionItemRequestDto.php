<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/dtos/sectionItem/request/CreateSectionHeroRequestDto.php";
require_once "app/core/constants/PatternsConst.php";
class UpdateSectionItemRequestDto
{

    public $id;
    public $sectionType;

    public $sectionId;

    public $title;

    public $subtitle;

    public $content;

    public $fileImage;

    public $imageUrl;

    public $backgroundFileImage;

    public $backgroundImageUrl;

    public $currentImageUrl;

    public $currentBackgroundImageUrl;


    public $linkId;

    public $linkTexted;


    public function __construct($data, $id)
    {
        $this->id = $id ?? 0;
        $this->sectionType = $data["sectionType"] ?? '';
        $this->sectionId = $data['sectionId'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->subtitle = $data['subtitle'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->fileImage = $data['fileImage'] ?? null;
        $this->imageUrl = $data['imageUrl'] ?? null;
        $this->linkId = $data['linkId'] ?? null;
        $this->linkTexted = $data['linkTexted'] ?? null;
        $this->backgroundFileImage = $data['backgroundFileImage'] ?? null;
        $this->backgroundImageUrl = $data['backgroundImageUrl'] ?? null;
        $this->currentImageUrl = $data['currentImageUrl'] ?? null;
        $this->currentBackgroundImageUrl = $data['currentBackgroundImageUrl'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required('id')
            ->integer('id')
            ->min('id', 1)

            ->required('sectionType')
            ->enum('sectionType', SectionType::class)

            ->integer("sectionId")
            ->min("sectionId", 1)
            ->optional("sectionId")

            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->optional("title")

            ->minLength("subtitle", 2)
            ->maxLength("subtitle", 150)
            ->optional("subtitle")

            ->minLength("content", 10)
            ->optional("content")

            ->files("fileImage")
            ->optional("fileImage")

            ->pattern("imageUrl", PatternsConst::$URL)
            ->optional("imageUrl")

            ->files("backgroundFileImage")
            ->optional("backgroundFileImage")

            ->pattern("backgroundImageUrl", PatternsConst::$URL)
            ->optional("backgroundImageUrl")

            ->pattern("currentImageUrl", PatternsConst::$URL)
            ->optional("currentImageUrl")

            ->pattern("currentBackgroundImageUrl", PatternsConst::$URL)
            ->optional("currentBackgroundImageUrl")

            ->integer("linkId")
            ->min("linkId", 1)
            ->optional("linkId")

            ->minLength("linkTexted", 2)
            ->maxLength("linkTexted", 100)
            ->optional("linkTexted")

        ;

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;
    }

    public function toUpdateDB($imageUrl = null, $backgroundImageUrl = null, $fileIconUrl = null): array
    {
        return [
            "section_id" => $this->sectionId,
            "title" => $this->title,
            "description" => $this->content,
            "subtitle" => $this->subtitle,
            "image" => $imageUrl,
            "background_image" => $backgroundImageUrl,
            "icon" => $fileIconUrl,
            "link_id" => $this->linkId,
            "text_button" => $this->linkTexted,
        ];
    }

}