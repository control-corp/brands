<?php

namespace App\Controller\Front;

use Micro\Application\Controller;
use Micro\Http\Response\JsonResponse;
use Micro\Http\Response\RedirectResponse;
use Micro\Auth\Auth;

class Error extends Controller
{
    const ERROR = 'Моля, опитайте по-късно!';

    public function indexAction()
    {
        $exception = $this->request->getParam('exception');

        if (!$exception instanceof \Exception) {
            return;
        }

        $code = $exception->getCode() ?: 404;
        $message = (env('development') || $code === 403 ? $exception->getMessage() : static::ERROR);

        if ($this->request->isAjax()) {
            return new JsonResponse(['error' => $message], $code);
        }

        if ($exception->getCode() === 403) {
            if (Auth::identity() === \null) {
                if (is_allowed(app('router')->getRoute('login')->getHandler())) {
                    return new RedirectResponse(
                        route('login', ['backTo' => urlencode(route())])
                    );
                }
            }
        }

        $this->response->setCode($code);

        return $this->view
                    ->addData([
                        'exception' => $exception,
                        'message'   => $message
                    ]);
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Application\Controller::init()
     */
    public function init()
    {

    }
}