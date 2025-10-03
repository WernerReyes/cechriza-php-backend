<?php

use Illuminate\Database\Eloquent\Model;


enum LinkType: string
{
    case PAGE = 'PAGE';
    case EXTERNAL = 'EXTERNAL';
}

class LinkModel extends Model
{
    public $table = 'links';

    public $primaryKey = 'id_link';

    public $timestamps = true;

    protected $fillable = [
        'id_link',
        'title',
        'url',
        'type',
        'page_id',
        'new_tab',
    ];

    public function page() {
        return $this->belongsTo(PageModel::class, 'page_id', 'id_page');
    }
}