<?php

namespace App\Controller\Admin;

use Micro\Application\Controller;
use Micro\Http\Response\RedirectResponse;

class Index extends Controller
{
    protected $scope = 'admin';

    public function indexAction()
    {
        if (!identity()) {
            return new RedirectResponse(route('admin-login'));
        }
    }
}