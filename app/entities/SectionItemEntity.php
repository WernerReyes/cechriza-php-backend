<?php

class SectionItemEntity
{

    public int $id;
    public int $sectionId;

    public int $order;

    public ?string $title = null;
    public ?string $subtitle = null;
    public ?string $description = null;
    public ?string $image = null;
    public ?string $backgroundImage = null;
    public ?string $textButton = null;
    public ?string $linkButton = null;

    public ?int $functionMachineId = null;

    public function __construct(array $data)
    {
        error_log("SectionItemEntity data: " . json_encode($data));
        $this->id = (int) $data['id_section_items'];
        $this->sectionId = (int) $data['sections_id'];
        $this->order = (int) $data['order'];
        $this->title = $data['title'] ?? null;
        $this->subtitle = $data['subtitle'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->image = $data['image'] ?? null;
        $this->backgroundImage = $data['background_image'] ?? null;
        $this->textButton = $data['text_button'] ?? null;
        $this->linkButton = $data['link_button'] ?? null;
        $this->functionMachineId = isset($data['function_machine_id']) ? (int) $data['function_machine_id'] : null;
    }
}