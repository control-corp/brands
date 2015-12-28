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
    function env($env = \null) {

        $_env = config('env', 'production');

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
    function json($body = '', $code = 200) {
        return new Micro\Http\Response\JsonResponse($body, $code);
    }
}

if (!function_exists('html')) {
    function html($body = '', $code = 200) {
        return new Micro\Http\Response\HtmlResponse($body, $code);
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $code = 302) {
        return new Micro\Http\Response\RedirectResponse($url, $code);
    }
}

if (!function_exists('view')) {
    function view($template, array $data = \null, $injectPaths = \false) {
        return new Micro\Application\View($template, $data, $injectPaths);
    }
}