<?php

use Micro\Container\Container;
use Micro\Application\Utils;
use Micro\Application\View;
use Micro\Http\Response\JsonResponse;
use Micro\Http\Response\RedirectResponse;
use Micro\Paginator\Paginator;
use Micro\Acl\RoleInterface;
use Micro\Auth\Auth;
use Micro\Helper\Flash;

if (!function_exists('app')) {
    function app($service = \null)
    {
        $container = Container::getInstance();

        if ($service !== \null) {
            return $container[$service];
        }

        return $container;
    }
}

if (!function_exists('app_path')) {
    function app_path($path = \null)
    {
        $appPath = ltrim(config('application.path', 'application'), '/\\');

        if ($path !== \null) {
            $appPath .= DIRECTORY_SEPARATOR . trim($path, '/\\');
        }

        return $appPath;
    }
}

if (!function_exists('public_path')) {
    function public_path($path = \null)
    {
        $publicPath = ltrim(config('application.public_path', 'public'), '/\\');

        if ($path !== \null) {
            $publicPath .= DIRECTORY_SEPARATOR . trim($path, '/\\');
        }

        return $publicPath;
    }
}

if (!function_exists('package_path')) {
    function package_path($package, $path = \null)
    {
        $packages = config('packages', []);

        if (!isset($packages[$package])) {
            throw new \Exception(sprintf('[' . __FUNCTION__ . '] Invalid package "%s"', $package));
        }

        $packagePath = rtrim($packages[$package], '/\\');

        if ($path !== \null) {
            $packagePath .= DIRECTORY_SEPARATOR . trim($path, '/\\');
        }

        return $packagePath;
    }
}

if (!function_exists('config')) {
    function config($key, $value = \null)
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        return $cache[$key] = app('config')->get($key, $value);
    }
}

if (!function_exists('env')) {
    function env($env = \null)
    {
        if (defined('APP_ENV')) {
            $_env = APP_ENV;
        } else {
            $_env = getenv('APP_ENV');
        }

        if ($_env === \false) {
            $_env = 'production';
        }

        if ($env === \null) {
            return $_env;
        }

        if ($_env === $env) {
            return \true;
        }

        return \false;
    }
}

if (!function_exists('route')) {
    function route($name = \null, array $data = [], $reset = \false, $qsa = \true)
    {
        return app('router')->assemble($name, $data, $reset, $qsa);
    }
}

if (!function_exists('base_url')) {
    function base_url($path = \null)
    {
        $baseUrl = app('request')->getBaseUrl();

        if ($path !== \null) {
            $baseUrl .= '/' . trim($path, '/\\');
        }

        return $baseUrl;
    }
}

if (!function_exists('server_url')) {
    function server_url($path = \null)
    {
        $request   = app('request');
        $serverUrl = $request->getScheme() . '://' . $request->getHttpHost();

        if ($path !== \null) {
            $serverUrl .= '/' . trim($path, '/\\');
        }

        return $serverUrl;
    }
}

if (!function_exists('json')) {
    /**
     * @param string $body
     * @param int $code
     * @return \Micro\Http\Response\JsonResponse
     */
    function json($body = '', $code = 200)
    {
        return new JsonResponse($body, $code);
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string $url
     * @param int $code
     * @return \Micro\Http\Response\RedirectResponse
     */
    function redirect($url, $code = 302)
    {
        return new RedirectResponse($url, $code);
    }
}

if (!function_exists('view')) {
    /**
     * @param string $template
     * @param array $data
     * @param boolean $injectPaths
     * @return \Micro\Application\View
     */
    function view($template, array $data = \null, $injectPaths = \false)
    {
        return new View($template, $data, $injectPaths);
    }
}

if (!function_exists('identity')) {
    /**
     * @param unknown $force
     * @return \Micro\Auth\Identity
     */
    function identity($force = \false)
    {
        return Auth::identity($force);
    }
}

if (!function_exists('flash')) {
    function flash()
    {
        $flash = new Flash();

        return $flash;
    }
}

if (!function_exists('escape')) {
    function escape($var, $encoding = 'UTF-8', $escape = 'htmlspecialchars')
    {
        if (in_array($escape, ['htmlspecialchars', 'htmlentities'])) {
            return call_user_func($escape, $var, ENT_COMPAT, $encoding);
        }

        return call_user_func($escape, $var);
    }
}

if (!function_exists('current_package')) {
    function current_package()
    {
        $route = app('router')->getCurrentRoute();

        if ($route === \null) {
            throw new \Exception(sprintf('[' . __FUNCTION__ . '] There is no current route'));
        }

        $resource = $route->getHandler();

        if (!is_string($resource)) {
            return \null;
        }

        $parts = explode('\\', $resource);

        return $parts[0];
    }
}

if (!function_exists('is_allowed')) {
    function is_allowed($resource = \null, $role = \null, $privilege = \true)
    {
        if (!app()->has('acl')) {
            return \true;
        }

        if ($role === \null) {

            $identity = identity();

            $role = 'guest';

            if ($identity !== \null && $identity instanceof RoleInterface) {
                try {
                    $role = $identity->getRoleId();
                } catch (\Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }
        }

        if ($resource === \null) {

            $route = app('router')->getCurrentRoute();

            if ($route === \null || $route->getName() === 'error') {
                return \true;
            }

            $resource = $route->getHandler();

            if (!is_string($resource)) {
                return \true;
            }
        }

        return app('acl')->isAllowed($role, $resource, $privilege);
    }
}

if (!function_exists('forward')) {
    function forward($package, array $params = [], $subRequest = \false)
    {
        $req = clone app('request');

        list($packageParts, $action) = explode('@', $package);

        $packageParts = explode('\\', $packageParts);

        $params['package'] = Utils::decamelize($packageParts[0]);
        $params['controller'] = Utils::decamelize($packageParts[count($packageParts) - 1]);
        $params['action'] = Utils::decamelize($action);

        $req->setParams($params);

        return app()->resolve($package, $req, clone app('response'), $subRequest);
    }
}
if (!function_exists('pagination')) {
    function pagination(Paginator $paginator, $partial = 'paginator', array $params = \null, View $view = \null)
    {
        $pages = ['pages' => $paginator->getPages()];

        if ($params !== \null) {
            $pages = array_merge($pages, (array) $params);
        }

        if ($view === \null) {
            $view = new View(\null);
        }

        return $view->partial($partial, $pages);
    }
}