<?php
require_once "config/Router.php";
require_once "app/controllers/LinkController.php";
class LinkRoutes
{
    private static string $prefix = '/link';
    public static function routes(
        Router $router
    ) {
        $router->get(self::$prefix, "LinkController@getAll", ["auth"]);
        $router->post(self::$prefix, "LinkController@create", ["auth"]);
        $router->put(self::$prefix . "/{id}", "LinkController@update", ["auth"]);
        $router->delete(self::$prefix . "/{id}", "LinkController@delete", ["auth"]);
    }
}