<?php
class MachineRoutes {
    private static string $prefix = '/machine';
    public static function routes(
        Router $router
    ) {

        $router->get(self::$prefix, "MachineController@getAll", ["auth"]);
        $router->post(self::$prefix, "MachineController@create", ["auth"]);
        $router->post(self::$prefix . '/{id}', "MachineController@update", ["auth"]);
    }
}