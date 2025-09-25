<?php
require_once "config/Router.php";
require_once "app/controllers/AuthController.php";
require_once "app/middlewares/AuthMiddleware.php";
class AuthRoutes
{

    private static string $prefix = "/auth";

    public static function routes(
        Router $router
    ) {
        $router->post(self::$prefix . "/register", "AuthController@register");
        $router->post(self::$prefix . "/login", "AuthController@login");
        $router->post(self::$prefix . "/logout", "AuthController@logout", ["auth"]);
        $router->get(self::$prefix . "/me", "AuthController@me", ["auth"]);
    }
}
?>