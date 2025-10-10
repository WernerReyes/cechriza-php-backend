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

    public function scopeWhereTitleCaseSensitive($query, $title)
    {
        return $query->whereRaw('BINARY title = ?', [$title]);
    }
}