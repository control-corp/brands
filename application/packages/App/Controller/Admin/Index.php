<?php

namespace App\Controller\Admin;

use Micro\Application\Controller;
use Micro\Application\View;

class Index extends Controller
{
    public function index()
    {
        return new View('admin/app/index');
    }
}