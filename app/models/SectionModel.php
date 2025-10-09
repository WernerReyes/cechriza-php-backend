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

    case WHY_US = 'WHY_US';

    case MACHINE_TYPE = 'MACHINE_TYPE';
}

class SectionModel extends Model
{

    protected $table = 'sections';
    public $timestamps = false;

    public $primaryKey = 'id_section';

    protected $fillable = [
        'id_section',
        'title',
        'description',
        'subtitle',
        'active',
        'text_button',
        'link_id',
        'type',
        'order_num',
        'page_id'
        // 'created_at',
        // 'updated_at',
    ];


    public function sectionItems()
    {
        return $this->hasMany(SectionItemModel::class, 'section_id', 'id_section');
    }
}