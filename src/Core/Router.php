<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $groupPrefix = '';

    public function group($prefix, $callback)
    {
        $previousPrefix = $this->groupPrefix;
        $this->groupPrefix .= $prefix;
        $callback($this);
        $this->groupPrefix = $previousPrefix;
    }

    public function get($path, $callback)
    {
        $path = $this->groupPrefix . $path;
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $path = $this->groupPrefix . $path;
        $this->routes['POST'][$path] = $callback;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove query string and trailing slash if necessary
        // For simplicity, we assume exact match or simple patterns
        
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];

            // Handle Middleware if defined in the route (advanced)
            // For now, we assume callback is an array [Controller, Method]
            
            if (is_array($callback)) {
                $controller = new $callback[0]();
                $method = $callback[1];
                return $controller->$method();
            }

            // Handle Closure
            if (is_callable($callback)) {
                return call_user_func($callback);
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}
