<?php
require_once "app/dtos/machine/response/MachineResponseDto.php";
class CategoryResponseDto {
    public int $id_category;
    public string $title;
    public string $type;
    public array $machines;

     public $created_at;

    public $updated_at;

    public function __construct($category)
    {
        $this->id_category = $category->id_category;
        $this->title = $category->title;
        $this->type = $category->type;
        $this->machines = $category->machines->map(fn($machine) => new MachineResponseDto($machine))->toArray();
        $this->created_at = $category->created_at;
        $this->updated_at = $category->updated_at;
    }
}