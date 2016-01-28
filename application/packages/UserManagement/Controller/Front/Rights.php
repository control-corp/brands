<?php

namespace UserManagement\Controller\Front;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Helper\Files;

class Rights extends Controller
{
    public function index()
    {
        return new View(
            'rights/index',
            ['resources' => Files::fetchControllers()]
        );
    }
}