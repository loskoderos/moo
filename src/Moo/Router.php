<?php

namespace Moo;

use LosKoderos\Generic\Collection\Collection;

class Router
{
    public Collection $routes;
    
    public function __construct()
    {
        $this->routes = new Collection();
    }

    public function register(array $methods, array $uris, ?callable $callback): Router
    {
        foreach ($methods as $method) {
            foreach ($uris as $uri) {
                $route = new Route();
                $route->method = $method;
                $route->uri = $uri;
                $route->callback = $callback;
                $key = $method.' '.trim($uri, '/');
                $this->routes->set($key, $route);
            }
        }
        return $this;
    }

    public function dispatch(Request $request): mixed
    {
        $uri = parse_url(preg_replace('/(\/+)/', '/', $request->uri))['path'];
        foreach ($this->routes as $route) {
            if ($route->method == '*' || $route->method == $request->method) {
                if (preg_match("#^{$route->uri}$#", $uri, $matches)) {
                    if (is_callable($route->callback)) {
                        return call_user_func_array($route->callback, array_slice($matches, 1));
                    } else {
                        throw new \RuntimeException("Route $method $uri is not callable");
                    }
                }
            }
        }
        throw new \RuntimeException(StatusCode::message(404), 404);
    }

    public static function requestFactory(): Request
    {
        $request = new Request();
        $request->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $request->uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $request->headers->populate($_SERVER);
        $request->query->populate($_GET);
        $request->post->populate($_POST);
        $request->files->populate($_FILES);
        return $request;
    }
}
