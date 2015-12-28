<?php

namespace App;

use Micro\Application\Package as BasePackage;
use Micro\Event\Message;
use Micro\Http\Response;

class Package extends BasePackage
{
    public function boot()
    {
        $this->container['event']->attach('application.end', array($this, 'onApplicationEnd'));
    }

    public function onApplicationEnd(Message $message)
    {
        if (config('application.perfomance.enable', 0)) {
            file_put_contents('application/data/classes.php', "<?php\nreturn " . var_export(\MicroLoader::getFiles, true) . ";", LOCK_EX);
        }

        $response = $message->getParam('response');

        if (!$response instanceof Response) {
            return;
        }

        if (config('debug', 0)) {
            $body = $response->getBody();
            $body = explode('</body>', $body);
            $body[0] = $body[0] . sprintf('Execution time %.8fs.', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . '</body>';
            $response->setBody(implode('', $body));
        }

        //$response->setBody(preg_replace('~\s\s+~', '', $response->getBody()));
    }
}