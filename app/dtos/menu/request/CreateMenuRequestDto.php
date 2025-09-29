<?php
require_once "app/utils/ValidationEngine.php";
class CreateMenuRequestDto
{
    public $title;

    public $menuType;
    public $order;
    public $url;

    public $parentId;

    public $pageId;

    public function __construct($data)
    {
        $this->title = $data['title'] ?? '';
        $this->order = $data['order'] ?? 0;
        $this->url = $data['url'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
        $this->menuType = $data['menuType'] ?? '';
        $this->pageId = $data['pageId'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("title")
            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->required("order")
            ->integer("order")
            ->min("order", 1)
            ->maxLength("order", 100)
            ->required("menuType")
            ->enum("menuType", MenuTypes::class);

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        if ($this->menuType === MenuTypes::EXTERNAL_LINK->value) {
            $internalLinkValidation = new CreateInternalMenuRequestDto($this);
            return $internalLinkValidation->validate();
        }


        return $this;
    }

    public function toInsertDB(): array
    {
        $userId = $GLOBALS[AuthConst::CURRENT_USER]['user_id'];
        return [
            $this->title,
            $this->generateSlug($this->title),
            intval($this->order),
            $userId,
            $this->url,
            $this->parentId == null ? null : intval($this->parentId),
            $this->pageId == null ? null : intval($this->pageId)
        ];
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }

}
?>