<?php

use Illuminate\Database\Eloquent\Model;

enum SectionSearchField: string
{
    case ID = 'id_section';
}

enum SectionType: string
{
    case HERO = 'HERO';
    case BENEFITS = 'BENEFITS';

    case MACHINE_TYPE = 'MACHINE_TYPE';
}

class SectionModel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id_section',
        'title',
        'content',
        'type',
        'order',
        'created_at',
        'updated_at',
    ];
}