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





    public function __construct(
        MachineModel $machine
    ) {
        $fileUploader = new FileUploader();

        $this->id_machine = $machine->id_machine;
        $this->name = $machine->name;
        $this->description = $machine->description;
        $this->long_description = $machine->long_description;
        $this->manual = isset($machine->manual) ? $fileUploader->getUrl($machine->manual, 'files') : null;
        $this->images = array_map(function ($imagePath) use ($fileUploader) {
            return $fileUploader->getUrl($imagePath);
        }, json_decode($machine->images, true));

        $this->technical_specifications = array_map(function ($spec) {
            return [
                'id' => $spec['id'],
                'title' => $spec['title'],
                'description' => $spec['description']
            ];
        }, json_decode($machine->technical_specifications, true));
        $this->category_id = $machine->category_id;
        $this->created_at = $machine->created_at;
        $this->updated_at = $machine->updated_at;
    }


}
