<?php
require_once "app/utils/ValidationEngine.php";
require_once "app/models/CategoryModel.php";
class UpdateCategoryDto
{
    public $id;
    public $title;
    public $type;

    public function __construct($body, $id)
    {
        $this->id = $id;
        $this->title = $body['title'];
        $this->type = $body['type'];
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required('id')
            ->integer('id')
            ->min('id', 1)

            ->minLength('title', 2)
            ->maxLength('title', 100)
            ->optional('title');
        $validation->required('type')
            ->enum('type', CategoryType::class)
            ->optional('type');

        if ($validation->fails()) {
            return $validation->getErrors();
        }

        return $this;
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
        ];
    }

}