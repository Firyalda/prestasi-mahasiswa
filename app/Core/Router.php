<?php
namespace App\Core;

class Router {
    private Request $request;
    private Response $response;
    private array $routes = [];

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, callable|array|string $callback, array $middlewares = []): void {
        $this->routes['GET'][$this->normalizePath($path)] = [
            'callback'    => $callback,
            'middlewares' => $middlewares
        ];
    }

    public function post(string $path, callable|array|string $callback, array $middlewares = []): void {
        $this->routes['POST'][$this->normalizePath($path)] = [
            'callback'    => $callback,
            'middlewares' => $middlewares
        ];
    }

    private function normalizePath(string $path): string {
        $path = trim($path, '/');
        return '/' . $path;
    }

    public function resolve(): mixed {
        $method = $this->request->getMethod();
        $path = $this->request->getUri();

        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            // Check for direct file loading or fallback
            $this->response->setStatusCode(404);
            echo "404 - Halaman Tidak Ditemukan: " . htmlspecialchars($path);
            return null;
        }

        // Execute Middlewares
        foreach ($route['middlewares'] as $middleware) {
            if ($middleware instanceof Middleware) {
                $middleware->execute();
            } elseif (is_string($middleware) && class_exists($middleware)) {
                $instance = new $middleware();
                if ($instance instanceof Middleware) {
                    $instance->execute();
                }
            }
        }

        $callback = $route['callback'];

        if (is_array($callback)) {
            $controllerClass = $callback[0];
            $action = $callback[1];

            $controller = new $controllerClass();
            return call_user_func([$controller, $action]);
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        return null;
    }
}
