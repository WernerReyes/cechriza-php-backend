<?php

use Illuminate\Database\Eloquent\Model;


class PageModel extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'id_page';
    public $timestamps = true;
    protected $fillable = ['title', 'description', 'slug', 'is_main'];


    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'sections.sectionItems',
            'sections.sectionItems.link:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.sectionItems.link.page:id_page,slug',
            'sections.machines',
            'sections.pageSections',
            'sections.link:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.link.page:id_page,slug',
            'sections.extraLink:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.extraLink.page:id_page,slug',
            'sections.machines.category:id_category,title,type',
            'sections.machines.link:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.machines.link.page:id_page,slug',
            'sections.menus.link:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.menus.link.page:id_page,slug',
            'sections.menus.parent.link:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.menus.parent.link.page:id_page,slug',
            'sections.menus.parent.parent.link:id_link,type,url,page_id,new_tab,file_path,title',
            'sections.menus.parent.parent.link.page:id_page,slug',
        ]);
    }
    
    public function sections()
    {
        return $this->belongsToMany(SectionModel::class, 'section_pages', 'id_page', 'id_section');
    }

    // public function pivot()
    // {
    //     return $this->hasMany(PageSectionModel::class, 'id_section', 'id_section');
    // }

}

?>