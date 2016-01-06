<?php

if (!function_exists('app')) {
    function app($service = \null)
    {
        $container = Micro\Container\Container::getInstance();

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
        $packagePath = app()->getPackage($package)->getDir();

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
        $_env = getenv('APP_ENV');

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
    function route($name, array $data = [], $qsa = \false)
    {
        static $cache = [];

        $hash = md5($name . json_encode($data) . (string) $qsa);

        if (isset($cache[$hash])) {
            return $cache[$hash];
        }

        return $cache[$hash] = app('router')->assemble($name, $data, $qsa);
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
        return new Micro\Http\Response\JsonResponse($body, $code);
    }
}

if (!function_exists('html')) {
    /**
     * @param string $body
     * @param int $code
     * @return \Micro\Http\Response\HtmlResponse
     */
    function html($body = '', $code = 200)
    {
        return new Micro\Http\Response\HtmlResponse($body, $code);
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
        return new Micro\Http\Response\RedirectResponse($url, $code);
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
        return new Micro\Application\View($template, $data, $injectPaths);
    }
}