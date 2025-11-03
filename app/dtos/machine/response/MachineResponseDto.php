<?php
class MachineResponseDto
{

    public $id_machine;
    public $name;
    public $description;
    public $long_description;

    public $images;

    public $manual;

    public $technical_specifications;

    public $category_id;

    public $created_at;

    public $updated_at;

    public $category;

    public $link_id;
    public $link;

    public $text_button;

    public $sections;





    public function __construct(
        MachineModel $machine
    ) {
        $fileUploader = new FileUploader();

        $this->id_machine = $machine->id_machine;
        $this->name = $machine->name;
        $this->description = $machine->description;
        $this->long_description = $machine->long_description;
        $this->manual = isset($machine->manual) ? $fileUploader->getUrl($machine->manual, 'files') : null;
        $this->images = isset($machine->images) ? array_map(function ($imagePath) use ($fileUploader) {
            return [
                'url' => $fileUploader->getUrl($imagePath['url']),
                'isMain' => $imagePath['isMain']
            ];
        }, json_decode($machine->images, true)) : null;

        $this->technical_specifications = isset($machine->technical_specifications) ? array_map(function ($spec) {
            return [
                'id' => $spec['id'],
                'title' => $spec['title'],
                'description' => $spec['description']
            ];
        }, json_decode($machine->technical_specifications, true)) : null;
        $this->category_id = $machine->category_id;
        $this->category = $machine->category;
        $this->link_id = $machine->link_id;
        $this->link = $machine->link;
        $this->text_button = $machine->text_button;
        $this->sections = $machine->sections;
        $this->created_at = $machine->created_at;
        $this->updated_at = $machine->updated_at;
    }


}
