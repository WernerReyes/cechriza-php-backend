<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/dtos/sectionItem/request/CreateSectionHeroRequestDto.php";
require_once "app/core/constants/PatternsConst.php";
class CreateSectionItemRequestDto
{
    public $sectionType;

    public $sectionId;

    public $title;

    public $subtitle;

    public $content;

    public $fileImage;

    public $imageUrl;

    public $backgroundFileImage;

    public $backgroundImageUrl;

    public $linkId;

    public $linkTexted;

    public $fileIcon;

    public $fileIconUrl;

    public $inputType;

    public $categoryId;


    public function __construct($data)
    {
        $this->sectionType = $data["sectionType"] ?? '';
        $this->sectionId = $data['sectionId'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->subtitle = $data['subtitle'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->fileImage = $data['fileImage'] ?? null;
        $this->imageUrl = $data['imageUrl'] ?? null;
        $this->linkId = $data['linkId'] ?? null;
        $this->linkTexted = $data['linkTexted'] ?? null;
        $this->backgroundFileImage = $data['backgroundFileImage'] ?? null;
        $this->backgroundImageUrl = $data['backgroundImageUrl'] ?? null;
        $this->fileIcon = $data['fileIcon'] ?? null;
        $this->fileIconUrl = $data['fileIconUrl'] ?? null;
        $this->categoryId = $data['categoryId'] ?? null;
        $this->inputType = $data['inputType'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required('sectionType')
            ->enum('sectionType', SectionType::class)

            ->required("sectionId")
            ->integer("sectionId")
            ->min("sectionId", 1)


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

            ->integer("linkId")
            ->min("linkId", 1)
            ->optional("linkId")

            ->minLength("linkTexted", 2)
            ->maxLength("linkTexted", 100)
            ->optional("linkTexted")

            ->files("fileIcon", ['svg'])
            ->optional("fileIcon")

            ->pattern("fileIconUrl", PatternsConst::$URL)
            ->optional("fileIconUrl")
            
            ->integer("categoryId")
            ->min("categoryId", 1)
            ->optional("categoryId")

            ->enum("inputType", InputType::class)
            ->optional("inputType")
            ;


        // if ($this->sectionType === SectionType::HERO->value) {
        //     $this->fileIcon = null;
        //     $this->fileIconUrl = null;
        // } 

        switch ($this->sectionType) {
            case SectionType::HERO->value:
                $this->fileIcon = null;
                $this->fileIconUrl = null;
                $this->categoryId = null;
                $this->inputType = null;
                break;

            case SectionType::WHY_US->value:
                $this->subtitle = null;
                $this->fileImage = null;
                $this->imageUrl = null;
                $this->backgroundFileImage = null;
                $this->backgroundImageUrl = null;
                $this->linkId = null;
                $this->linkTexted = null;
                $this->categoryId = null;
                 $this->inputType = null;
                break;

            case SectionType::CASH_PROCESSING_EQUIPMENT->value:
                $this->subtitle = null;
                $this->content = null;
                $this->backgroundFileImage = null;
                $this->backgroundImageUrl = null;
                $this->fileImage = null;
                $this->imageUrl = null;
                $this->categoryId = null;
                 $this->inputType = null;
                break;

            case SectionType::VALUE_PROPOSITION->value:
                $this->fileImage = null;
                $this->imageUrl = null;
                $this->backgroundFileImage = null;
                $this->backgroundImageUrl = null;
                $this->linkId = null;
                $this->linkTexted = null;
                $this->fileIcon = null;
                $this->fileIconUrl = null;
                $this->categoryId = null;
                 $this->inputType = null;
                break;

            case SectionType::CLIENT->value:
                $this->title = null;
                $this->subtitle = null;
                $this->content = null;
                $this->backgroundFileImage = null;
                $this->backgroundImageUrl = null;
                $this->linkId = null;
                $this->linkTexted = null;
                $this->fileIcon = null;
                $this->fileIconUrl = null;
                $this->categoryId = null;
                 $this->inputType = null;
                break;

            case SectionType::CONTACT_TOP_BAR->value:
                $this->subtitle = null;
                $this->content = null;
                $this->backgroundFileImage = null;
                $this->backgroundImageUrl = null;
                $this->linkId = null;
                $this->linkTexted = null;
                $this->imageUrl = null;
                $this->fileImage = null;
                $this->categoryId = null;
                 $this->inputType = null;
                break;
        }

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;
    }

    public function toInsertDB($imageUrl = null, $backgroundImageUrl = null, $fileIconUrl = null): array
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
            "category_id" => $this->categoryId,
            "input_type" => $this->inputType,
        ];
    }

}