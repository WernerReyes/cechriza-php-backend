<?php
require_once "app/models/SectionModel.php";

class SectionEntity {
    public int $id;
    public int $order;
    public SectionType $type;
    public ?string $title;
    public ?string $subtitle;
    public ?string $description;
    public ?string $text_button;
    public ?string $url_button;
    public bool $active;
    public int $pages_id;

    public function __construct(array $data) {
        $this->id = (int)$data['id_section'];
        $this->order = (int)$data['order'];
        $this->type = SectionType::from($data['type']);
        $this->title = $data['title'] ?? null;
        $this->subtitle = $data['subtitle'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->text_button = $data['text_button'] ?? null;
        $this->url_button = $data['url_button'] ?? null;
        $this->active = isset($data['active']) ? (bool)$data['active'] : true;
        $this->pages_id = (int)$data['pages_id'];
    }

}