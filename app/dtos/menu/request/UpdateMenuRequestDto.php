<?php
require_once "app/utils/ValidationEngine.php";
class UpdateMenuRequestDto
{
    public $id;
    public $title;
    public $order;
    public $url;

    public $parentId;

    public function __construct($data)
    {
        $this->id = $data["id"];
        $this->title = $data['title'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->parentId = $data['parentId'] ?? null;
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

            ->integer("order")
            ->min("order", 1)
            ->maxLength("order", 100)
            ->optional("order")

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

    public function toUpdateDB(): array
    {
        $userId = $GLOBALS[AuthConst::CURRENT_USER]['user_id'];
        return [
            $this->id,
            $this->title,
            $this->generateSlug($this->title),
            intval($this->order),
            // $userId,
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