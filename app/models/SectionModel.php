<?php

use Illuminate\Database\Eloquent\Model;
require_once "app/models/MenuModel.php";

enum SectionType: string
{
    case HERO = 'HERO';
    case WHY_US = 'WHY_US';
    case CASH_PROCESSING_EQUIPMENT = 'CASH_PROCESSING_EQUIPMENT';
    case VALUE_PROPOSITION = 'VALUE_PROPOSITION';
    case CLIENT = 'CLIENT';
    case OUR_COMPANY = 'OUR_COMPANY';
    case MACHINE = 'MACHINE';
    case CONTACT_TOP_BAR = 'CONTACT_TOP_BAR';
    case MAIN_NAVIGATION_MENU = 'MAIN_NAVIGATION_MENU';
    case CTA_BANNER = 'CTA_BANNER';
    case SOLUTIONS_OVERVIEW = 'SOLUTIONS_OVERVIEW';
    case MISSION_VISION = 'MISSION_VISION';
    case CONTACT_US = 'CONTACT_US';
    case FOOTER = 'FOOTER';

    case ADVANTAGES = 'ADVANTAGES';

    case SUPPORT_MAINTENANCE = 'SUPPORT_MAINTENANCE';

    case OPERATIONAL_BENEFITS = 'OPERATIONAL_BENEFITS';

    case MACHINE_DETAILS = 'MACHINE_DETAILS';

    case MACHINES_CATALOG = 'MACHINES_CATALOG';

    case FULL_MAINTENANCE_PLAN = 'FULL_MAINTENANCE_PLAN';

    case PREVENTIVE_CORRECTIVE_MAINTENANCE = 'PREVENTIVE_CORRECTIVE_MAINTENANCE';

    case SUPPORT_WIDGET = 'SUPPORT_WIDGET';


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
        'extra_text_button',
        'image',
        'link_id',
        'extra_link_id',
        'type',
        "icon",
        "icon_url",
        "icon_type",
        "additional_info_list",
        'video'
        // 'order_num',
        // 'page_id'
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

    public function extraLink()
    {
        return $this->hasOne(LinkModel::class, 'id_link', 'extra_link_id');
    }


    public function menus()
{
    return $this->belongsToMany(MenuModel::class, 'section_menus', 'id_section', 'id_menu')
        ->withPivot('order_num')
        ->orderBy('section_menus.order_num');
}

public function machines()
{
    return $this->belongsToMany(MachineModel::class, 'section_machines', 'id_section', 'id_machine')
        ->withPivot('order_num')
        ->orderBy('section_machines.order_num');
}


    public function pageSections()
    {
        return $this->hasMany(PageSectionModel::class, 'id_section', 'id_section');
    }

    public function pages()
    {
        return $this->belongsToMany(PageModel::class, 'section_pages', 'id_section', 'id_page');
        // withPivot(['type', 'active', 'order_num'])->
        // orderBy('order_num', 'asc');
    }




}