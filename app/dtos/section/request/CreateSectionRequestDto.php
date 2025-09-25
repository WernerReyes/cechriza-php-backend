<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/models/SectionModel.php";
class CreateSectionRequestDto
{
    public $order;
    public $type;
    public $title;

    public $subtitle;

    public $description;

    public $textButton;

    public $urlButton;

    public $pageId;

    public function __construct($data)
    {
        $this->order = $data['order'] ?? 0;
        $this->type = $data['type'] ?? '';
        $this->title = $data['title'] ?? null;
        $this->subtitle = $data['subtitle'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->textButton = $data['textButton'] ?? null;
        $this->urlButton = $data['urlButton'] ?? null;
        $this->pageId = $data['pageId'] ?? 0;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("order")
            ->integer("order")
            ->min("order", 1)

            ->required("type")
            ->enum("type", SectionType::class)

            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->optional("title")

            ->minLength("subtitle", 2)
            ->maxLength("subtitle", 100)
            ->optional("subtitle")

            ->minLength("description", 2)
            ->maxLength("description", 1000)
            ->optional("description")

            ->minLength("textButton", 2)
            ->maxLength("textButton", 100)
            ->optional("textButton")

            ->minLength("urlButton", 2)
            ->maxLength("urlButton", 100)
            ->optional("urlButton")

            ->required("pageId")
            ->integer("pageId")
            ->min("pageId", 1);

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toInsertDB(): array
    {
        return [
            $this->order,
            $this->type,
            $this->title,
            $this->subtitle,
            $this->description,
            $this->textButton,
            $this->urlButton,
            intval($this->pageId)
        ];
    }



}
?>