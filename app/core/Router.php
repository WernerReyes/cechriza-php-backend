<?php
class Router
{
    private $routes = [];

    public function get($path, $action)
    {
        $this->addRoute("GET", $path, $action);
    }
    public function post($path, $action)
    {
        $this->addRoute("POST", $path, $action);
    }

    public function put($path, $action)
    {
        $this->addRoute("PUT", $path, $action);
    }

    public function delete($path, $action)
    {
        $this->addRoute("DELETE", $path, $action);
    }

    private function addRoute($method, $path, $action)
    {
        $this->routes[] = ["method" => $method, "path" => $path, "action" => $action];
    }

    public function dispatch()
    {
        $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        // 👇 Este es el nombre de tu carpeta del proyecto en htdocs
        $basePath = "/api";

        // Elimina el prefijo de la URL si existe
        if (strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }

        // Si quedó vacío, significa que estás en la raíz "/"
        if ($url === "" || $url === false) {
            $url = "/";
        }

        $method = $_SERVER["REQUEST_METHOD"];

        foreach ($this->routes as $route) {
            $pattern = "@^" . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([0-9]+)', $route["path"]) . "$@";
            if ($method === $route["method"] && preg_match($pattern, $url, $matches)) {
                array_shift($matches);
                list($controller, $method) = explode("@", $route["action"]);

                error_log("app/controllers/$controller.php");
                require_once "app/controllers/$controller.php";
                $obj = new $controller;
                call_user_func_array([$obj, $method], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
    }
}
