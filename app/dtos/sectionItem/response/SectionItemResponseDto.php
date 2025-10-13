<?php
class SectionItemResponseDto
{

    public int $id_section_item;
    public ?string $title;
    public ?string $subtitle;
    public ?string $description;
    public ?string $image;
    public ?string $background_image;
    public ?string $icon;
    public ?string $text_button;
    public ?int $link_id;
    public ?int $order_num;
    public int $section_id;
    public ?int $category_id;

    public function __construct($data)
    {
        $fileUploader = new FileUploader();
        $this->id_section_item =  isset($data->id_section_item) ? $data->id_section_item : null;
        $this->title = isset($data->title) ? $data->title : null;
        $this->subtitle = isset($data->subtitle) ? $data->subtitle : null;
        $this->description = isset($data->description) ? $data->description : null;
        $this->image = isset($data->image) ? $fileUploader->getUrl($data->image) : null;
        $this->background_image = isset($data->background_image) ? $fileUploader->getUrl($data->background_image) : null;
        $this->icon = isset($data->icon) ? $fileUploader->getUrl($data->icon) : null;
        $this->text_button = isset($data->text_button) ? $data->text_button : null;
        $this->link_id = isset($data->link_id) ? $data->link_id : null;
        $this->order_num = isset($data->order_num) ? $data->order_num : null;
        $this->section_id = isset($data->section_id) ? $data->section_id : null;
        $this->category_id = isset($data->category_id) ? $data->category_id : null;
    }
}