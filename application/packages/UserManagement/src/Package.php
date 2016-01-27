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
        Acl::setResolver(function () {
            return include __DIR__ . '/../rights.php';
        });

        /**
         * Auth
         */
        Auth::setResolver(function ($identity) {
            return \UserManagement\Model\Users::callFind((int) $identity);
        });
    }
}