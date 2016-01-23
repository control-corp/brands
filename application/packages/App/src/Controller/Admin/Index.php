<?php

namespace App\Controller\Admin;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\RedirectResponse;

class Index extends Controller
{
    public function index()
    {
        return new View('admin/index');
    }

    public function test()
    {
        return new RedirectResponse(route('admin'));
    }
}