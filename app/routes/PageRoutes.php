<?php
require_once "config/Router.php";
require_once "app/controllers/PageController.php";
class PageRoutes
{
    private static string $prefix = '/page';
    public static function routes(
        Router $router
    ) {
        $router->get(self::$prefix, "PageController@getAll", ["auth"]);
        $router->post(self::$prefix, "PageController@create", ["auth"]);
        $router->put(self::$prefix . "/{id}", "PageController@update", ["auth"]);
        $router->delete(self::$prefix . "/{id}", "PageController@delete", ["auth"]);
    }
}
?>