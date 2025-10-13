<?php
require_once "config/Router.php";
require_once "app/controllers/SectionController.php";
class SectionRoutes
{
    private static string $prefix = "/section";

    public static function routes(
        Router $router,
    ) {
        $router->get(self::$prefix, "SectionController@getAll", ["auth"]);
        $router->post(self::$prefix, "SectionController@create", ["auth"]);
        $router->post(self::$prefix . "/{id}", "SectionController@update", ["auth"]);
        $router->put(self::$prefix . "/order", "SectionController@updateOrder", ["auth"]);
        $router->delete(self::$prefix . "/{id}", "SectionController@delete", ["auth"]);

    }
}