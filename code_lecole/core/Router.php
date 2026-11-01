<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->addRoute('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->addRoute('POST', $path, $action);
    }

    private function addRoute(string $method, string $path, array $action): void
    {
        $this->routes[$method][$path] = $action;
    }

    public function dispatch(string $method, string $path): void
    {
        $action = $this->routes[$method][$path] ?? null;

        // Unknown page → visitor sees the 404 page (not an error)
        if ($action === null)
        {
            http_response_code(404);
            $code    = 404;
            $heading = 'Page not found';
            $message = "This page doesn't exist or has been moved.";
            require dirname(__DIR__) . '/app/Views/errors/error.php';
            return;
        }

        [$controllerClass, $controllerMethod] = $action;
        $arguments = array_slice($action, 2);

        // Route points to a missing controller/method → developer mistake, so throw
        if (!method_exists($controllerClass, $controllerMethod))
        {
            throw new RuntimeException("Route action not found: {$controllerClass}::{$controllerMethod}");
        }

        $controller = new $controllerClass();

        // Every form sent (POST) passes this one gate first: without this visitor's CSRF token it never reaches the action
        if ($method === 'POST')
        {
            $controller->checkCsrf();
        }

        $controller->$controllerMethod(...$arguments);
    }
}
