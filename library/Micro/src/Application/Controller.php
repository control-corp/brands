<?php

namespace Micro\Application;

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
}