<?php

namespace App\Controller\Admin;

use Micro\Application\Controller;
use Micro\Http\Response\JsonResponse;
use Micro\Http\Response\RedirectResponse;
use Micro\Auth\Auth;

class Error extends Controller
{
    const ERROR = 'Моля, опитайте по-късно!';

    protected $scope = 'admin';

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

        if ($exception->getCode() === 403) {
            if (Auth::identity() === \null) {
                if (is_allowed(app('router')->getRoute('admin-login')->getHandler())) {
                    return new RedirectResponse(
                        route('admin-login', ['backTo' => urlencode(route())])
                    );
                }
            }
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