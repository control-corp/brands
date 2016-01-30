<?php

namespace Micro\Application;

use Exception as CoreException;
use Micro\Http;
use Micro\Container\ContainerAwareInterface;
use Micro\Container\ContainerAwareTrait;

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
     * @var View
     */
    protected $view;

    protected $scope;

    /**
     * @param \Micro\Http\Request $request
     * @param \Micro\Http\Response $response
     */
    public function __construct(Http\Request $request, Http\Response $response)
    {
        $this->request = $request;

        $this->response = $response;

        $this->view = new View();
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        if (!is_allowed()) {
            throw new CoreException('Access denied', 403);
        }
    }

    public function getView()
    {
        return $this->view;
    }

    public function getScope()
    {
        return $this->scope;
    }
}