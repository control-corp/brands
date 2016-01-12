<?php

namespace UserManagement;

use Micro\Application\Package as BasePackage;
use Micro\Acl\Acl;
use Micro\Auth\Auth;
use Micro\Event\Message;

class Package extends BasePackage
{
    public function boot()
    {
        /**
         * Acl
         */
        $this->container['acl'] = function () {
            return new Acl([
                'guest' => [
                    'resources' => [
                        'App\Index@index' => \true,
                        'App\Index@login' => \true,
                        'App\Index@register' => \true,
                    ],
                    'parent' => \null
                ],
                'user' => [
                    'resources' => [
                        'App\Index@index' => \true,
                        'App\Index@logout' => \true,
                        'App\Index@profile' => \true,
                        'Article\Index@index' => \true,
                        'Article\Index@detail' => \true,
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

        $this->container['event']->attach('unpackage.start', array($this, 'onUnpackageStart'));
    }

    public function onUnpackageStart(Message $message)
    {
        $route   = $message->getParam('route');
        $handler = $route->getHandler();

        if ($handler instanceof \Closure) {
            $handler = $handler->__invoke($route, $this);
        }

        if (!is_string($handler)) {
            return;
        }

        $identity = Auth::identity();

        $role = 'guest';

        if ($identity !== \null) {
            $role = $identity->getGroup();
        }

        if ($route->getName() !== 'error'
            && $this->container['acl']->isAllowed($role, $handler, \true) === \false
        ) {
            throw new \Exception('Access denied', 403);
        }
    }
}