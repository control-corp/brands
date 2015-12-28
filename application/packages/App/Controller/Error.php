<?php

namespace App\Controller;

use Micro\Application\Controller;
use Micro\Application\View;

class Error extends Controller
{
    public function index()
    {
        return new View('error', $this->request->getParams());
    }
}