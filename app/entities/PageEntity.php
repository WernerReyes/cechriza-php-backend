<?php
class PageEntity
{
    public int $id;

    public string $title;

    public ?string $description;

    public bool $active;

    public ?int $menuId;

    public \DateTime $createdAt;
    public \DateTime $updatedAt;

    public ?int $sectionCount;

   
    public function __construct(array $data)
    {
        $this->id = $data['id_pages'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->active = $data['active'] ? true : false;
        $this->menuId = $data['menu_id'] ?? null;
        $this->createdAt = new \DateTime($data['created_at']);
        $this->updatedAt = new \DateTime($data['updated_at']);
        $this->sectionCount = $data['section_count'] ?? null;
    }

}
