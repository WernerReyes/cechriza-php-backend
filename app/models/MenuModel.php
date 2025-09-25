<?php

enum MenuSearchField: string
{
    case ID = 'id_menu';
}
class MenuModel
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


    public function getAll()
    {
        $stmt = $this->db->prepare("CALL GetAllMenusOrdered()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getByField(MenuSearchField $field, $value)
    {
        $stmt = $this->db->prepare("CALL GetMenuByField(?, ?)");
        $stmt->execute([$field->value, $value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function create($data)
    {
        $stmt = $this->db->prepare('CALL InsertMenu(?,?,?,?,?,?)');
        $stmt->execute($data);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $stmt = $this->db->prepare('CALL UpdateMenu(?,?,?,?,?,?)');
        $stmt->execute($data);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
