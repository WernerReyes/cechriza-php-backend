<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/models/LinkModel.php";
require_once "app/core/constants/PatternsConst.php";
class CreateLinkRequestDto
{

    public $title;
    public $type;

    public $pageId;

    public $openInNewTab;

    public $url;

    public function __construct($data)
    {

        $this->title = $data['title'] ?? '';
        $this->type = $data['type'] ?? '';
        $this->pageId = $data['pageId'] ?? null;
        $this->openInNewTab = $data['openInNewTab'] ?? false;
        $this->url = $data['url'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)

            ->required("type")
            ->enum("type", LinkType::class)

            ->required("openInNewTab")
            ->boolean("openInNewTab");

        if ($this->type === LinkType::PAGE->value) {
            $validation->required("pageId")
                ->integer("pageId")
                ->min("pageId", 1);
            $this->url = null;
        } else if ($this->type === LinkType::EXTERNAL->value) {
            $validation->required("url")
                ->minLength("url", 10)
                ->pattern("url", PatternsConst::$URL);
            $this->pageId = null;
        }

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toInsertDB(): array
    {
        return [
            "title" => $this->title,
            "type" => $this->type,
            "page_id" => $this->pageId ? intval($this->pageId) : null,
            "new_tab" => $this->openInNewTab,
            "url" => $this->url,
        ];
    }

}
?>