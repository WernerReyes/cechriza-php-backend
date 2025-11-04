<?php

use Illuminate\Database\Eloquent\Model;


enum InputType: string
{
    case TEXT = 'TEXT';
    case EMAIL = 'EMAIL';
    case TEXTAREA = 'TEXTAREA';
}

enum IconType: string
{
    case LIBRARY = 'LIBRARY';
    case IMAGE = 'IMAGE';
}

class SectionItemModel extends Model
{
    public $table = "section_items";
    public $timestamps = false;

    protected $primaryKey = "id_section_item";

    protected $fillable = [
        "id_section_item",
        "title",
        "subtitle",
        "description",
        "image",
        "background_image",
        "text_button",
        "section_id",
        "category_id",
        "input_type",
        "icon",
        "icon_url",
        "icon_type",
        "link_id",
        "additional_info_list",
    ];

    public function link()
    {
        return $this->hasOne(LinkModel::class, 'id_link', 'link_id');
    }

  
}

?>