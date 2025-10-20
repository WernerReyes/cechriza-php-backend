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
        'tecnical_specifications',
        'category_id',
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id_category');
    }
}

