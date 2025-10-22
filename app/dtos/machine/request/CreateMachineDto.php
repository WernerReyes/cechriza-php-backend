<?php

require_once "app/utils/ValidationEngine.php";
class CreateMachineDto
{
    public $name;
    public $shortDescription;
    public $fullDescription;

    public $fileImages;

    public $technicalSpecifications;

    public $categoryId;

    public function __construct($body)
    {
        $this->name = $body['name'] ?? '';
        $this->shortDescription = $body['shortDescription'] ?? '';
        $this->fullDescription = $body['fullDescription'] ?? '';
        $this->fileImages = $body['fileImages'] ?? [];
        $this->technicalSpecifications = $body['technicalSpecifications'] ?? [];
        $this->categoryId = $body['categoryId'] ?? 0;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("name")
            ->minLength("name", 2)
            ->maxLength("name", 100)

            ->required("shortDescription")
            ->minLength("shortDescription", 10)
            ->maxLength("shortDescription", 255)

            ->required("fullDescription")
            ->minLength("fullDescription", 10)
            ->maxLength("fullDescription", 5000)

            ->required("fileImages")
            ->array("fileImages")
            ->minItems("fileImages", 1)
            ->maxItems("fileImages", 5)


            ->required("technicalSpecifications")
            ->array("technicalSpecifications")
            ->minItems("technicalSpecifications", 1)
            ->fieldsMatchInArray( ['title', 'description'], $this->technicalSpecifications)


            ->required("categoryId")
            ->integer("categoryId")
            ->min("categoryId", 1)

        ;

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;

    }


    public function toArray(array $imagesPath): array
    {
        return [
            'name' => $this->name,
            'description' => $this->shortDescription,
            'long_description' => $this->fullDescription,
            'images' => json_encode($imagesPath),
            'technical_specifications' => json_encode(array_map(function ($spec) {
                return [
                    'id' => $this->uuid(),
                    'title' => $spec['title'],
                    'description' => $spec['description']
                ];
            }, $this->technicalSpecifications)),
            'category_id' => $this->categoryId,
        ];
    }

    private function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }


}