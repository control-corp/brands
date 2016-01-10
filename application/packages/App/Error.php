<?php

namespace App;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\JsonResponse;

class Error extends Controller
{
    public function index()
    {
        $exception = $this->request->getParam('exception');

        if ($this->request->isAjax()) {
            return new JsonResponse($exception->getMessage());
        }

        return new View('error', ['exception' => $exception]);
    }
}