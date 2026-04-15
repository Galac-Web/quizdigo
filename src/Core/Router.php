<?php
namespace Evasystem\Core;

use Evasystem\Core\AdvancedCRUD;
use Evasystem\Core\PageLoader;

class Router
{
    private array $routes = [];

    public function addRoute($method, $route, $controller = null, $action = null, $dir = '')
    {
        // fallback rapid
        if (func_num_args() === 3) {
            $dir = $controller;
            $controller = 'Admin';
            $action = 'rootFunction';
        }

        $this->routes[] = [
            'method'     => $method,
            'route'      => $route,
            'controller' => $controller,
            'action'     => $action,
            'dir'        => $dir
        ];
    }

    public function loadRoutesFromDatabase(): void
    {
        $rows = AdvancedCRUD::select('routes', '*', "WHERE is_active = 1");

        foreach ($rows as $row) {
            $method     = $row['method'];
            $path       = $row['path'];
            $controller = $row['controller'];
            $action     = $row['action'];
            $dir        = $row['dir'];

            $this->addRoute($method, $path, $controller, $action, $dir);
        }
    }

    public function handleRequest()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['route'] === $requestUri) {
                $controller = $route['controller'] ?? null;
                $action     = $route['action'] ?? null;
                $dir        = $route['dir'] ?? '';

                if (!empty($controller) && !empty($action)) {
                    $controllerName = "Evasystem\\Controllers\\" . $controller;

                    if (class_exists($controllerName)) {
                        $controllerInstance = new $controllerName();

                        if (method_exists($controllerInstance, $action)) {
                            $controllerInstance->$action($dir);
                            return;
                        } else {
                            throw new \Exception("Method $action not found in $controllerName");
                        }
                    } else {
                        throw new \Exception("Controller class $controllerName not found");
                    }
                } else {
                    // Nu are controller - fallback PageLoader
                    $this->handleStaticPage($requestUri);
                    return;
                }
            }
        }

        // Dacă nu a fost găsit nimic
        $this->handleStaticPage($requestUri);
    }

    private function handleStaticPage(string $uri)
    {
        $page = trim($uri, '/');
        if ($page === '') {
            $page = '/'; // root
        } else {
            $page = '/' . $page;
        }

        $results = AdvancedCRUD::select('routes', '*', "WHERE path = '$page' AND is_active = 1");

        if (!empty($results)) {
            $route = $results[0];

            if (!empty($route['file_path']) && !empty($route['load_type'])) {
                $loader = new PageLoader([trim($page, '/') => $route['file_path']]);
                if (method_exists($loader, $route['load_type'])) {
                    $loader->{$route['load_type']}();
                    return;
                } else {
                    echo "⚠️ Metoda {$route['load_type']} nu există în PageLoader.";
                    return;
                }
            }
        }

        // Nimic găsit deloc
        http_response_code(404);
        echo "<h1>404 - Pagină inexistentă</h1>";
    }
}
?>