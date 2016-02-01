<?php

namespace Micro\Application;

use Exception as CoreException;
use Micro\Http;
use Micro\Container\ContainerAwareInterface;
use Micro\Container\ContainerAwareTrait;

class Router implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \Micro\Http\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var array
     */
    protected $routesStatic = [];

    /**
     * @var array
     */
    protected $globalParams = [];

    /**
     * @var \Micro\Application\Route
     */
    protected $currentRoute;

    /**
     * @var string
     */
    const URL_DELIMITER = '/';

    /**
     * @param \Micro\Http\Request $request
     */
    public function __construct(Http\Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string|null $requestUri
     * @return \Micro\Application\Route|null
     */
    public function match($requestUri = \null)
    {
        if ($requestUri === \null) {
            $requestUri = $this->request->getPathInfo();
        }

        if ($requestUri !== static::URL_DELIMITER) {
            $requestUri = rtrim($requestUri, static::URL_DELIMITER);
        }

        if (isset($this->routesStatic[$requestUri])) {
            return $this->currentRoute = $this->routes[$this->routesStatic[$requestUri]];
        }

        foreach ($this->routes as $route) {
            if ($route instanceof Route && $route->match($requestUri)) {
                return $this->currentRoute = $route;
            }
        }

        return \null;
    }

    /**
     * @param string $pattern
     * @param \Closure|string $handler
     * @param string $name
     * @throws \Exception
     * @return \Micro\Application\Route
     */
    public function map($pattern, $handler, $name = \null)
    {
        if (\null === $name) {
            $name = preg_replace('~[^\w]~', '-', $pattern);
            $name = preg_replace('~[\-]+~', '-', $name);
            $name = 'route-' . trim($name, '-');
        }

        if (isset($this->routes[$name])) {
            throw new CoreException(sprintf('[' . __METHOD__ . '] Route "%s" already exists!', $name), 500);
        }

        $pattern = static::URL_DELIMITER . trim($pattern, static::URL_DELIMITER);

        $route = new Route($name, $pattern, $handler);

        if (Route::isStatic($pattern)) {
            if (isset($this->routesStatic[$pattern])) {
                throw new CoreException(sprintf('[' . __METHOD__ . '] Route pattern "%s" already exists!', $pattern), 500);
            }
            $this->routesStatic[$pattern] = $name;
        }

        $this->routes[$name] = $route;

        return $route;
    }

    /**
     * @param string $name
     * @param array $data
     * @param boolean $qsa
     * @throws \Exception
     * @return string
     */
    public function assemble($name, array $data = [], $reset = \false, $qsa = \true)
    {
        if ($name === \null && $this->currentRoute instanceof Route) {
            $name = $this->currentRoute->getName();
        }

        if (!isset($this->routes[$name])) {
            throw new CoreException(sprintf('[' . __METHOD__ . '] Route "%s" not found!', $name), 500);
        }

        $route = $this->routes[$name];

        $pattern = $route->getPattern();

        $data += $this->globalParams;

        if ($this->currentRoute instanceof Route
            && $name === $this->currentRoute->getName()
            && $reset === \false
        ) {
            $data += $this->currentRoute->getParams();
        }

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

            $qs = array_filter($qs);

            if (!empty($qs)) {
                $url .= '?' . http_build_query($qs);
            }
        }

        return $this->request->getBaseUrl() . static::URL_DELIMITER . trim($url, static::URL_DELIMITER);
    }

    /**
     * @param string $key
     * @param string $value
     * @return \Micro\Application\Router
     */
    public function setGlobalParam($key, $value)
    {
        $this->globalParams[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param string|null $value
     * @return multitype:|unknown
     */
    public function getGlobalParam($key, $value = \null)
    {
        if (isset($this->globalParams[$key])) {
            return $this->globalParams[$key];
        }

        return $value;
    }

    /**
     * @return array of \Micro\Application\Route's
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param string $name
     * @return \Micro\Application\Route|\null
     */
    public function getRoute($name)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        return \null;
    }

    /**
     * @param \Micro\Application\Route $route
     * @return \Micro\Application\Router
     */
    public function setCurrentRoute(Route $route)
    {
        $this->currentRoute = $route;

        return $this;
    }

    /**
     * @return \Micro\Application\Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function loadDefaultRoutes()
    {
        if (!isset($this->routes['admin'])) {

            $route = $this->map('/admin[/{package}][/{controller}][/{action}][/{id}][/{wildcard}]', function () {

                static $cache = [];

                $params = $this->getParams();

                $hash = 'admin_' . $params['package'] . '_' . $params['controller'] . '_' . $params['action'] . '_' . $params['id'] . '_' . $params['wildcard'];

                if (isset($cache[$hash])) {
                    return $cache[$hash];
                }

                $package = \ucfirst(Utils::camelize($params['package']));
                $controller = \ucfirst(Utils::camelize($params['controller']));
                $action = \lcfirst(Utils::camelize($params['action']));

                return $cache[$hash] = $package . '\\Controller\Admin\\' . $controller . '@' . $action;

            }, 'admin');

            $route->setDefaults(['package' => 'app', 'controller' => 'index', 'action' => 'index', 'id' => \null, 'wildcard' => \null]);

            $route->setConditions(['package' => '[^\/]+', 'controller' => '[^\/]+', 'action' => '[^\/]+', 'wildcard' => '.+']);
        }

        if (!isset($this->routes['default'])) {

            $route = $this->map('/{package}[/{controller}][/{action}][/{id}][/{wildcard}]', function () {

                static $cache = [];

                $params = $this->getParams();

                $hash = 'front_' . $params['package'] . '_' . $params['controller'] . '_' . $params['action'] . '_' . $params['id'] . '_' . $params['wildcard'];

                if (isset($cache[$hash])) {
                    return $cache[$hash];
                }

                $package = \ucfirst(Utils::camelize($params['package']));
                $controller = \ucfirst(Utils::camelize($params['controller']));
                $action = \lcfirst(Utils::camelize($params['action']));

                return $cache[$hash] = $package . '\\Controller\Front\\' . $controller . '@' . $action;

            }, 'default');

            $route->setDefaults(['package' => 'app', 'controller' => 'index', 'action' => 'index', 'id' => \null, 'wildcard' => \null]);

            $route->setConditions(['package' => '[^\/]+', 'controller' => '[^\/]+', 'action' => '[^\/]+', 'wildcard' => '.+']);
        }

        return $this;
    }

    public function mapFromConfig()
    {
        $routes = $this->container->get('config')->get('routes', []);

        foreach ($routes as $name => $config) {

            if (!isset($config['pattern']) || !isset($config['handler'])) {
                continue;
            }

            $route = $this->map($config['pattern'], $config['handler'], $name);

            if (isset($config['conditions'])) {
                $route->setConditions($config['conditions']);
            }

            if (isset($config['defaults'])) {
                $route->setDefaults($config['defaults']);
            }
        }

        return $this;
    }
}