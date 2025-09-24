<?php
require_once 'app/controllers/SeedController.php';

// Register Error Handler
// require_once 'app/utils/ErrorHandler.php';
// ErrorHandler::register();

// require_once 'app/routes/AuthRoutes.php';
// require_once 'app/core/Router.php';


// // Headers para API
// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');

// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//    http_response_code(200);
//    exit();
// }

// try {
//    $router = new Router();
//    AuthRoutes::routes($router);
//    $router->dispatch();


// } catch (Exception $e) {
//    // El manejador se encargará de esto
//    throw $e;
// }

// phpinfo();
$seed = new SeedController();
$seed->runScript();


?>

