<?php

namespace MicroDebug\Handler;

use Micro\Event\Message;

class Performance
{
    public function boot()
    {
        if (!config('debug.handlers.perfomance', 0)) {
            return;
        }

        app('event')->attach('application.end', [$this, 'onApplicationEnd']);
    }

    public function onApplicationEnd(Message $message)
    {
        file_put_contents('application/data/classes.php', "<?php\nreturn " . var_export(\MicroLoader::getFiles(), \true) . ";", LOCK_EX);
    }
}