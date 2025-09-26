<?php
require_once "app/controllers/SectionItemController.php";
class SectionItemRoutes
{

    private static string $prefix = "/section-item";

    public static function routes(
        Router $router
    ) {
        // $router->get(self::$prefix, "SectionItemController@getAll", ["auth"]);
        $router->post(self::$prefix, "SectionItemController@create", ["auth"]);
        // $router->put(self::$prefix . "/{id}", "SectionItemController@update", ["auth"]);
        // $router->delete(self::$prefix . "/{id}", "SectionItemController@delete", ["auth"]);

    }
}