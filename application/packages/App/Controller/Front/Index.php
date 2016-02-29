<?php

namespace App\Controller\Front;

use Micro\Application\Controller;
use Micro\Http\Response\RedirectResponse;

class Index extends Controller
{
    public function init() {}

    public function indexAction()
    {
        return new RedirectResponse(route('admin'));
    }
}