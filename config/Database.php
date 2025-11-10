<?php
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . "/../vendor/autoload.php";
class Database
{


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
