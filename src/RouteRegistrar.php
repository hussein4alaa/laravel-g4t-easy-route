<?php

namespace G4T\EaseRoute;

use ReflectionClass;
use Illuminate\Support\Facades\Route;
use G4T\EaseRoute\Attributes\Route as ControllerRoute;
use G4T\EaseRoute\Attributes\Get;
use G4T\EaseRoute\Attributes\Post;
use G4T\EaseRoute\Attributes\Put;
use G4T\EaseRoute\Attributes\Patch;
use G4T\EaseRoute\Attributes\Delete;
use G4T\EaseRoute\Attributes\Any;

class RouteRegistrar
{
    public static function scanAndRegister(string $controllersPath)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllersPath)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace(app_path() . '/', '', $file->getPathname());
                $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                if (class_exists($class)) {
                    self::register($class);
                }
            }
        }
    }

    public static function register(string $controller)
    {
        if (!class_exists($controller)) return;

        $reflection = new ReflectionClass($controller);

        $controllerAttributes = $reflection->getAttributes(ControllerRoute::class);
        $baseUri = '';
        $controllerMiddleware = [];

        if ($controllerAttributes) {
            $instance = $controllerAttributes[0]->newInstance();
            $baseUri = rtrim($instance->uri ?? '', '/');
            $controllerMiddleware = $instance->middleware;
        }

        foreach ($reflection->getMethods() as $method) {
            $methodAttributes = array_merge(
                $method->getAttributes(Get::class),
                $method->getAttributes(Post::class),
                $method->getAttributes(Put::class),
                $method->getAttributes(Patch::class),
                $method->getAttributes(Delete::class),
                $method->getAttributes(Any::class),
            );

            foreach ($methodAttributes as $attr) {
                $instance = $attr->newInstance();
                $httpMethod = strtolower((new ReflectionClass($attr->getName()))->getShortName());

                if ($instance->onController) {
                    $controllerSegment = strtolower(str_replace('Controller', '', $reflection->getShortName()));
                    $methodSegment = strtolower($method->getName());
                    $uri = $controllerSegment . '/' . $methodSegment;
                    $params = array_map(fn($p) => '{'.$p->getName().'}', $method->getParameters());
                    if ($params) $uri .= '/' . implode('/', $params);
                } else {
                    $uri = $instance->uri ?? strtolower($method->getName());
                    $params = array_map(fn($p) => '{'.$p->getName().'}', $method->getParameters());
                    foreach ($params as $param) {
                        if (!str_contains($uri, $param)) $uri .= '/' . $param;
                    }
                }

                $fullUri = trim($baseUri . '/' . $uri, '/');

                $route = Route::$httpMethod($fullUri, [$controller, $method->getName()]);

                $middleware = array_merge($controllerMiddleware, $instance->middleware);
                if (!empty($middleware)) $route->middleware($middleware);

                if (!empty($instance->name)) $route->name($instance->name);
            }
        }
    }

    public static function getRoutesFromPath(string $controllersPath): array
    {
        $routes = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllersPath)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace(app_path() . '/', '', $file->getPathname());
                $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);
                if (class_exists($class)) {
                    $routes[$class] = self::getRoutesFromController($class);
                }
            }
        }

        return $routes;
    }

    public static function getRoutesFromController(string $controller): array
    {
        $reflection = new ReflectionClass($controller);
        $controllerAttributes = $reflection->getAttributes(ControllerRoute::class);
        $baseUri = '';
        $controllerMiddleware = [];

        if ($controllerAttributes) {
            $instance = $controllerAttributes[0]->newInstance();
            $baseUri = rtrim($instance->uri ?? '', '/');
            $controllerMiddleware = $instance->middleware;
        }

        $routes = [];

        foreach ($reflection->getMethods() as $method) {
            $methodAttributes = array_merge(
                $method->getAttributes(Get::class),
                $method->getAttributes(Post::class),
                $method->getAttributes(Put::class),
                $method->getAttributes(Patch::class),
                $method->getAttributes(Delete::class),
                $method->getAttributes(Any::class),
            );

            foreach ($methodAttributes as $attr) {
                $instance = $attr->newInstance();
                $httpMethod = strtoupper((new \ReflectionClass($attr->getName()))->getShortName());

                if ($instance->onController) {
                    $controllerSegment = strtolower(str_replace('Controller', '', $reflection->getShortName()));
                    $methodSegment = strtolower($method->getName());
                    $uri = $controllerSegment . '/' . $methodSegment;
                    $params = array_map(fn($p) => '{'.$p->getName().'}', $method->getParameters());
                    if ($params) $uri .= '/' . implode('/', $params);
                } else {
                    $uri = $instance->uri ?? strtolower($method->getName());
                    $params = array_map(fn($p) => '{'.$p->getName().'}', $method->getParameters());
                    foreach ($params as $param) {
                        if (!str_contains($uri, $param)) $uri .= '/' . $param;
                    }
                }

                $routes[] = [
                    'uri' => trim($baseUri . '/' . $uri, '/'),
                    'method' => $httpMethod,
                    'controller' => $controller,
                    'action' => $method->getName(),
                    'middleware' => array_merge($controllerMiddleware, $instance->middleware),
                    'name' => $instance->name ?? null,
                ];
            }
        }

        return $routes;
    }
}