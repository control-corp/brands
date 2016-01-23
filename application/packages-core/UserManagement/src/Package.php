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
    }

    public function onApplicationStart(Message $message)
    {
        TableAbstract::setDefaultAdapter($this->container['db']);

        TableAbstract::setDefaultMetadataCache($this->container['cache']);

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
                        'App\Controller\Admin\Index@test' => \true,
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
}