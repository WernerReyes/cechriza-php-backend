<?php
class AssocieteToPagesRequestDto
{
    public $id;
    public $pagesIds;

    public function __construct($body, $id)
    {
        $this->id = $id;
        $this->pagesIds = $body['pagesIds'] ?? [];
    }


    public function validate()
    {
        $validation = new ValidationEngine($this);
        $validation
            ->required("id")
            ->integer("id")
            ->min("id", 1)

            // ->required("pagesIds")
            ->array("pagesIds")
            // ->minItems("pagesIds", 1)
            ->optional("pagesIds")

        ;

        if ($validation->fails()) {
            return $validation->getErrors();
        }



        return $this;
    }

    public function toDBArray($orderNum = null, $pageId = null)
    {
        return [
            'id_section' => $this->id,
            'order_num' => $orderNum,
            'id_page' => $pageId,
            'type' => SectionMode::LAYOUT->value,
        ];
    }


}