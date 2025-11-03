<?php
class MachineRoutes
{
    private static string $prefix = '/machine';
    public static function routes(
        Router $router
    ) {

        $router->get(self::$prefix, "MachineController@getAll", ["auth"]);
        $router->post(self::$prefix, "MachineController@create", ["auth"]);
        $router->post(self::$prefix . '/{id}', "MachineController@update", ["auth"]);
        $router->put(self::$prefix . '/{id}/set-main-image', "MachineController@setImageAsMain", ["auth"]);
        $router->delete(self::$prefix . '/{id}', "MachineController@delete", ["auth"]);
    }
}