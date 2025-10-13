<?php

use Illuminate\Database\Eloquent\Model;


class CategoryModel extends Model
{
    public $table = 'categories';

    public $timestamps = true;

    public $primaryKey = 'id_category';

    protected $fillable = [
        'id_category',
        'title',
    ];

    public function sectionItem()
    {
        return $this->hasMany(SectionItemModel::class, 'category_id', 'id_category');
    }

}