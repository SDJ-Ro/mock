<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();

        $routeFile = dirname(__DIR__) . '/routes/web.php';
        $registerRoutes = require $routeFile;

        if (!is_callable($registerRoutes))
        {
            throw new RuntimeException(
                'The web routes file must return a callable.'
            );
        }

        $registerRoutes($this->router);
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // HEAD = a GET without the page itself ("is it there?"). "Stay Signed In" (session-timeout.js) sends one; as a
        // GET it reaches requireRole(), which counts it as activity. PHP leaves the body out by itself.
        if ($method === 'HEAD')
        {
            $method = 'GET';
        }

        $path = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        if (!is_string($path) || $path === '')
        {
            $path = '/';
        }

        if ($path !== '/')
        {
            $path = rtrim($path, '/');
        }

        $this->router->dispatch($method, $path);
    }
}