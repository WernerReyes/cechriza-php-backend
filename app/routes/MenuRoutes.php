<?php
require_once "config/Router.php";
require_once "app/middlewares/AuthMiddleware.php";

class MenuRoutes
{

    private static string $prefix = "/menu";

    public static function routes(
        Router $router
    ) {
        $router->get(self::$prefix, "MenuController@getAll", ["auth"]);
        $router->get(self::$prefix . "/{id}", "MenuController@getById", ["auth"]);
        $router->post(self::$prefix, "MenuController@create", ["auth"]);
        $router->put(self::$prefix . "/{id}", "MenuController@update", ["auth"]);
        $router->put(self::$prefix . "/order", "MenuController@updateOrder", ["auth"]);
        $router->delete(self::$prefix . "/{id}", "MenuController@delete", ["auth"]);

    }
}
