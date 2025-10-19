<?php

use Illuminate\Database\Eloquent\Model;
// class SectionItemModel
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


//     // public function getByField(PageSearchField $field, $value)
//     // {
//     //     $stmt = $this->db->prepare("CALL GetPageByField(?, ?)");
//     //     $stmt->execute([$field->value, $value]);
//     //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     // }

//     public function create($data)
//     {
//         $stmt = $this->db->prepare('CALL InsertSectionItem(?,?,?,?,?,?,?,?,?,?,?)');
//         $stmt->execute($data);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

// }

enum InputType: string
{
    case TEXT = 'TEXT';
    case EMAIL = 'EMAIL';
    case TEXTAREA = 'TEXTAREA';
}

class SectionItemModel extends Model
{
    public $table = "section_items";
    public $timestamps = false;

    protected $primaryKey = "id_section_item";

    protected $fillable = [
        "id_section_item",
        "title",
        "subtitle",
        "description",
        "image",
        "background_image",
        "text_button",
        "section_id",
        "category_id",
        "input_type",
        "icon",
        "link_id"
    ];

    public function link()
    {
        return $this->hasOne(LinkModel::class, 'id_link', 'link_id');
    }

  
}

?>