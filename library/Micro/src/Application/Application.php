<?php

namespace Micro\Application;

use Micro\Http;
use Micro\Event;
use Micro\Container\Container;
use Micro\Container\ContainerAwareInterface;
use Micro\Cache\Cache;
use Micro\Session\Session;
use Micro\Database\Database;
use Micro\Acl\Acl;

class Application extends Container
{
    /**
     * @var array
     */
    private $packages = [];

    /**
     * Constructor
     * @param array | string $config
     * @throws \InvalidArgumentException
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new Config($config);
        } elseif (is_string($config) && file_exists($config)) {
            $config = new Config(include $config);
        } else if (!$config instanceof Config) {
            throw new \InvalidArgumentException('[' . __METHOD__ . '] Config param must be valid file or array', 500);
        }

        \MicroLoader::addPath($config->get('packages', []), \null, 'src');

        $this['config'] = $config;

        static::setInstance($this);
    }

    /**
     * Start the application
     * @return \Micro\Application\Application
     */
    public function run()
    {
        try {

            $this->boot();

            if (($eventResponse = $this['event']->trigger('application.start', ['request' => $this['request']])) instanceof Http\Response) {
                $response = $eventResponse;
            } else {
                $response = $this->start();
            }

            if (($eventResponse = $this['event']->trigger('application.end', ['response' => $response])) instanceof Http\Response) {
                $response = $eventResponse;
            }

            $response->send();

        } catch (\Exception $e) {
            if (env('development')) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * @return \Micro\Application\Application
     */
    public function registerDefaultServices()
    {
        if (!isset($this['request'])) {
            $this['request'] = function () {
                return new Http\Request();
            };
        }

        if (!isset($this['response'])) {
            $this['response'] = function () {
                return new Http\Response\HtmlResponse();
            };
        }

        if (!isset($this['event'])) {
            $this['event'] = function () {
                return new Event\Manager();
            };
        }

        if (!isset($this['exception.handler'])) {
            $this['exception.handler'] = function ($app) {
                return $app;
            };
        }

        if (!isset($this['acl'])) {
            $this['acl'] = function () {
                return new Acl();
            };
        }

        if (!isset($this['caches'])) {
            $this['caches'] = function ($app) {
                $adapters = $app['config']->get('cache.adapters', []);
                $caches = [];
                foreach ($adapters as $adapter => $config) {
                    $caches[$adapter] = Cache::factory(
                        $config['frontend']['adapter'], $config['backend']['adapter'],
                        $config['frontend']['options'], $config['backend']['options']
                    );
                }
                return $caches;
            };
        }

        if (!isset($this['cache'])) {
            $this['cache'] = function ($app) {
                $adapters = $app->get('caches');
                $default  = (string) $app['config']->get('cache.default');
                return isset($adapters[$default]) ? $adapters[$default] : \null;
            };
        }

        /**
         * Create router with routes
         */
        if (!isset($this['router'])) {
            $this['router'] = function ($app) {
                $router = new Router($app['request']);
                $router->mapFromConfig($app['config']->get('routes', []));
                return $router;
            };
        }

        /**
         * Create default db adapter
         */
        if (!isset($this['db'])) {
            $this['db'] = function ($app) {
                $default  = $app['config']->get('db.default');
                $adapters = $app['config']->get('db.adapters', []);
                return Database::factory($adapters[$default]['adapter'], $adapters[$default]);
            };
        }

        /**
         * Register session config
         */
        Session::register($this['config']->get('session', []));

        return $this;
    }

    /**
     * Unpackage the application request
     * @return \Micro\Http\Response
     */
    public function start()
    {
        $response = $this['response'];

        try {

            if (($route = $this['router']->match()) === \null) {
                throw new \Exception('[' . __METHOD__ . '] Route not found', 404);
            }

            if (($packageResponse = $this->unpackage($route)) instanceof Http\Response) {
                $response = $packageResponse;
            }

        } catch (\Exception $e) {

            try {

                if (($exceptionResponse = $this['exception.handler']->handleException($e)) instanceof Http\Response) {
                    return $exceptionResponse;
                }

                if (env('development')) {
                    $response->setBody((string) $exceptionResponse);
                }

            } catch (\Exception $e) {

                if (env('development')) {
                    $response->setBody($e->getMessage());
                }
            }
        }

        return $response;
    }

    /**
     * @param \Exception $e
     * @throws \Exception
     * @return \Micro\Http\Response
     */
    public function handleException(\Exception $e)
    {
        $errorHandler = $this['config']->get('error');

        if ($errorHandler === \null || !isset($errorHandler['route'])) {
            throw $e;
        }

        $route = $this['router']->getRoute($errorHandler['route']);

        if ($route === \null) {
            throw new \Exception('[' . __METHOD__ . '] Error route not found', 404);
        }

        $route->setParams($this['config']->get('error.params', []) + ['exception' => $e]);

        $this['router']->setCurrentRoute($route);

        return $this->unpackage($route);
    }

    /**
     * Boot the application
     * @throws \Exception
     */
    public function boot()
    {
        $this->registerDefaultServices();

        $packages = $this['config']->get('packages', []);

        foreach ($packages as $package => $path) {
            $packageInstance = $package . '\\Package';
            if (class_exists($packageInstance, \true)) {
                $instance = new $packageInstance($this);
                if (!$instance instanceof Package) {
                    throw new \RuntimeException(sprintf('[' . __METHOD__ . '] %s must be instance of Micro\Application\Package', $packageInstance), 500);
                }
                $instance->setContainer($this);
                $instance->boot();
                $this->packages[$package] = $instance;
            }
        }
    }

    /**
     * Unpackage the application request
     * @param \Micro\Application\Route $route
     * @throws \Exception
     * @return \Micro\Http\Response
     */
    public function unpackage(Route $route)
    {
        $this['request']->setParams($route->getParams() + $route->getDefaults());

        if (($eventResponse = $this['event']->trigger('unpackage.start', compact('route'))) instanceof Http\Response) {
            return $eventResponse;
        }

        $routeHandler = $route->getHandler();

        if (is_string($routeHandler) && strpos($routeHandler, '@') !== \false) { // package format
            $routeHandler = $this->resolve($routeHandler, $this['request'], $this['response']);
        }

        if ($routeHandler instanceof Http\Response) {
            $response = $routeHandler;
        } else {
            $response = $this['response']->setBody((string) $routeHandler);
        }

        if (($eventResponse = $this['event']->trigger('unpackage.end', compact('response'))) instanceof Http\Response) {
            return $eventResponse;
        }

        return $response;
    }

    /**
     * @param string $package
     * @param Http\Request $request
     * @param Http\Response $response
     * @param bool $subRequest
     * @throws \Exception
     * @return \Micro\Http\Response
     */
    public function resolve($package, Http\Request $request, Http\Response $response, $subRequest = \false)
    {
        if (!is_string($package) || strpos($package, '@') === \false) {
            throw new \Exception('[' . __METHOD__ . '] Package must be Package\Handler@action format', 500);
        }

        list($package, $action) = explode('@', $package);

        if (!class_exists($package, \true)) {
            throw new \Exception('[' . __METHOD__ . '] Package class "' . $package . '" not found', 404);
        }

        $packageInstance = new $package($request, $response);

        if (!method_exists($packageInstance, $action)) {
            throw new \Exception('[' . __METHOD__ . '] Method "' . $action . '" not found in "' . $package . '"', 404);
        }

        if ($packageInstance instanceof ContainerAwareInterface) {
            $packageInstance->setContainer($this);
        }

        if ($packageInstance instanceof Controller) {
            $packageInstance->init();
        }

        if (($packageResponse = $packageInstance->$action()) instanceof Http\Response) {
            return $packageResponse;
        }

        if (is_object($packageResponse) && !$packageResponse instanceof View) {
            throw new \Exception('[' . __METHOD__ . '] Package response is object and must be instance of View', 500);
        }

        if (is_array($packageResponse) || $packageResponse === \null) {
            $packageResponse = new View(\null, $packageResponse);
            $packageResponse->setTemplate(Utils::decamelize($action));
        }

        $parts = explode('\\', $package);

        if ($packageResponse instanceof View) {
            $packageResponse->injectPaths((array) package_path($parts[0], 'views'));
            if (($eventResponse = $this['event']->trigger('render.start', ['view' => $packageResponse])) instanceof Http\Response) {
                return $eventResponse;
            }
            if ($subRequest) {
                $packageResponse->setRenderParent(\false);
            }
            $response->setBody((string) $packageResponse->render());
        } else {
            $response->setBody((string) $packageResponse);
        }

        return $response;
    }

    /**
     * @return array of \Micro\Application\Package's
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param string $package
     * @throws \Exception
     * @return \Micro\Application\Package
     */
    public function getPackage($package)
    {
        if (!isset($this->packages[$package])) {
            throw new \Exception('[' . __METHOD__ . '] Package "' . $package . '" not found');
        }

        return $this->packages[$package];
    }
}