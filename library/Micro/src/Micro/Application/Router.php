<?php

namespace Micro\Application;

use Micro\Http;

class Router
{
    /**
     * @var \Micro\Http\Request
     */
    protected $request;

    protected $routes = [];
    protected $routesStatic = [];
    protected $globalParams = [];

    /**
     * @param \Micro\Http\Request $request
     */
    public function __construct(Http\Request $request)
    {
        $this->request = $request;
    }

    public function match($requestUri = \null)
    {
        if ($requestUri === \null) {
            $requestUri = $this->request->getPathInfo();
        }

        if ($requestUri !== '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        if (isset($this->routesStatic[$requestUri])) {
            return $this->routes[$this->routesStatic[$requestUri]];
        }

        foreach ($this->routes as $route) {
            if ($route->match($requestUri)) {
                return $route;
            }
        }

        return null;
    }

    public function map($name, $pattern, $handler)
    {
        if (isset($this->routes[$name])) {
            throw new \Exception(sprintf('Route "%s" already exists!', $name), 500);
        }

        $route = new Route($name, $pattern, $handler);

        if (Route::isStatic($pattern)) {
            if (isset($this->routesStatic[$pattern])) {
                throw new \Exception(sprintf('Route pattern "%s" already exists!', $pattern), 500);
            }
            $this->routesStatic[$pattern] = $name;
        }

        $this->routes[$name] = $route;

        return $route;
    }

    public function assemble($name, array $data = [], $qsa = \false)
    {
        if (!isset($this->routes[$name])) {
            throw new \Exception(sprintf('Route "%s" not found!', $name), 500);
        }

        $route = $this->routes[$name];

        $pattern = $route->getPattern();

        $data += $this->globalParams;

        if (isset($this->routesStatic[$pattern])) {
            $url = $pattern;
        } else {
            $url = $route->assemble($data);
        }

        if ($qsa === \true) {

            $qs = $this->request->getQuery();

            if (!empty($data)) {
                $qs = $data + $qs;
            }

            if (!empty($qs)) {
                $url .= '?' . http_build_query($qs);
            }
        }

        return $this->request->getBaseUrl() . '/' . trim($url, '/');
    }

    public function setGlobalParam($key, $value)
    {
        $this->globalParams[$key] = $value;

        return $this;
    }

    public function getGlobalParam($key, $value = \null)
    {
        if (isset($this->globalParams[$key])) {
            return $this->globalParams[$key];
        }

        return $value;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getRoute($name)
    {
        return isset($this->routes[$name]) ? $this->routes[$name] : \null;
    }
}