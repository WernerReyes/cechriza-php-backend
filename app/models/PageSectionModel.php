<?php

use Illuminate\Database\Eloquent\Model;

enum SectionMode: string
{
    case CUSTOM = 'CUSTOM';
    case LAYOUT = 'LAYOUT';
}

class PageSectionModel extends Model
{
    protected $table = "section_pages";

    // Soporte para primary key compuesto en Eloquent
    public $incrementing = false;

    // protected $primaryKey = ['id_page', 'id_section'];

    public $timestamps = false;

   

    protected $fillable = [
        "id_page",
        "id_section",
        "order_num",
        "active",
        "type",
    ];


}


// CREATE TABLE section_pages (
//     id_page INT NOT NULL,
//     id_section INT NOT NULL,
//     order_num INT DEFAULT 1,
//     active TINYINT DEFAULT 1,
//     PRIMARY KEY (id_page, id_section),
//     FOREIGN KEY (id_page) REFERENCES pages(id_page) ON DELETE CASCADE,
//     FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
