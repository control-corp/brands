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
            $config = new Application\Config($config);
        } elseif (is_string($config) && file_exists($config)) {
            $config = new Application\Config(include $config);
        } else if (!$config instanceof Application\Config) {
            throw new \InvalidArgumentException('[' . __METHOD__ . '] Config param must be valid file or array', 500);
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
        if (!isset($this['request'])) {
            $this['request'] = new Http\Request();
        }

        if (!isset($this['response'])) {
            $this['response'] = new Http\Response\HtmlResponse();
        }

        if (!isset($this['event'])) {
            $this['event'] = new Event\Manager();
        }

        try {
            $this->boot();
            $response = $this->start();
            $response->send();
        } catch (\Exception $e) {
            if (env('development')) {
                try {
                    if ($this->has('exception.handler')) {
                        echo $this->get('exception.handler')->handleException($e);
                    } else {
                        echo $e->getMessage();
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }

    /**
     * Boot and unpackage the application request
     * @return \Micro\Http\Response
     */
    public function start()
    {
        $response = $this['response'];

        if (($eventResponse = $this['event']->trigger('application.start', compact('response'))) instanceof Http\Response) {
            return $eventResponse;
        }

        try {

            if (($route = $this['router']->match()) === \null) {
                throw new \Exception('[' . __METHOD__ . '] Route not found', 404);
            }

            $response = $this->unpackage($route);

        } catch (\Exception $e) {

            try {

                if (($exceptionResponse = $this->handleException($e)) instanceof Http\Response) {
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
        if ($this->has('exception.handler')) {
            return $this->get('exception.handler')->handleException($e);
        }

        $errorHandler = $this['config']->get('error');

        if ($errorHandler === \null || !isset($errorHandler['route'])) {
            throw $e;
        }

        $route = $this['router']->getRoute($errorHandler['route']);

        if ($route === \null) {
            throw new \Exception('[' . __METHOD__ . '] Error route not found', 404);
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
            $packageInstance = $package . '\\Package';
            if (class_exists($packageInstance)) {
                $instance = new $packageInstance($this);
                if (!$instance instanceof Application\Package) {
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
    public function unpackage(Application\Route $route)
    {
        $this['request']->setParams($route->getParams()/*  + $route->getDefaults() */);

        if (($eventResponse = $this['event']->trigger('unpackage.start', compact('response'))) instanceof Http\Response) {
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

            if (!isset($this->packages[$parts[0]])) {
                throw new \Exception('[' . __METHOD__ . '] Package "' . $parts[0] . '" not found');
            }

            if (!class_exists($package)) {
                throw new \Exception('[' . __METHOD__ . '] Package class "' . $package . '" not found');
            }

            $packageInstance = new $package($this['request'], $this['response']);

            if (!method_exists($packageInstance, $action)) {
                throw new \Exception('[' . __METHOD__ . '] Method "' . $action . '" not found in "' . $package . '"', 404);
            }

            if ($packageInstance instanceof Container\ContainerAwareInterface) {
                $packageInstance->setContainer($this);
            }

            if ($packageInstance instanceof Application\Controller) {
                $packageInstance->init();
            }

            $packageResponse = call_user_func_array(array($packageInstance, $action), $route->getParams());

            if (is_array($packageResponse) || $packageResponse === \null) {
                $packageResponse = new Application\View(\null, $packageResponse);
            }

            if ($packageResponse instanceof Application\View) {
                if ($packageResponse->getTemplate() === \null) {
                    $packageResponse->setTemplate(
                        Utils::decamelize($parts[0]) . '/' . Utils::decamelize($action)
                    );
                }
                $packageResponse->injectPaths();
                $packageResponse = $packageResponse->render();
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
            throw new \Exception('[' . __METHOD__ . '] Package "' . $package . '" not found');
        }

        return $this->packages[$package];
    }
}