<?php


class SectionItemModel
{
    private static $instance = null;
    private $db;

    public function __construct()
    {
        $this->db = Database::$db;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    // public function getByField(PageSearchField $field, $value)
    // {
    //     $stmt = $this->db->prepare("CALL GetPageByField(?, ?)");
    //     $stmt->execute([$field->value, $value]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function create($data)
    {
        $stmt = $this->db->prepare('CALL InsertSectionItem(?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute($data);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}

?>