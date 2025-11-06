<?php
class MenuResponseDto {
    public $id_menu;
    public $title;
    public $parent_id;

    public $parent;

    public $link;

    public $children;

    public $created_at;
    public $updated_at;

    public function __construct($menu) {
        $this->id_menu = $menu->id_menu;
        $this->title = $menu->title;
        $this->parent_id = $menu->parent_id;
        $this->link = ($menu->relationLoaded('link') && $menu->link) ? new LinkResponseDto($menu->link) : null;
        $this->parent = ($menu->relationLoaded('parent') && $menu->parent) ? new MenuResponseDto($menu->parent) : null;
        $this->children = ($menu->relationLoaded('children') && $menu->children) ? $menu->children->map(fn($child) => new MenuResponseDto($child)) : null;
        $this->created_at = $menu->created_at;
        $this->updated_at = $menu->updated_at;
    }
}