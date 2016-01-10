<?php

namespace App;

use Micro\Application\Package as BasePackage;
use Micro\Event\Message;
use Micro\Http\Response;

class Package extends BasePackage
{
    public function boot()
    {
        $this->container['event']->attach('application.end', function (Message $message) {

            if (config('application.perfomance', 0)) {
                file_put_contents('application/data/classes.php', "<?php\nreturn " . var_export(\MicroLoader::getFiles(), true) . ";", LOCK_EX);
            }

            $response = $message->getParam('response');

            if ($response instanceof Response\JsonResponse || app('request')->isAjax()) {
                return;
            }

            if (config('application.debug', 0)) {
                $body = $response->getBody();
                $body = explode('</body>', $body);
                $body[0] = $body[0] . sprintf('<br />Execution time %.8fs.', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . '</body>';
                $response->setBody(implode('', $body));
            }

            //$response->setBody(preg_replace('~\s\s+~', '', $response->getBody()));
        });
    }
}