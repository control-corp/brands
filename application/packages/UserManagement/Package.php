<?php

namespace UserManagement;

use Micro\Application\Package as BasePackage;
use Micro\Acl\Acl;
use Micro\Auth\Auth;
use Micro\Cache;

class Package extends BasePackage
{
    public function boot()
    {
        $this->container['event']->attach('application.start', array($this, 'onApplicationStart'));
    }

    public function onApplicationStart()
    {
        /**
         * Acl
         */
        Acl::setResolver(function () {

            try {
                $cache = app('cache');
            } catch (\Exception $e) {
                $cache = \null;
            }

            if ($cache === \null || ($data = $cache->load('Acl')) === \false) {
                $groups = app('db')->fetchAll('
                    SELECT a.alias, b.alias as parentAlias, a.rights
                    FROM Groups a
                    LEFT JOIN Groups b ON b.id = a.parentId
                ');
                $data = [];
                foreach ($groups as $group) {
                    $data[$group['alias']] = [
                        'group'     => $group['alias'],
                        'parent'    => $group['parentAlias'],
                        'resources' => []
                    ];
                    $rights = $group['rights'] ? json_decode($group['rights'], \true) : [];
                    $rights = is_array($rights) ? $rights : [];
                    $data[$group['alias']]['resources'] = $rights;
                }
                if ($cache instanceof Cache\Core) {
                    $cache->save($data, 'Acl');
                }
            }

            return $data;
        });

        /**
         * Auth
         */
        Auth::setResolver(function ($identity) {
            return \UserManagement\Model\Users::callFind((int) $identity);
        });
    }
}