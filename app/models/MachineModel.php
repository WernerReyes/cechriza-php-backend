<?php

use Illuminate\Database\Eloquent\Model;
class MachineModel extends Model
{
    protected $table = "machines";

    public $primaryKey = "id_machine";

    protected $fillable = [
        'name',
        'description',
        'long_description',
        'images',
        'technical_specifications',
        'category_id',
        'link_id',
        'text_button',
        'manual'
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id_category');
    }

    public function link()
    {
        return $this->belongsTo(LinkModel::class, 'link_id', 'id_link');
    }
}

