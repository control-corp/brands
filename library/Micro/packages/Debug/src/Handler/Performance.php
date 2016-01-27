<?php

namespace Debug\Handler;

use Micro\Container\ContainerInterface;
use Micro\Event\Message;

class Performance
{
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
        if (!config('debug.handlers.perfomance', 0)) {
            return;
        }

        $this->container['event']->attach('application.end', array($this, 'onApplicationEnd'));
    }

    public function onApplicationEnd(Message $message)
    {
        file_put_contents('application/data/classes.php', "<?php\nreturn " . var_export(\MicroLoader::getFiles(), true) . ";", LOCK_EX);
    }
}