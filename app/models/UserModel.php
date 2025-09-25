<?php
require_once 'config/Database.php';

enum UserSearchField: string
{
  case ID = 'id_user';
  case EMAIL = 'email';
}

enum UserRole: string
{
  case USER = 'USER';
  case EDITOR = 'EDITOR';
}


class UserModel
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


  public function findByField(UserSearchField $field, $value)
  {
    $stmt = $this->db->prepare('CALL GetUserByField(?, ?)');
    $stmt->execute([$field->value, $value]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }


  public function create($data)
  {
    $stmt = $this->db->prepare('CALL InsertUser(?,?,?,?,?)');
    $stmt->execute($data);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function createMany(array $data)
  {
    $query = $this->db->query('INSERT INTO users (name, lastname, email, password, role) VALUES ' . implode(',', array_map(fn($item) => "('{$item['name']}', '{$item['lastname']}', '{$item['email']}', '{$item['password']}', '{$item['role']}')", $data)));
    return $query->rowCount();
  }

  public function truncate()
  {
    $query = $this->db->query('TRUNCATE TABLE users');
  }



}

?>