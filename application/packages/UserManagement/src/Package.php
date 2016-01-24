<?php

namespace UserManagement;

use Micro\Application\Package as BasePackage;
use Micro\Acl\Acl;
use Micro\Auth\Auth;
use Micro\Event\Message;
use Micro\Database\Table\TableAbstract;
use Micro\Helper\Files;

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
            $config = [
                'guest' => [
                    'resources' => Files::fetchControllers(),
                    'parent' => \null
                ],
                'user' => [
                    'resources' => [],
                    'parent' => 'guest'
                ],
                'admin' => [
                    'resources' => [],
                    'parent' => 'user'
                ]
            ];
            return new Acl($config);
        };

        /**
         * Auth
         */
        Auth::setResolver(function ($identity) {
            return $identity;
        });
    }
}