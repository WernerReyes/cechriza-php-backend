<?php
require_once "app/utils/ValidationEngine.php";
class CreateMenuRequestDto
{
    public $title;
    public $order;
    public $url;

    public $parentId;

    public function __construct($data)
    {
        $this->title = $data['title'] ?? '';
        $this->order = $data['order'] ?? 0;
        $this->url = $data['url'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
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
            ->minLength("url", 2)
            ->maxLength("url", 255)
            ->optional("url")
            ->min("parentId", 1)
            ->integer("parentId")
            ->optional("parentId");
        


        if ($validation->fails()) {
            return $validation->getErrors();
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
            $this->parentId
        ];
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }

}
?>