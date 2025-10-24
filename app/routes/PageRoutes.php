<?php
require_once "config/Router.php";
require_once "app/controllers/PageController.php";
class PageRoutes
{
    private static string $prefix = '/page';

    private static string $publicPrefix = "/public/page";
    public static function routes(
        Router $router
    ) {
        $router->get(self::$publicPrefix, "PageController@getAll");


        $router->get(self::$prefix, "PageController@getAll", ["auth"]);
        $router->get(self::$prefix . "/{id}", "PageController@getById", ["auth"]);
        $router->post(self::$prefix, "PageController@create", ["auth"]);
        $router->put(self::$prefix . "/{id}", "PageController@update", ["auth"]);
        $router->delete(self::$prefix . "/{id}", "PageController@delete", ["auth"]);
    }
}
?>