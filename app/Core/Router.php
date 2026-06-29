<?php
namespace app\Core;

class Router {
    private $routes = [];

    public function get($uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    public function delete($uri, $action) {
        $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute($method, $uri, $action) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function dispatch($uri, $method) {
        foreach ($this->routes as $route) {
            // Regex to match params like {id}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                
                $action = $route['action'];
                if (is_array($action)) {
                    $controller = new $action[0]();
                    $methodName = $action[1];
                    return call_user_func_array([$controller, $methodName], $matches);
                }
            }
        }

        // 404
        http_response_code(404);
        echo "404 Not Found";
    }
}
