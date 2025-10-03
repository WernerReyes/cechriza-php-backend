<?php

use Illuminate\Database\Eloquent\Model;


class CategoryModel extends Model
{
    public $table = 'categories';

    public $timestamps = true;

    protected $fillable = [
        'id_category',
        'title',
        'slug',
        'description',
        'image_url',
    ];
}