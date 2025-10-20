<?php

use Illuminate\Database\Eloquent\Model;
require_once "app/models/MachineModel.php";


enum CategoryType: string
{
    case COIN = 'COIN';
    case BILL = 'BILL';
}

class CategoryModel extends Model
{
    public $table = 'categories';

    public $timestamps = true;

    public $primaryKey = 'id_category';

    protected $fillable = [
        'id_category',
        'title',
        'type',
    ];

    public function sectionItem()
    {
        return $this->hasMany(SectionItemModel::class, 'category_id', 'id_category');
    }

    public function machines()
    {
        return $this->hasMany(MachineModel::class, 'category_id', 'id_category');
    }

}