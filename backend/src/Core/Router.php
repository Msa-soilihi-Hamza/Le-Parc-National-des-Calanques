<?php

declare(strict_types=1);

namespace ParcCalanques\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function __construct()
    {
        // Middleware global pour CORS et JSON
        $this->addGlobalMiddleware('cors');
        $this->addGlobalMiddleware('json');
    }

    public function get(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): self
    {
        $originalPrefix = $this->currentPrefix ?? '';
        $originalMiddlewares = $this->currentMiddlewares ?? [];
        
        $this->currentPrefix = $originalPrefix . $prefix;
        $this->currentMiddlewares = array_merge($originalMiddlewares, $middlewares);
        
        $callback($this);
        
        $this->currentPrefix = $originalPrefix;
        $this->currentMiddlewares = $originalMiddlewares;
        
        return $this;
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): self
    {
        $fullPath = ($this->currentPrefix ?? '') . $path;
        $allMiddlewares = array_merge(
            $this->currentMiddlewares ?? [],
            $middlewares
        );

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => $allMiddlewares,
            'params' => $this->extractParams($fullPath)
        ];

        return $this;
    }

    public function addGlobalMiddleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function dispatch(string $method, string $path): void
    {
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            $this->sendNotFound($path, $method);
            return;
        }

        try {
            // Exécuter middlewares globaux
            $this->runMiddlewares($this->middlewares);
            
            // Exécuter middlewares de route
            $this->runMiddlewares($route['middlewares']);
            
            // Exécuter le handler
            $this->runHandler($route['handler'], $route['matched_params'] ?? []);
            
        } catch (\Throwable $e) {
            $this->sendError($e);
        }
    }

    private function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $matched = $this->matchPath($route['path'], $path);
            if ($matched !== null) {
                $route['matched_params'] = $matched;
                return $route;
            }
        }

        return null;
    }

    private function matchPath(string $routePath, string $requestPath): ?array
    {
        // Supporte les paramètres comme /users/{id}
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Enlever le match complet
            
            // Extraire les noms des paramètres
            preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
            $params = [];
            
            foreach ($paramNames[1] as $index => $paramName) {
                $params[$paramName] = $matches[$index] ?? null;
            }
            
            return $params;
        }

        // Match exact si pas de paramètres
        return $routePath === $requestPath ? [] : null;
    }

    private function extractParams(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    private function runMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->runMiddleware($middleware);
        }
    }

    private function runMiddleware(string $middleware): void
    {
        switch ($middleware) {
            case 'cors':
                $this->handleCors();
                break;
            case 'json':
                $this->handleJson();
                break;
            case 'auth':
                $this->handleAuth();
                break;
            case 'admin':
                $this->handleAdmin();
                break;
        }
    }

    private function runHandler($handler, array $params = []): void
    {
        if (is_string($handler)) {
            // Format: 'ControllerClass@method'
            [$class, $method] = explode('@', $handler);
            $controller = new $class();
            $controller->$method($params);
        } elseif (is_callable($handler)) {
            $handler($params);
        } else {
            throw new \InvalidArgumentException('Invalid handler format');
        }
    }

    private function handleCors(): void
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: " . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
            }

            http_response_code(200);
            exit;
        }
    }

    private function handleJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    private function handleAuth(): void
    {
        // Intégrer votre JwtMiddleware existant
        $jwtMiddleware = \ParcCalanques\Auth\AuthBootstrap::jwtMiddleware();
        $jwtMiddleware->authenticate();
    }

    private function handleAdmin(): void
    {
        $jwtMiddleware = \ParcCalanques\Auth\AuthBootstrap::jwtMiddleware();
        $jwtMiddleware->requireAdmin();
    }

    private function sendNotFound(string $path, string $method): void
    {
        http_response_code(404);
        echo json_encode([
            'error' => 'Not Found',
            'message' => 'API endpoint not found',
            'code' => 404,
            'path' => $path,
            'method' => $method
        ]);
    }

    private function sendError(\Throwable $e): void
    {
        $code = $e->getCode() ?: 500;
        
        if ($code < 100 || $code > 599) {
            $code = 500;
        }

        http_response_code($code);
        
        $response = [
            'error' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $code
        ];

        // En développement, ajouter plus de détails
        if ($_ENV['APP_ENV'] ?? 'production' !== 'production') {
            $response['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        echo json_encode($response);
        error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    }
}