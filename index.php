<?php
// require_once 'app/controllers/SeedController.php';
require_once 'vendor/autoload.php';

//* Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Register Error Handler
require_once 'app/utils/ErrorHandler.php';
ErrorHandler::register();

require_once 'app/AppRoutes.php';



// Headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    Database::connect();

    AppRoutes::routes();
} catch (Exception $e) {
    throw $e;
}

// phpinfo();
// $seed = new SeedController();
// $seed->runScript();


?>