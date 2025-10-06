<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/dtos/sectionItem/request/CreateSectionHeroRequestDto.php";
class CreateSectionItemRequestDto
{
    public $sectionType;

    public $sectionId;

    public $title;

    public $subtitle;

    public $content;

    public $fileImage;

    public $imageUrl;

    public $linkId;

    public $linkTexted;


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

            ->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)

            ->minLength("subtitle", 2)
            ->maxLength("subtitle", 150)
            ->optional("subtitle")

            ->minLength("content", 10)
            ->optional("content")

            ->files("fileImage")
            ->optional("fileImage")

            ->enum("imageUrl", PatternsConst::$URL)
            ->optional("imageUrl")

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

    public function toInsertDB($imageUrl): array
    {
        return [
            "section_id" => $this->sectionId,
            "title" => $this->title,
            "description" => $this->content,
            "subtitle" => $this->subtitle,
            "image" => $imageUrl,
            "link_id" => $this->linkId,
            "text_button" => $this->linkTexted,
        ];
    }

}