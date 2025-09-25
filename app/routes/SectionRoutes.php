<?php
require_once "config/Router.php";
require_once "app/controllers/SectionController.php";
class SectionRoutes
{
    private static string $prefix = "/section";

    public static function routes(
        Router $router,
    ) {
        $router->post(self::$prefix, "SectionController@create", ["auth"]);
    }
}
?>