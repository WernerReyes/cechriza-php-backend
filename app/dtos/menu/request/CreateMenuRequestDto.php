<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/core/constants/PatternsConst.php";
class CreateMenuRequestDto
{
    public $title;

    public $menuType;
    public $order;
    public $url;

    public $parentId;

    public $pageId;

    public $active;

    public $dropdownArray = [];

    public function __construct($data)
    {
        $this->title = $data['title'] ?? '';
        $this->order = $data['order'] ?? 0;
        $this->url = $data['url'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
        $this->menuType = $data['menuType'] ?? '';
        $this->pageId = $data['pageId'] ?? null;
        $this->dropdownArray = $data['dropdownArray'] ?? [];
        $this->active = $data['active'] ?? true;
        
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

        if ($this->menuType === MenuTypes::INTERNAL_PAGE->value) {
            $validation->required("pageId")
                ->integer("pageId")
                ->min("pageId", 1);
            $this->url = null;

        } else if ($this->menuType === MenuTypes::EXTERNAL_LINK->value) {
            $validation->required("url")
                ->pattern("url", PatternsConst::$URL);
            $this->pageId = null;
        } else if ($this->menuType === MenuTypes::DROPDOWN->value) {
            $validation->required("dropdownArray")
                ->array("dropdownArray")
                ->minItems("dropdownArray", 1);
        }

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;
    }


    public function toInsertDB(): array
    {
        $userId = $GLOBALS[AuthConst::CURRENT_USER]['user_id'];
        // return [
        //     $this->title,
        //     $this->generateSlug($this->title),
        //     intval($this->order),
        //     $userId,
        //     $this->url,
        //     $this->parentId == null ? null : intval($this->parentId),
        //     $this->pageId == null ? null : intval($this->pageId)
        // ];
        return [
            "title" => $this->title,
            "slug" => $this->generateSlug($this->title),
            "order" => intval($this->order),
            "users_id" => $userId,
            "url" => $this->url,
            "parent_id" => $this->parentId == null ? null : intval($this->parentId),
            "pages_id" => $this->pageId == null ? null : intval($this->pageId),
            "active" => boolval($this->active) ? 1 : 0,
        ];
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }

}
?>