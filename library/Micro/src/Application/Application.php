<?php

namespace Micro\Application;

use Micro\Http;
use Micro\Event;
use Micro\Container\Container;
use Micro\Container\ContainerAwareInterface;
use Micro\Cache\Cache;

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

            $response = $this->start();

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

        return $this;
    }

    /**
     * Unpackage the application request
     * @return \Micro\Http\Response
     */
    public function start()
    {
        $response = $this['response'];

        if (($eventResponse = $this['event']->trigger('application.start', compact('request', 'response'))) instanceof Http\Response) {
            return $eventResponse;
        }

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

        if (($eventResponse = $this['event']->trigger('application.end', compact('response'))) instanceof Http\Response) {
            return $eventResponse;
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

        $package = \null;
        $action = \null;

        $handler = $route->getHandler();

        if ($handler instanceof \Closure) {
            $handlerResponse = $handler->__invoke($route, $this);
        } else {
            $handlerResponse = $handler;
        }

        if (is_string($handlerResponse) && strpos($handlerResponse, '@') !== \false) {

            list($package, $action) = explode('@', $handlerResponse);

            $parts = explode('\\', $package);

            if (!class_exists($package)) {
                throw new \Exception('[' . __METHOD__ . '] Package class "' . $package . '" not found');
            }

            $packageInstance = new $package($this['request'], $this['response']);

            if (!method_exists($packageInstance, $action)) {
                throw new \Exception('[' . __METHOD__ . '] Method "' . $action . '" not found in "' . $package . '"', 404);
            }

            if ($packageInstance instanceof ContainerAwareInterface) {
                $packageInstance->setContainer($this);
            }

            if ($packageInstance instanceof Controller) {
                $packageInstance->init();
            }

            if (($eventResponse = $this['event']->trigger('dispatch.start', ['route' => $route, 'package_instance' => $packageInstance])) instanceof Http\Response) {
                return $eventResponse;
            }

            $packageResponse = $packageInstance->$action();

            if (($eventResponse = $this['event']->trigger('dispatch.end', ['route' => $route, 'package_instance' => $packageInstance, 'package_response' => $packageResponse])) instanceof Http\Response) {
                return $eventResponse;
            }

            if (is_array($packageResponse) || $packageResponse === \null) {
                $packageResponse = new View(\null, $packageResponse);
            }

            if ($packageResponse instanceof View) {
                if ($packageResponse->getTemplate() === \null) {
                    $packageResponse->setTemplate(Utils::decamelize($action));
                }
                try {
                    $paths = (array) package_path($parts[0], 'views');
                } catch (\Exception $e) {
                    $paths = [];
                }
                $packageResponse = $packageResponse->injectPaths($paths)->render();
            }

            $handlerResponse = $packageResponse;
        }

        if ($handlerResponse instanceof Http\Response) {
            $response = $handlerResponse;
        } else {
            $response = $this['response']->setBody((string) $handlerResponse);
        }

        if (($eventResponse = $this['event']->trigger('unpackage.end', compact('response'))) instanceof Http\Response) {
            return $eventResponse;
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