<?php
require_once "config/Router.php";
require_once "app/routes/AuthRoutes.php";
require_once "app/routes/MenuRoutes.php";
require_once "app/routes/PageRoutes.php";
require_once "app/routes/SectionRoutes.php";
require_once "app/routes/SectionItemRoutes.php";
require_once "app/routes/CategoryRoutes.php";
require_once "app/routes/LinkRoutes.php";
require_once "app/routes/MachineRoutes.php";

class AppRoutes
{
    public static function routes()
    {
        $router = new Router();

        AuthRoutes::routes($router);
        MenuRoutes::routes($router);
        PageRoutes::routes($router);
        SectionRoutes::routes($router);
        SectionItemRoutes::routes($router);
        CategoryRoutes::routes($router);
        LinkRoutes::routes($router);
        MachineRoutes::routes($router);

        $router->dispatch();
    }
}
?>