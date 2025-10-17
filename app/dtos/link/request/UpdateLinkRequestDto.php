<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/models/LinkModel.php";
require_once "app/core/constants/PatternsConst.php";
class UpdateLinkRequestDto
{

    public $id;
    public $title;
    public $type;

    public $pageId;

    public $openInNewTab;

    public $url;

    public $file;

    

    public function __construct($data, $id)
    {
        $this->id = $id;
        $this->title = $data['title'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->pageId = $data['pageId'] ?? null;
        $this->openInNewTab = $data['openInNewTab'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->file = $data['file'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("id")
            ->integer("id")
            ->min("id", 1)

            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->optional("title")

            ->enum("type", LinkType::class)
            ->optional("type")

            ->boolean("openInNewTab")
            ->optional("openInNewTab");

        if (!empty($this->type) && $this->type === LinkType::PAGE->value) {
            $validation
                ->integer("pageId")
                ->min("pageId", 1)
                ->required("pageId");
            $this->url = null;
            $this->file = null;
        } else if (!empty($this->type) && $this->type === LinkType::EXTERNAL->value) {
            $validation
                ->minLength("url", 10)
                ->pattern("url", PatternsConst::$URL)
                ->required("url");
            $this->pageId = null;
            $this->file = null;
        } else if (!empty($this->type) && $this->type === LinkType::FILE->value) {
            $validation
                ->files("file", ['pdf']);
            $this->pageId = null;
            $this->url = null;
        }

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toUpdateDB($fileUrl = null): array
    {

        return [
            "title" => $this->title,
            "type" => $this->type,
            "file_path" => $fileUrl,
            "page_id" => $this->pageId !== null ? intval($this->pageId) : null,
            "new_tab" => $this->openInNewTab,
            "url" => $this->url,
        ];

    }

}
?>