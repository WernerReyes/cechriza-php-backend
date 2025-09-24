<?php
require_once 'config/Database.php';

class User
{
  private static $instance = null;
  private $db;

  public function __construct()
  {
    $this->db = Database::connect();
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
    $query = $this->db->query('SELECT * FROM users');
    return $query->fetchAll();
  }

  public function getById(int $id)
  {
    $query = $this->db->query('SELECT * FROM users WHERE id_user = ' . $id);
    return $query->fetch();
  }

  public function getByEmail(string $email)
  {
    $query = $this->db->query('SELECT * FROM users WHERE email = "' . $email . '"');
    return $query->fetch();
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
