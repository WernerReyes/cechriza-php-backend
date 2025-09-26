<?php

enum SectionSearchField: string
{
    case ID = 'id_section';
}

enum SectionType: string
{
    case HERO = 'HERO';
    case BENEFITS = 'BENEFITS';

    case MACHINE_TYPE = 'MACHINE_TYPE';
}

class SectionModel
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
        $stmt = $this->db->prepare("CALL GetAllSectionsOrdered()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getByField(SectionSearchField $field, $value)
    {
        $stmt = $this->db->prepare("CALL GetSectionByField(?, ?)");
        $stmt->execute([$field->value, $value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function create($data)
    {
        $stmt = $this->db->prepare('CALL InsertSection(?,?,?,?,?,?,?,?)');
        $stmt->execute($data);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>