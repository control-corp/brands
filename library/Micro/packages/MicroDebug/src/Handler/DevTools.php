<?php

namespace MicroDebug\Handler;

use Micro\Container\ContainerInterface;
use Micro\Event\Message;
use Micro\Application\View;

class DevTools
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function boot()
    {
        if (!config('debug.handlers.dev_tools', 0)) {
            return;
        }

        $this->container['event']->attach('render.start', array($this, 'onRenderStart'));
        $this->container['event']->attach('application.end', array($this, 'onApplicationEnd'));
    }

    public function onRenderStart(Message $message)
    {
        $this->view = new View('debug');
        $this->view->addPath(package_path('Debug', 'views'));

        $view = $message->getParam('view');
        $view->section('styles', (string) $this->view->partial('css'));
    }

    public function onApplicationEnd(Message $message)
    {
        $response = $message->getParam('response');

        $b = $response->getBody();

        $b = explode('</body>', $b);

        $b[0] .= $this->view->render() . '</body>';

        $response->setBody(implode('', $b));
    }
}