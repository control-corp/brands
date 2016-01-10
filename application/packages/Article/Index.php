<?php

namespace Article;

use Micro\Application\Controller;

class Index extends Controller
{
    public function index()
    {

    }

    public function detail()
    {
        return $this->request->getParams();
    }
}