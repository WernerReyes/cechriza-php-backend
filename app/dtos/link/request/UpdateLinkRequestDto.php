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

    public function __construct($data)
    {
        $this->id = $data['id'] ?? 0;
        $this->title = $data['title'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->pageId = $data['pageId'] ?? null;
        $this->openInNewTab = $data['openInNewTab'] ?? null;
        $this->url = $data['url'] ?? null;
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
        } else if (!empty($this->type) && $this->type === LinkType::EXTERNAL->value) {
            $validation
                ->minLength("url", 10)
                ->pattern("url", PatternsConst::$URL)
                ->required("url");
            $this->pageId = null;
        }

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toUpdateDB(): array
    {
        $data = array_filter(
            array_merge(
                $this->title !== null ? ["title" => $this->title] : [],
                $this->type !== null ? ["type" => $this->type] : [],
                $this->openInNewTab !== null ? ["new_tab" => $this->openInNewTab] : [],
            ),
            function ($value) {
                return $value !== null;
            }
        );



        return array_merge(
            $data,
            [
                "page_id" => $this->pageId !== null ? intval($this->pageId) : null,
                "url" => $this->url
            ]
        );

    }

}
?>