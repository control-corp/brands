<?php

namespace MicroDebug\Handler;

use Micro\Event\Message;

class FirePHP
{
    public function boot()
    {
        if (!config('debug.handlers.fire_php', 0)) {
            return;
        }

        app('event')->attach('application.start', [$this, 'onApplicationStart']);
        app('event')->attach('application.end', [$this, 'onApplicationEnd']);
    }

    public function onApplicationStart()
    {
        app('db')->setProfiler(\true);
    }

    public function onApplicationEnd(Message $message)
    {
        $profiler = app('db')->getProfiler();

        if ($profiler->getEnabled()) {

            $totalTime    = $profiler->getTotalElapsedSecs();
            $queryCount   = $profiler->getTotalNumQueries();
            $longestTime  = 0;
            $longestQuery = \null;

            $total = sprintf('%.6f', microtime(\true) - $_SERVER['REQUEST_TIME_FLOAT']);

            if ($profiler->getQueryProfiles()) {
                $label = 'Executed ' . $queryCount . ' queries in ' . sprintf('%.6f', $totalTime) . ' seconds. (' . ($total ? round(($totalTime / $total) * 100, 2) : 0) . '%)';
                $table = [];
                $table[] = ['Time', 'Event', 'Parameters'];
                foreach ($profiler->getQueryProfiles() as $k => $query) {
                    if ($query->getElapsedSecs() > $longestTime) {
                        $longestTime  = $query->getElapsedSecs();
                        $longestQuery = $k;
                    }
                }
                foreach ($profiler->getQueryProfiles() as $k => $query) {
                    $table[] = [sprintf('%.6f', $query->getElapsedSecs()) . ($k == $longestQuery ? ' !!!' : ''), $query->getQuery(), ($params = $query->getQueryParams()) ? $params : \null];
                }
                FirePHP\FirePHP::getInstance()->table('DB - ' . $label, $table);
            }
        }
    }
}