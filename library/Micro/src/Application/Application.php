<?php

namespace Micro\Application;

use Micro\Application\Exception as CoreException;
use Micro\Http;
use Micro\Event;
use Micro\Container\Container;
use Micro\Container\ContainerAwareInterface;
use Micro\Cache\Cache;
use Micro\Session\Session;
use Micro\Database\Database;
use Micro\Acl\Acl;
use Micro\Translator\Translator;
use Micro\Log\Log as CoreLog;
use Micro\Database\Table\TableAbstract;

class Application extends Container
{
    /**
     * @var array
     */
    private $packages = [];

    /**
     * @var array of collected Exceptions
     */
    private $exceptions = [];

    /**
     * @var Application is booted
     */
    private $booted = \false;

    /**
     * Constructor
     * @param array | string $config
     * @throws CoreException
     */
    public function __construct($config, $name = 'app')
    {
        if (is_array($config)) {
            $config = new Config($config);
        } elseif (is_string($config) && file_exists($config)) {
            $config = new Config(include $config);
        } else if (!$config instanceof Config) {
            throw new CoreException('[' . __METHOD__ . '] Config param must be valid file or array', 500);
        }

        \MicroLoader::addPath($config->get('packages', []), \null);

        $this->set('config', $config);

        static::setInstance($name, $this);
    }

    /**
     * Start the application
     * @return \Micro\Application\Application
     */
    public function run()
    {
        try {

            $this->boot();

            $em = $this->get('event');

            $request = $this->get('request');

            if (($eventResponse = $em->trigger('application.start', ['request' => $request])) instanceof Http\Response) {
                return $eventResponse;
            }

            $response = $this->start();

            if (($eventResponse = $em->trigger('application.end', ['response' => $response])) instanceof Http\Response) {
                return $eventResponse;
            }

            if (env('development')) {
                foreach ($this->exceptions as $exception) {
                    if ($exception instanceof \Exception) {
                        $response->write('<pre>' . $exception->getMessage() . '</pre>');
                    }
                }
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
            $this['acl'] = function ($c) {
                if ($c['config']->get('acl', \true)) {
                    return new Acl();
                }
                return \null;
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
                return new Router($app['request']);
            };
        }

        /**
         * Create default db adapter
         */
        if (!isset($this['db'])) {
            $this['db'] = function ($app) {
                $default  = $app['config']->get('db.default');
                $adapters = $app['config']->get('db.adapters', []);
                if (!isset($adapters[$default])) {
                    return \null;
                }
                $db = Database::factory($adapters[$default]['adapter'], $adapters[$default]);
                TableAbstract::setDefaultAdapter($db);
                TableAbstract::setDefaultMetadataCache($app['cache']);
                return $db;
            };
        }

        /**
         * Create default translator
         */
        if (!isset($this['translator'])) {
            $this['translator'] = function ($app) {
                return new Translator();
            };
        }

        /**
         * Register session config
         */
        $sessionConfig = $this['config']->get('session', []);

        if (!empty($sessionConfig)) {
            Session::register($sessionConfig);
        }

        CoreLog::register();

        CoreException::register();

        return $this;
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @param string $name
     * @return Route
     */
    public function map($pattern, $handler, $name = \null)
    {
        return $this->get('router')->map($pattern, $handler, $name);
    }

    /**
     * Unpackage the application request
     * @return \Micro\Http\Response
     */
    public function start()
    {
        $response = $this->get('response');

        try {

            if (($route = $this->get('router')->match()) === \null) {
                throw new CoreException('[' . __METHOD__ . '] Route not found', 404);
            }

            $request = $this->get('request');

            $request->setParams($route->getParams());

            $routeHandler = $route->getHandler();

            if (is_string($routeHandler) && strpos($routeHandler, '@') !== \false) { // package format
                $routeHandler = $this->resolve($routeHandler, $request, $response);
            }

            if ($routeHandler instanceof Http\Response) {
                $response = $routeHandler;
            } else {
                $response->setBody((string) $routeHandler);
            }

        } catch (\Exception $e) {

            try {

                if (($exceptionResponse = $this->get('exception.handler')->handleException($e)) instanceof Http\Response) {
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
     * @throws CoreException
     * @return \Micro\Http\Response
     */
    public function handleException(\Exception $e)
    {
        $errorHandler = $this->get('config')->get('error');

        if ($errorHandler === \null || empty($errorHandler)) {
            throw $e;
        }

        $currentRoute = $this->get('router')->getCurrentRoute();

        $package = current($errorHandler);

        if ($currentRoute) {
            if (isset($errorHandler[$currentRoute->getName()])) {
                $package = $errorHandler[$currentRoute->getName()];
            }
        }

        $request = $this->get('request');

        $request->setParam('exception', $e);

        return $this->resolve($package, $request, $this->get('response'));
    }

    /**
     * Boot the application
     * @throws CoreException
     */
    public function boot()
    {
        if (\true === $this->booted) {
            return;
        }

        $packages = $this->get('config')->get('packages', []);

        foreach ($packages as $package => $path) {

            if ($path instanceof Package) {
                $path->setContainer($this)->boot();
                $this->packages[$package] = $path;
                continue;
            }

            $packageInstance = $package . '\\Package';

            if (\class_exists($packageInstance, \true)) {
                $instance = new $packageInstance($this);
                if (!$instance instanceof Package) {
                    throw new CoreException(\sprintf('[' . __METHOD__ . '] %s must be instance of Micro\Application\Package', $packageInstance), 500);
                }
                $instance->setContainer($this)->boot();
                $this->packages[$package] = $instance;
            }
        }

        $this->booted = \true;
    }

    /**
     * @param string $package
     * @param Http\Request $request
     * @param Http\Response $response
     * @param bool $subRequest
     * @throws CoreException
     * @return \Micro\Http\Response
     */
    public function resolve($package, Http\Request $request, Http\Response $response, $subRequest = \false)
    {
        if (!is_string($package) || strpos($package, '@') === \false) {
            throw new CoreException('[' . __METHOD__ . '] Package must be in [Package\Handler@action] format', 500);
        }

        list($package, $action) = explode('@', $package);

        if (!class_exists($package, \true)) {
            throw new CoreException('[' . __METHOD__ . '] Package class "' . $package . '" not found', 404);
        }

        $packageInstance = new $package($request, $response);

        if ($packageInstance instanceof Controller) {
            $actionMethod = lcfirst(Utils::camelize($action)) . 'Action';
        } else {
            $actionMethod = lcfirst(Utils::camelize($action));
        }

        if (!method_exists($packageInstance, $actionMethod)) {
            throw new CoreException('[' . __METHOD__ . '] Method "' . $actionMethod . '" not found in "' . $package . '"', 404);
        }

        if ($packageInstance instanceof ContainerAwareInterface) {
            $packageInstance->setContainer($this);
        }

        $scope = '';

        if ($packageInstance instanceof Controller) {
            $packageInstance->init();
            $scope = $packageInstance->getScope();
        }

        if (($packageResponse = $packageInstance->$actionMethod()) instanceof Http\Response) {
            return $packageResponse;
        }

        if (is_object($packageResponse) && !$packageResponse instanceof View) {
            throw new CoreException('[' . __METHOD__ . '] Package response is object and must be instance of View', 500);
        }

        if ($packageResponse === \null || is_array($packageResponse)) {

            if ($packageInstance instanceof Controller) {
                $view = $packageInstance->getView();
            } else {
                $view = new View();
            }

            if (is_array($packageResponse)) {
                $view->addData($packageResponse);
            }

            $packageResponse = $view;
        }

        if ($packageResponse instanceof View) {

            $parts = explode('\\', $package);

            if ($packageResponse->getTemplate() === \null) {
                $packageResponse->setTemplate(($scope ? $scope . '/' : '') . Utils::decamelize($parts[count($parts) - 1]) . '/' . Utils::decamelize($action));
            }

            $packageResponse->injectPaths((array) package_path($parts[0], 'Resources/views'));

            if (($eventResponse = $this->get('event')->trigger('render.start', ['view' => $packageResponse])) instanceof Http\Response) {
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
     * @throws CoreException
     * @return \Micro\Application\Package
     */
    public function getPackage($package)
    {
        if (!isset($this->packages[$package])) {
            throw new CoreException('[' . __METHOD__ . '] Package "' . $package . '" not found');
        }

        return $this->packages[$package];
    }

    /**
     * @param \Exception $e
     */
    public function collectException(\Exception $e)
    {
        $this->exceptions[] = $e;
    }
}