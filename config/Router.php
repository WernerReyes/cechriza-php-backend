<?php
class Router
{
    private $routes = [];

    public function get($path, $action, $middlewares = [])
    {
        $this->addRoute("GET", $path, $action, $middlewares);
    }
    public function post($path, $action, $middlewares = [])
    {
        $this->addRoute("POST", $path, $action, $middlewares);
    }

    public function put($path, $action, $middlewares = [])
    {
        $this->addRoute("PUT", $path, $action, $middlewares);
    }

    public function delete($path, $action, $middlewares = [])
    {
        $this->addRoute("DELETE", $path, $action, $middlewares);
    }

    private function addRoute($method, $path, $action, $middleware = [])
    {
        $this->routes[] = ["method" => $method, "path" => $path, "action" => $action, "middleware" => $middleware];
    }

    public function dispatch()
    {
        $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        // 👇 Este es el nombre de tu carpeta del proyecto en htdocs
        $basePath = $_ENV['BASE_PATH'] ?? '';

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
            $pattern = "@^" . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route["path"]) . "$@";
            if ($method === $route["method"] && preg_match($pattern, $url, $matches)) {
                array_shift($matches);
                list($controller, $method) = explode("@", $route["action"]);

                error_log("app/controllers/$controller.php");
                // Ejecutar middleware si existe
                if (!empty($route["middleware"])) {
                    $this->runMiddleware($route["middleware"]);
                }

                require_once "app/controllers/$controller.php";
                $obj = new $controller;
                call_user_func_array([$obj, $method], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
    }

    private function runMiddleware(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            switch ($middleware) {
                case 'auth':
                    AuthMiddleware::authenticate();
                    break;
                case 'admin':
                    AuthMiddleware::requireRole('ADMIN');
                    break;
                // Agregar más middleware aquí
            }
        }
    }


}
