<?php

namespace App;

use Micro\Application\Package as BasePackage;
use Micro\Event\Message;
use Micro\Http\Response;

class __Package extends BasePackage
{
    public function boot()
    {
        $this->container['event']->attach('application.start', function (Message $message) {
            if (config('application.debug', 0)) {
                app('db')->setProfiler(\true);
            }
        });

        $this->container['event']->attach('application.end', function (Message $message) {

            if (config('application.perfomance', 0)) {
                file_put_contents('application/data/classes.php', "<?php\nreturn " . var_export(\MicroLoader::getFiles(), true) . ";", LOCK_EX);
            }

            $response = $message->getParam('response');

            if ($response instanceof Response\JsonResponse || app('request')->isAjax()) {
                return;
            }

            if (config('application.debug', 0)) {

                $buff = sprintf('<br />Execution time %.8fs.', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);

                $queries = app('db')->getProfiler()->getQueryProfiles();

                if ($queries) {
                    foreach ($queries as $query) {
                        $buff .= '<br />(' . sprintf('%.6f', $query->getElapsedSecs()) . ') :: ' . $query->getQuery();
                    }
                }

                $body = $response->getBody();
                $body = explode('</body>', $body);
                $body[0] = $body[0] . $buff . '</body>';
                $response->setBody(implode('', $body));
            }

            //$response->setBody(preg_replace('~\s\s+~', '', $response->getBody()));
        });
    }
}