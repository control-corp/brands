<?php

namespace Micro;

class Application extends Container\Container
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
            throw new \InvalidArgumentException('Config param must be valid file or array', 500);
        }

        \MicroLoader::addPath($config->get('application.packages_paths', []));

        $this['config'] = $config;

        static::setInstance($this);
    }

    /**
     * Start the application
     * @return \Micro\Application
     */
    public function run()
    {
        $response = $this->start();

        $response->send();

        return $this;
    }

    /**
     * Boot and unpackage the application request
     * @return \Micro\Http\Response
     */
    public function start()
    {
        $this->boot();

        $response = $this['response'];

        if (($eventResponse = $this['event']->trigger('application.start', compact('response'))) instanceof Http\Response) {
            return $eventResponse;
        }

        try {

            if (($route = $this['router']->match()) === \null) {
                throw new \Exception('Route not found', 404);
            }

            $response = $this->unpackage($route);

        } catch (\Exception $e) {

            try {

                $this->handleError($e);

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
    public function handleError(\Exception $e)
    {
        $route = $this['router']->getRoute($this['config']->get('error.route'));

        if ($route === \null) {
            throw new \Exception('Error route not found', 404);
        }

        $route->setParams($this['config']->get('error.params', []) + ['exception' => $e]);

        return $this->unpackage($route);
    }

    /**
     * Boot the application
     * @throws \Exception
     */
    public function boot()
    {
        $packages = $this['config']->get('application.packages', []);

        foreach ($packages as $package) {
            $instance = $package . '\\Package';
            if (class_exists($instance)) {
                $instance = new $instance($this);
                $instance->setContainer($this);
                $instance->boot();
                $this->packages[$package] = $instance;
            }
        }

        if (empty($this->packages)) {
            throw new \Exception('No packages found', 500);
        }
    }

    /**
     * Unpackage the application request
     * @param \Micro\Application\Route $route
     * @throws \Exception
     * @return \Micro\Http\Response
     */
    public function unpackage(Application\Route $route)
    {
        $this['request']->setParams($route->getParams());

        if (($eventResponse = $this['event']->trigger('unpackage.start', compact('response'))) instanceof Http\Response) {
            return $eventResponse;
        }

        $package = \null;
        $action = \null;

        $handler = $route->getHandler();

        if ($handler instanceof \Closure) {
            $handler = $handler->bindTo($route);
            $handlerResponse = $handler->__invoke();
        } else {
            $handlerResponse = $handler;
        }

        if (is_callable($handlerResponse)) {
            $handlerResponse = call_user_func($handlerResponse);
        }

        if (is_string($handlerResponse)) {

            if (strpos($handlerResponse, '@') !== \false) {
                list($package, $action) = explode('@', $handlerResponse);
            } else if (strpos($handlerResponse, '::') !== \false) {
                list($package, $action) = explode('::', $handlerResponse);
            }

            if ($package !== \null) {

                $parts = explode('\\', $package);

                if (!isset($this->packages[$parts[0]])) {
                    throw new \Exception('Package "' . $parts[0] . '" not found');
                }

                if (!class_exists($package)) {
                    throw new \Exception('Package class "' . $package . '" not found');
                }

                $package = new $package($this['request'], $this['response']);

                if (!method_exists($package, $action)) {
                    throw new \Exception('Method "' . $action . '" not found in "' . get_class($package) . '"', 404);
                }

                if ($package instanceof Container\ContainerAwareInterface) {
                    $package->setContainer($this);
                }

                $package->init();

                $handlerResponse = call_user_func_array(array($package, $action), $route->getParams());

                if (is_array($handlerResponse) || $handlerResponse === \null) {
                    $handlerResponse = new Application\View(\null, $handlerResponse);
                }

                if ($handlerResponse instanceof Application\View) {
                    if ($handlerResponse->getTemplate() === \null) {
                        $handlerResponse->setTemplate(
                            Utils::decamelize($parts[0]) . '/' . Utils::decamelize($action)
                        );
                    }
                    $handlerResponse->injectPaths();
                }
            }
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
     * @return array of \Micro\Package's
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param string $package
     * @throws \Exception
     * @return \Micro\Package
     */
    public function getPackage($package)
    {
        if (!isset($this->packages[$package])) {
            throw new \Exception('Package "' . $package . '" not found');
        }

        return $this->packages[$package];
    }
}