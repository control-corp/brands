<?php

namespace Micro\Application;

use Micro\Http;
use Micro\Container\ContainerAwareInterface;
use Micro\Container\ContainerAwareTrait;
use Micro\Application\Utils;

class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \Micro\Http\Request
     */
    protected $request;

    /**
     * @var \Micro\Http\Response
     */
    protected $response;

    /**
     * @param \Micro\Http\Request $request
     * @param \Micro\Http\Response $response
     */
    public function __construct(Http\Request $request, Http\Response $response)
    {
        $this->request = $request;

        $this->response = $response;
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        if (!is_allowed()) {
            throw new \Exception('Access denied', 403);
        }
    }

    public function forward($package, array $params = [], $subRequest = \false)
    {
        $req = clone $this->request;

        list($packageParts, $action) = explode('@', $package);

        $packageParts = explode('\\', $packageParts);

        $params['package'] = Utils::decamelize($packageParts[0]);
        $params['controller'] = Utils::decamelize($packageParts[count($packageParts) - 1]);
        $params['action'] = Utils::decamelize($action);

        $req->setParams($params);

        return $this->container->resolve($package, $req, clone $this->response, $subRequest);
    }
}