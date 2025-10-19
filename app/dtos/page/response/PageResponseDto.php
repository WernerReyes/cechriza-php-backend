<?php
class PageResponseDto
{
    public $page_id;
    public $title;
    public $slug;
    public $description;
    public $link_id;

    public $created_at;

    public $updated_at;

    public $sections;

    public function __construct(PageModel $data)
    {
        $this->page_id = $data->id_page;
        $this->title = $data->title;
        $this->slug = $data->slug;
        $this->description = $data->description;
        $this->link_id = $data->link_id;
        $this->created_at = $data->created_at;
        $this->updated_at = $data->updated_at;
        $this->sections = isset($data->sections) ? $data->sections->map(fn($section) => new SectionResponseDto($section)) : null;
    }
}


