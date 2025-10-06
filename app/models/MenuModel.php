<?php

use Illuminate\Database\Eloquent\Model;

enum MenuSearchField: string
{
    case ID = 'id_menu';

    case ORDER = 'order';
}

enum MenuTypes: string
{
    case INTERNAL_PAGE = 'internal-page';
    case EXTERNAL_LINK = 'external-link';
    case DROPDOWN = 'dropdown';
}

enum MenuUpdateField: string
{
    case TITLE = 'title';
    case ORDER = 'order';
    case URL = 'url';
    case PARENT_ID = 'parent_id';
    case ACTIVE = 'active';
}

// class MenuModel
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


//     public function getAll()
//     {
//         $stmt = $this->db->prepare("CALL GetAllMenusOrdered()");
//         $stmt->execute();
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }


//     public function getByField(MenuSearchField $field, $value)
//     {
//         $stmt = $this->db->prepare("CALL GetMenuByField(?, ?)");
//         $stmt->execute([$field->value, $value]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }


//     public function create(array $data)
//     {
//         $stmt = $this->db->prepare('CALL InsertMenu(?,?,?,?,?,?,?)');
//         $stmt->execute($data);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

//     public function update(array $data)
//     {
//         $stmt = $this->db->prepare('CALL UpdateMenu(?,?,?,?,?,?)');
//         $stmt->execute($data);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

//     public function delete(int $id)
//     {
//         $stmt = $this->db->prepare('CALL DeleteMenu(?)');
//         return $stmt->execute([$id]);
//     }
// }

class MenuModel extends Model {
    public $table = 'menu';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'order_num',
        'parent_id',
        'link_id',
        'active',
    ];

    protected $primaryKey = 'id_menu';

    public function parent() {
        return $this->belongsTo(MenuModel::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(MenuModel::class, 'parent_id');
    }

    public function link() {
        return $this->hasOne(LinkModel::class, 'id_link', 'link_id');
    }

}
