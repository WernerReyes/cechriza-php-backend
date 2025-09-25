<?php
class MenuEntity
{
    public int $id;
    public string $title;

    public ?string $url;

    public string $slug;

    public int $order;

    public bool $active;

    public ?int $parentId;

    public int $userId;

    public function __construct(array $data)
    {
        $this->id = $data['id_menu'];
        $this->title = $data['title'];
        $this->url = $data['url'];
        $this->slug = $data['slug'];
        $this->order = $data['order'];
        $this->active = $data['active'] == 1 ? true : false;
        $this->parentId = $data['parent_id'];
        $this->userId = $data['users_id'];
    }

}

?>