<?php

use Illuminate\Database\Eloquent\Collection;
require_once "app/dtos/sectionItem/response/SectionItemResponseDto.php";
class SectionResponseDto
{

    public int $id_section;
    public int $order_num;
    public string $type;
    public ?string $title;
    public ?string $subtitle;
    public ?string $description;
    public ?string $text_button;
    public ?int $link_id;
    public bool $active;
    public ?string $image;
    public int $page_id;

    public $section_items;
    public $link;

    public ?Collection $menus;

    public function __construct($data)
    {
        $fileUploader = new FileUploader();
        $this->id_section = $data->id_section;
        $this->order_num = isset($data->order_num) ? $data->order_num : null;
        $this->type = $data->type;
        $this->title = isset($data->title) ? $data->title : null;
        $this->subtitle = isset($data->subtitle) ? $data->subtitle : null;
        $this->description = isset($data->description) ? $data->description : null;
        $this->text_button = isset($data->text_button) ? $data->text_button : null;
        $this->link_id = isset($data->link_id) ? $data->link_id : null;
        $this->active = isset($data->active) ? $data->active : null;
        $this->image = isset($data->image) ? $fileUploader->getUrl($data->image) : null;
        $this->page_id = isset($data->page_id) ? $data->page_id : null;
        $this->section_items = isset($data->sectionItems) ? $data->sectionItems->map(fn($item) => new SectionItemResponseDto($item)) : null;
        $this->link = isset($data->link) ? $data->link : null;
        $this->menus = isset($data->menus) ? $data->menus : null;
    }
}
