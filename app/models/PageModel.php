<?php

use Illuminate\Database\Eloquent\Model;

enum PageSearchField: string
{
    case ID = 'id_pages';

}

// class PageModel
// {
//     private static $instance = null;
//     private $db;

//     public function __construct()
//     {
//         $this->db = Database::$db;
//     }

//     public static function getInstance()
//     {
//         if (self::$instance === null) {
//             self::$instance = new self();
//         }

//         return self::$instance;
//     }


//     public function getAll($params = [])
//     {
//         $stmt = $this->db->prepare("CALL GetAllPages()");
//         $stmt->execute();
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }

//     public function getByField(PageSearchField $field, $value)
//     {
//         $stmt = $this->db->prepare("CALL GetPageByField(?, ?)");
//         $stmt->execute([$field->value, $value]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }

//     public function create($data)
//     {
//         $stmt = $this->db->prepare('CALL InsertPage(?,?,?)');
//         $stmt->execute($data);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

// }

class PageModel extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'id_page';
    public $timestamps = true;
    protected $fillable = ['title', 'description', 'slug'];

    public function menu()
    {
        // return $this->hasOne(PageModel::class, 'menu_id', 'id_pages');
        return $this->belongsTo(MenuModel::class, 'menu_id', 'id_menu');
    }

   

    public function sections() {
        return $this->belongsToMany(SectionModel::class, 'section_pages', 'id_page', 'id_section')->orderBy('order_num', 'asc');
    }
}

?>