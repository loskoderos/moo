<?php

namespace Moo;

class Moo {
    public Request $request;
    public Response $response;
    public Router $router;

    public ?\Closure $init;
    public ?\Closure $finish;
    public ?\Closure $error;
    public ?\Closure $flush;

    public function __construct()
    {
        $this->router = new Router();
        $this->before = function() {};
        $this->after = function() {};
        $this->error = function(\Exception $exc) {
            $this->response->code = $exc->getCode() > 0 ? $exc->getCode() : 500;
            $this->response->message = StatusCode::message($exc->getCode());
            $this->response->headers->clear();
            $this->response->body = $exc->getMessage();
        };
        $this->flush = function() {
            header('HTTP/1.1 ' . $this->response->code . ' ' . $this->response->message);
            foreach ($this->response->headers as $name => $value) {
                header($name.': '.$value, true);
            }
            echo $this->response->body;
        };
    }

    public function route(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['*'], [$uri], $callback);
        return $this;
    }

    public function get(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['GET'], [$uri], $callback);
        return $this;
    }

    public function head(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['HEAD'], [$uri], $callback);
        return $this;
    }

    public function post(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['POST'], [$uri], $callback);
        return $this;
    }

    public function put(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['PUT'], [$uri], $callback);
        return $this;
    }

    public function delete(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['DELETE'], [$uri], $callback);
        return $this;
    }

    public function connect(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['CONNECT'], [$uri], $callback);
        return $this;
    }

    public function options(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['OPTIONS'], [$uri], $callback);
        return $this;
    }

    public function trace(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['TRACE'], [$uri], $callback);
        return $this;
    }

    public function patch(string $uri, ?callable $callback): Moo
    {
        $this->router->register(['PATCH'], [$uri], $callback);
        return $this;
    }

    protected function _dispatch(?Request $request = null, ?Response $response = null): mixed
    {
        $this->request = isset($request) ? $request : $this->router->requestFactory();
        $this->response = isset($response) ? $response : new Response();

        ob_start();
        try {
            is_callable($this->before) ? $this->before() : null;
            
            $result = $this->router->dispatch($this->request, $this->response);
            if ($result !== null) {
                $this->response->body = $result;
            }
            
            is_callable($this->after) ? $this->after() : null;

        } catch (\Exception $exc) {
            is_callable($this->error) ? $this->error($exc) : throw $exc;
        }
        $this->response->body = $this->response->body . ob_get_clean();

        return ob_get_level() <= 1 && is_callable($this->flush) ? $this->flush() : null;
    }

    public function __call($name, $args)
    {
        foreach ($this as $key => $callback) {
            if ($key == $name) {
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $args);
                } else {
                    throw new \BadMethodCallException("Moo::$name is not callable");
                }      
            }
        }
        throw new \BadMethodCallException("Moo::$name is not callable");
    }

    public function __invoke(?Request $request = null, ?Response $response = null): mixed
    {
        return $this->_dispatch($request, $response);
    }
}
