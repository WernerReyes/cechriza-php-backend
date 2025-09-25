<?php
class PageEntity
{
    public int $id;

    public string $title;

    public ?string $description;

    public bool $active;

    public int $menuId;

    public function __construct(array $data)
    {
        $this->id = $data['id_pages'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->active = $data['active'];
        $this->menuId = $data['menu_id'];
    }
}
?>