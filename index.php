<?php
// require_once 'app/controllers/SeedController.php';
require_once 'vendor/autoload.php';
require_once 'app/controllers/OS_TICKET.php';



//* Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Register Error Handler
require_once 'app/utils/ErrorHandler.php';
ErrorHandler::register();

require_once 'app/AppRoutes.php';

$allowOrigins = ['http://localhost:4200'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';


if (in_array($origin, $allowOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');


try {
    Database::connect();

    AppRoutes::routes();
} catch (Exception $e) {
    throw $e;
}

// phpinfo();
// $seed = new SeedController();
// $seed->runScript();


// $os_ticket = new OS_TICKET();
//  $os_ticket->runScript();






?>