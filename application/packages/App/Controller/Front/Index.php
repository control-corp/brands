<?php

namespace App\Controller\Front;

use Micro\Application\Controller;
use Micro\Http\Response\RedirectResponse;

class Index extends Controller
{
    public function indexAction()
    {
        if (!identity()) {
            return new RedirectResponse(route('login'));
        }
    }
}