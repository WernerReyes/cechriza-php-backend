<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/dtos/sectionItem/request/CreateSectionHeroRequestDto.php";
class CreateSectionItemRequestDto
{
    public $sectionType;

    public $sectionId;


    public $order;

    public $title;

    public $subtitle;

    public $description;

    public $image;

    public $icon;

    public $textButton;

    public $linkButton;

    public $backgroundImage;

    public $functionMachineId;


    public function __construct($data)
    {
        $this->sectionType = $data["sectionType"] ?? '';
        $this->sectionId = $data['sectionId'] ?? 0;
        $this->order = $data['order'] ?? 0
        ;
        $this->title = $data['title'] ?? null;
        $this->subtitle = $data['subtitle'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->image = $data['image'] ?? null;
        $this->icon = $data['icon'] ?? null;
        $this->textButton = $data['textButton'] ?? null;
        $this->linkButton = $data['linkButton'] ?? null;
        $this->backgroundImage = $data['backgroundImage'] ?? null;
        $this->functionMachineId = $data['functionMachineId'] ?? null;
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

            ->required("order")
            ->integer("order")
            ->min("order", 1)
        ;

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        if ($this->sectionType == SectionType::HERO) {
            $createDto = new CreateSectionHeroRequestDto((array) $this);
            return $createDto->validate();
        }

        return $this;
    }

    public function toInsertDB(): array
    {
        return [
            intval($this->sectionId),
            intval($this->order),
            $this->title,
            $this->subtitle,
            $this->description,
            $this->image,
            $this->backgroundImage,
            $this->icon,
            $this->textButton,
            $this->linkButton,
            $this->functionMachineId !== null ? intval($this->functionMachineId) : null,
        ];
    }

}