<?php

namespace App\Controller;

use Micro\Application\Controller;
use Micro\Application\View;

class Index extends Controller
{
    public function index()
    {
        return new View('index');
    }

    public function articles()
    {
        return new View('articles/list');
    }

    public function article($id)
    {
        return new View('articles/detail', ['id' => $id]);
    }
}