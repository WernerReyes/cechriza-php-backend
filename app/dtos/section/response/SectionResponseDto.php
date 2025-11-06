<?php

use Illuminate\Database\Eloquent\Collection;
require_once "app/dtos/sectionItem/response/SectionItemResponseDto.php";
require_once "app/dtos/menu/response/MenuResponseDto.php";
class SectionResponseDto
{

    public int $id_section;
    // public int $order_num;
    public string $type;
    public ?string $title;
    public ?string $subtitle;
    public ?string $description;
    public ?string $text_button;

    public ?string $extra_text_button;
    public ?int $link_id;

    public ?int $extra_link_id;
    // public bool $active;
    public ?string $image;
    // public int $page_id;

    public $section_items;
    public $link;

    public $extra_link;


    public $pivot_pages;

    public $pages;

    public $menus;

    public $machines;

    public ?string $icon_url;
    public $icon;

    public $icon_type;

    public $additional_info_list;

    public $video;

    public function __construct($data)
    {
        $fileUploader = new FileUploader();
        $this->id_section = $data->id_section;
        // $this->order_num = isset($data->order_num) ? $data->order_num : null;
        $this->type = $data->type;
        $this->title = isset($data->title) ? $data->title : null;
        $this->subtitle = isset($data->subtitle) ? $data->subtitle : null;
        $this->description = isset($data->description) ? $data->description : null;
        $this->text_button = isset($data->text_button) ? $data->text_button : null;
        $this->extra_text_button = isset($data->extra_text_button) ? $data->extra_text_button : null;

        $this->link_id = isset($data->link_id) ? $data->link_id : null;

        $this->extra_link_id = isset($data->extra_link_id) ? $data->extra_link_id : null;
        // $this->active = isset($data->active) ? $data->active : null;
        $this->image = isset($data->image) ? $fileUploader->getUrl($data->image) : null;

        $this->video = isset($data->video) ? $fileUploader->getUrl($data->video, 'videos') : null;
        // $this->page_id = isset($data->page_id) ? $data->page_id : null;
        $this->section_items = ($data->relationLoaded('sectionItems') && $data->sectionItems) ? $data->sectionItems->map(fn($item) => new SectionItemResponseDto($item)) : null;

        $this->machines = ($data->relationLoaded('machines') && $data->machines) ? $data->machines->map(fn($item) => new MachineResponseDto($item)) : null;

        $this->link = ($data->relationLoaded('link') && $data->link) ? new LinkResponseDto($data->link) : null;
        $this->extra_link = ($data->relationLoaded('extraLink') && $data->extraLink) ? new LinkResponseDto($data->extraLink) : null;
        $this->menus = ($data->relationLoaded('menus') && $data->menus) ? $data->menus->map(fn($menu) => new MenuResponseDto($menu)) : null;

        $this->pivot_pages = ($data->relationLoaded('pivot') && $data->pivot) ? $data->pivot : null;
        $this->pages = ($data->relationLoaded('pages') && $data->pages) ? $data->pages : null;

        $this->icon_url = isset($data->icon_url) ? $fileUploader->getUrl($data->icon_url) : null;
        $this->icon = isset($data->icon) ? json_decode($data->icon, true) : null;
        $this->icon_type = isset($data->icon_type) ? $data->icon_type : null;
        $this->additional_info_list = isset($data->additional_info_list) ? json_decode($data->additional_info_list, true) : null;



    }
}
