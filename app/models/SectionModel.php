<?php

use Illuminate\Database\Eloquent\Model;

enum SectionType: string
{
    case HERO = 'HERO';
    case WHY_US = 'WHY_US';
    case CASH_PROCESSING_EQUIPMENT = 'CASH_PROCESSING_EQUIPMENT';
    case VALUE_PROPOSITION = 'VALUE_PROPOSITION';
    case CLIENT = 'CLIENT';
    case OUR_COMPANY = 'OUR_COMPANY';

    case MACHINE = 'MACHINE';


    case BENEFITS = 'BENEFITS';
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
        'image',
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

    public function link()
    {
        return $this->hasOne(LinkModel::class, 'id_link', 'link_id');
    }
}