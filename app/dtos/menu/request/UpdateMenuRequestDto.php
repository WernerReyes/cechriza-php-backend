<?php
require_once "app/utils/ValidationEngine.php";
class UpdateMenuRequestDto
{

    public $menuId;
    public $title;

    public $menuType;
    public $order;
    public $url;

    public $parentId;

    public $pageId;

    public $active;

    public $dropdownArray = [];

    public function __construct($data, $id)
    {
        $this->menuId = $id ?? 0;
        $this->title = $data['title'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
        $this->menuType = $data['menuType'] ?? null;
        $this->pageId = $data['pageId'] ?? null;
        $this->dropdownArray = $data['dropdownArray'] ?? null;
        $this->active = $data['active'] ?? null;

    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("menuId")
            ->integer("menuId")
            ->min("menuId", 1)

            ->minLength("title", 2)
            ->maxLength("title", 100)
            ->optional("title")

            ->integer("order")
            ->min("order", 1)
            ->maxLength("order", 100)
            ->optional("order")

            ->required("menuType")
            ->enum("menuType", MenuTypes::class);


        if ($this->menuType === MenuTypes::INTERNAL_PAGE->value) {
            $validation
                ->integer("pageId")
                ->min("pageId", 1)
                ->optional("pageId");

            $this->url = null;

        } else if ($this->menuType === MenuTypes::EXTERNAL_LINK->value) {
            $validation
                ->pattern("url", PatternsConst::$URL)
                ->optional("url");
            $this->pageId = null;
        } else if ($this->menuType === MenuTypes::DROPDOWN->value) {
            $validation
                ->array("dropdownArray")
                ->minItems("dropdownArray", 1)
                ->optional("dropdownArray");
            $this->url = null;
            $this->pageId = null;
        }

        if ($validation->fails()) {
            return $validation->getErrors();
        }


        return $this;




    }

    public function toUpdateDB(): array
    {
        $array = [];
        $userId = $GLOBALS[AuthConst::CURRENT_USER]['user_id'];
        // return [
        //     "title" => $this->title,
        //     "slug" => $this->generateSlug($this->title),
        //     "order" => intval(value: $this->order),
        //     "users_id" => $userId,
        //     "url" => $this->url,
        //     "parent_id" => $this->parentId == null ? null : intval(value: $this->parentId),
        //     "pages_id" => $this->pageId == null ? null : intval(value: $this->pageId),
        //     "active" => boolval(value: $this->active) ? 1 : 0,
        // ];

        if ($this->title !== null) {
            $array['title'] = $this->title;
            $array['slug'] = $this->generateSlug($this->title);
        }

        if ($this->order !== null) {
            $array['order'] = intval(value: $this->order);
        }

        if ($this->active !== null) {
            $array['active'] = boolval(value: $this->active) ? 1 : 0;
        }

        $array['url'] = $this->url;
        $array['pages_id'] = $this->pageId == null ? null : intval(value: $this->pageId);
        $array['parent_id'] = $this->parentId == null ? null : intval(value: $this->parentId);
      
        


        return $array;
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }

}
?>