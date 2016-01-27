<?php

namespace Debug\Handler;

use Micro\Container\ContainerInterface;
use Micro\Event\Message;

class FirePHP
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
        if (!config('debug.handlers.fire_php', 0)) {
            return;
        }

        $this->container['event']->attach('application.start', array($this, 'onApplicationStart'));
        $this->container['event']->attach('application.end', array($this, 'onApplicationEnd'));
    }

    public function onApplicationStart()
    {
        $this->container['db']->setProfiler(\true);
    }

    public function onApplicationEnd(Message $message)
    {
        $profiler = $this->container['db']->getProfiler();

        if ($profiler->getEnabled()) {

            $totalTime    = $profiler->getTotalElapsedSecs();
            $queryCount   = $profiler->getTotalNumQueries();
            $longestTime  = 0;
            $longestQuery = null;

            $total = sprintf('%.6f', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);

            if ($profiler->getQueryProfiles()) {
                $label = 'Executed ' . $queryCount . ' queries in ' . sprintf('%.6f', $totalTime) . ' seconds. (' . ($total ? round(($totalTime / $total) * 100, 2) : 0) . '%)';
                $table = array();
                $table[] = array('Time', 'Event', 'Parameters');
                foreach ($profiler->getQueryProfiles() as $k => $query) {
                    if ($query->getElapsedSecs() > $longestTime) {
                        $longestTime  = $query->getElapsedSecs();
                        $longestQuery = $k;
                    }
                }
                foreach ($profiler->getQueryProfiles() as $k => $query) {
                    $table[] = array(sprintf('%.6f', $query->getElapsedSecs()) . ($k == $longestQuery ? ' !!!' : ''), $query->getQuery(), ($params = $query->getQueryParams()) ? $params : null);
                }
                FirePHP\FirePHP::getInstance()->table('DB - ' . $label, $table);
            }
        }
    }
}