<?php

namespace UserManagement\Controller\Front;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Helper\Files;

class Rights extends Controller
{
    public function indexAction()
    {
        return new View(
            'rights/index',
            ['resources' => Files::fetchControllers()]
        );
    }
}