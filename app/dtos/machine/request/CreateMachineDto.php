<?php

require_once "app/utils/ValidationEngine.php";
require_once "app/utils/UuidUtil.php";
class CreateMachineDto
{
    public $name;
    public $shortDescription;
    public $fullDescription;

    public $fileImages;

    public $manualFile;

    public $technicalSpecifications;

    public $categoryId;

    public $linkId;

    public $textButton;

    public function __construct($body)
    {
        $this->name = $body['name'] ?? '';
        $this->shortDescription = $body['shortDescription'] ?? '';
        $this->fullDescription = $body['fullDescription'] ?? '';
        $this->fileImages = $body['fileImages'] ?? [];
        $this->technicalSpecifications = $body['technicalSpecifications'] ?? [];
        $this->categoryId = $body['categoryId'] ?? 0;
        $this->manualFile = $body['manualFile'] ?? null;
        $this->linkId = $body['linkId'] ?? null;
        $this->textButton = $body['textButton'] ?? null;
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required("name")
            ->minLength("name", 2)
            ->maxLength("name", 100)

            ->required("shortDescription")
            ->minLength("shortDescription", 10)
            ->maxLength("shortDescription", 500)

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
            ->fieldsMatchInArray(['title', 'description'], $this->technicalSpecifications)


            ->required("categoryId")
            ->integer("categoryId")
            ->min("categoryId", 1)

            ->files("manualFile", ['pdf'])
            ->optional("manualFile")

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
            'images' => json_encode($imagesPath),
            'manual' => $manualPath,
            'technical_specifications' => json_encode(array_map(function ($spec) {
                return [
                    'id' => UuidUtil::v4(),
                    'title' => $spec['title'],
                    'description' => $spec['description']
                ];
            }, $this->technicalSpecifications)),
            'category_id' => $this->categoryId,
            'link_id' => $this->linkId,
            'text_button' => $this->textButton,
        ];
    }




}