<?php
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . "/../vendor/autoload.php";
class Database
{

    // public static $db;
    // public static function connect()
    // {
    //     $host = $_ENV['DB_HOST'];
    //     $db = $_ENV['DB_NAME'];
    //     $user = $_ENV['DB_USERNAME'];
    //     $pass = $_ENV['DB_PASSWORD'];
    //     $charset = "utf8mb4";

    //     $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    //     $options = [
    //         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //     ];

    //     self::$db = new PDO($dsn, $user, $pass, $options);

    // }

   

    public static function connect()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'database' => $_ENV['DB_NAME'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);


        $capsule->setAsGlobal();


        $capsule->bootEloquent();
    }
}
