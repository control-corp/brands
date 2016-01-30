<?php

namespace Navigation\Model;

use Micro\Model\DatabaseAbstract;

class Menu extends DatabaseAbstract
{
    protected $table = Table\Menu::class;
    protected $entity = Entity\Menu::class;
}