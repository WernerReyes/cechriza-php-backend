<?php
class CreateCategoryDto
{
    public $title;
    public $type;

    public function __construct($body)
    {
        $this->title = $body['title'];
        $this->type = $body['type'];
    }

    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation->required('title')
            ->minLength('title', 2)
            ->maxLength('title', 100);
        $validation->required('type')
            ->enum('type', [CategoryType::class]);

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