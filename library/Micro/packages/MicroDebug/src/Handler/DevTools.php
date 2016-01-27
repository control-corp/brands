<?php

namespace MicroDebug\Handler;

use Micro\Event\Message;
use Micro\Application\View;

class DevTools
{
    /**
     * @var View
     */
    protected $view;

    public function boot()
    {
        if (!config('debug.handlers.dev_tools', 0)) {
            return;
        }

        app('event')->attach('render.start', [$this, 'onRenderStart']);
        app('event')->attach('application.end', [$this, 'onApplicationEnd']);
    }

    public function onRenderStart(Message $message)
    {
        $this->view = new View('debug');
        $this->view->addPath(package_path('MicroDebug', 'views'));

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