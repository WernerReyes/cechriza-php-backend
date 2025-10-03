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
    }
}
?>