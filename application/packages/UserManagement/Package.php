<?php

namespace UserManagement;

use Micro\Application\Package as BasePackage;
use Micro\Acl\Acl;
use Micro\Auth\Auth;
use Micro\Event\Message;
use Micro\Database\Table\TableAbstract;

class Package extends BasePackage
{
    public function boot()
    {
        $this->container['event']->attach('application.start', array($this, 'onApplicationStart'));
        $this->container['event']->attach('dispatch.start', array($this, 'onDispatchStart'));
    }

    public function onApplicationStart(Message $message)
    {
        TableAbstract::setDefaultAdapter(app('db'));

        TableAbstract::setDefaultMetadataCache(app('cache'));

        /**
         * Acl
         */
        $this->container['acl'] = function () {
            return new Acl([
                'guest' => [
                    'resources' => [
                        'App\Controller\Index@index' => \true,
                        'App\Controller\Index@login' => \true,
                        'App\Controller\Index@register' => \true,
                        'App\Controller\Admin\Index@index' => \true,
                    ],
                    'parent' => \null
                ],
                'user' => [
                    'resources' => [
                        'App\Controller\Index@index' => \true,
                        'App\Controller\Index@logout' => \true,
                        'App\Controller\Index@profile' => \true,
                        'Article\Controller\Index@index' => \true,
                        'Article\Controller\Index@detail' => \true,
                    ],
                    'parent' => \null
                ],
                'admin' => [
                    'resources' => [],
                    'parent' => 'user'
                ]
            ]);
        };

        /**
         * Auth
         */
        Auth::setResolver(function ($identity) {
            return $identity;
        });
    }

    public function onDispatchStart(Message $message)
    {
        $route   = $message->getParam('route');
        $handler = $route->getHandler();

        if ($handler instanceof \Closure) {
            $handler = $handler->__invoke($route, $this);
        }

        if (!is_string($handler) || strpos($handler, '@') === \false) {
            return;
        }

        // текущ потребител
        $identity = identity();

        if ($identity !== \null) {
            $role = $identity->getGroup();
        } else {
            $role = 'guest';
        }

        if ($route->getName() !== 'error'
            && $this->container['acl']->isAllowed($role, $handler, \true) === \false
        ) {
            throw new \Exception('Access denied', 403);
        }
    }
}