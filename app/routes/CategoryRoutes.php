<?php
require_once "config/Router.php";
require_once "app/controllers/CategoryController.php";
class CategoryRoutes
{
    private static string $prefix = '/category';
    public static function routes(
        Router $router
    ) {
        $router->get(self::$prefix, "CategoryController@getAll", ["auth"]);
        $router->post(self::$prefix,"CategoryController@create", ["auth"]);
        $router->put(self::$prefix . "/{id}", "CategoryController@update", ["auth"]);
        $router->delete(self::$prefix . "/{id}", "CategoryController@delete", ["auth"]);
    }
}
?>