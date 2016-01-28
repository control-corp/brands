<?php

namespace MicroDebug\Handler;

use Micro\Event\Message;

class Performance
{
    public function boot()
    {
        if (!\config('debug.handlers.performance', 0)) {
            return;
        }

        \app('event')->attach('application.end', [$this, 'onApplicationEnd']);
    }

    public function onApplicationEnd(Message $message)
    {
        $files = \MicroLoader::getFiles();
        $forStore = [];

        foreach ($files as $class => $file) {
            if (\substr($class, 0, 6) === 'Micro\\') {
                $forStore[$class] = $file;
            }
        }

        \file_put_contents('application/data/classes.php', "<?php\nreturn " . \var_export($forStore, \true) . ";", \LOCK_EX);
    }
}