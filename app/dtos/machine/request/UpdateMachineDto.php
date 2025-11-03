<?php

require_once "app/utils/ValidationEngine.php";
require_once "app/utils/UuidUtil.php";
class UpdateMachineDto
{
    public $id;
    public $name;
    public $shortDescription;
    public $fullDescription;

    public $fileImages;

    public $imagesToRemove;

    public $imagesToUpdate;

    public $manualFile;

    public $technicalSpecifications;

    public $linkId;

    public $textButton;


    public function __construct($body, $id)
    {
        $this->id = $id;
        $this->name = $body['name'] ?? null;
        $this->shortDescription = $body['shortDescription'] ?? null;
        $this->fullDescription = $body['fullDescription'] ?? null;
        $this->fileImages = $body['fileImages'] ?? null;
        $this->imagesToUpdate = isset($body['imagesToUpdateOld']) ? $this->mapImagesToUpdate($body) : null;
        $this->imagesToRemove = $body['imagesToRemove'] ?? null;
        $this->technicalSpecifications = $body['technicalSpecifications'] ?? null;
        $this->manualFile = $body['manualFile'] ?? null;
        $this->linkId = $body['linkId'] ?? null;
        $this->textButton = $body['textButton'] ?? null;
    }

    private function mapImagesToUpdate($body): array
    {
        $mapped = [];
        foreach ($body['imagesToUpdateOld'] as $index => $item) {
            $mapped[] = [
                'oldImage' => $item ?? null,
                'newFile' => $body['imagesToUpdateNew'][$index] ?? null,
            ];
        }
        return $mapped;
    }

    // private function mapImagesToRemove($body): array
    // {
    //     $mapped = [];
    //     foreach ($body['imagesToRemove'] as $index => $item) {
    //         $mapped[] = [
    //             'delete' => $item ?? null,
    //             'newFile' => $body['imagesToRemoveNew'][$index] ?? null,
    //         ];
    //     }
    //     return $mapped;
    // }


    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->
            minLength("name", 2)
            ->maxLength("name", 100)
            ->optional("name")

            ->minLength("shortDescription", 10)
            ->maxLength("shortDescription", 500)
            ->optional("shortDescription")

            ->minLength("fullDescription", 10)
            ->maxLength("fullDescription", 5000)
            ->optional("fullDescription")

            ->array("fileImages")
            ->minItems("fileImages", 1)
            ->maxItems("fileImages", 5)
            ->optional("fileImages")


            ->array("technicalSpecifications")
            ->minItems("technicalSpecifications", 1)
            ->fieldsMatchInArray(['title', 'description'], $this->technicalSpecifications)
            ->optional("technicalSpecifications")


            ->files("manualFile", ['pdf'])
            ->optional("manualFile")

            ->array("imagesToRemove")
            // ->fieldsMatchInArray(['delete', 'newFile'], $this->imagesToRemove)
            ->minItems("imagesToRemove", 1)
            ->maxItems("imagesToRemove", 5)
            ->optional("imagesToRemove")

            ->array("imagesToUpdate")
            ->fieldsMatchInArray(['oldImage', 'newFile'], $this->imagesToUpdate)
            ->minItems("imagesToUpdate", 1)
            ->maxItems("imagesToUpdate", 5)
            ->optional("imagesToUpdate")

            ->integer("linkId")
            ->min("linkId", 1)
            ->optional("linkId")

            ->maxLength("textButton", 50)
            ->optional("textButton")

        ;


        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;

    }


    public function toArray(array $imagesPath, $manualPath = null): array
    {
        return [
            'name' => $this->name,
            'description' => $this->shortDescription,
            'long_description' => $this->fullDescription,
            'images' => json_encode(array_map(function ($path) {
                return [
                    'isMain' => $path["isMain"],
                    'url' => $path["url"]
                ];
            }, $imagesPath)),
            'manual' => $manualPath,
            'technical_specifications' => json_encode(array_map(function ($spec) {
                return [
                    'id' => UuidUtil::v4(),
                    'title' => $spec['title'],
                    'description' => $spec['description']
                ];
            }, $this->technicalSpecifications)),
            'link_id' => $this->linkId,
            'text_button' => $this->textButton,
        ];
    }




}