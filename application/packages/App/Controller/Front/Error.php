<?php

namespace App\Controller\Front;

use Micro\Application\Controller;
use Micro\Http\Response\JsonResponse;

class Error extends Controller
{
    const ERROR = 'Моля, опитайте по-късно!';

    public function indexAction()
    {
        $exception = $this->request->getParam('exception');

        if (!$exception instanceof \Exception) {
            return ['exception' => $exception, 'message' => static::ERROR];
        }

        $code = $exception->getCode() ?: 404;
        $message = (env('development') || $code === 403 ? $exception->getMessage() : static::ERROR);

        if ($this->request->isAjax()) {
            return new JsonResponse([
                'error' => [
                    'message' => $exception->getMessage(),
                    'code'    => $exception->getCode(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'trace'   => $exception->getTrace()
                ]
            ], $code);
        }

        $this->response->setCode($code);

        return ['exception' => $exception, 'message' => $message];
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Application\Controller::init()
     */
    public function init()
    {

    }
}