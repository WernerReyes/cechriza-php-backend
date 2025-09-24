<?php
require_once "app/models/User.php";
require_once "app/data/data.php";
class Seed
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function run()
    {
        try {
            //code...
            //* Truncate tables
            $this->user->truncate();

            $this->user->createMany(DataSeeder::getUsersData());

            error_log("✅ Data inserted correctly");
        } catch (Exception $e) {
            error_log("❌ There was an error " . $e->getMessage());
            throw $e;
        }


    }
}

?>