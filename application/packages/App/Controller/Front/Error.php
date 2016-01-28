<?php

namespace App\Controller\Front;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\JsonResponse;

class Error extends Controller
{
    const ERROR = 'Моля, опитайте по-късно!';

    public function index()
    {
        $exception = $this->request->getParam('exception');

        if (!$exception instanceof \Exception) {
            return '';
        }

        $code = $exception->getCode() ?: 404;
        $message = (env('development') || $code === 403 ? $exception->getMessage() : static::ERROR);

        if ($this->request->isAjax()) {
            return new JsonResponse(['error' => $message], $code);
        }

        $this->response->setCode($code);

        return new View('error', [
            'exception' => $exception,
            'message' => $message
        ]);
    }
}