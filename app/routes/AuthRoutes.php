<?php
require_once "app/core/Router.php";
require_once "app/controllers/AuthController.php";

$router = new Router();

// $router->get("/", "UserController@index");
// $router->post("/many", "UserController@createMany");
// $router->get("/seed", "SeedController@run");

class AuthRoutes
{

    private static string $prefix = "/auth";

    public static function routes(
        Router $router
    ) {
        $router->post(self::$prefix . "/register", "AuthController@register");
    }
}
?>