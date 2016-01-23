<?php

\MicroLoader::register();

include __DIR__ . '/src/helpers.php';

class MicroLoader
{
    protected static $paths;

    protected static $files = [];

    public static function register()
    {
        static::addPath('Micro\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

        spl_autoload_register(array('MicroLoader', 'autoload'));
    }

    public static function autoload($class)
    {
        if ($class[0] === '\\') {
            $class = ltrim($class, '\\');
        }

        if (isset(static::$files[$class])) {
            include static::$files[$class];
            return \true;
        }

        $parts  = explode('\\', $class);
        $vendor = $parts[0] . '\\';

        if (isset(static::$paths[$vendor])) {

            $file = static::$paths[$vendor] . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($vendor))) . '.php';

            if (file_exists($file)) {
                include $file;
                static::$files[$class] = $file;
                return \true;
            }

        }
    }

    public static function addPath($prefix, $path = \null, $suffix = \null)
    {
        if (is_array($prefix)) {
            foreach ($prefix as $k => $v) {
                static::addPath($k, $v, $suffix);
            }
            return;
        }

        if ($path === \null) {
            return;
        }

        $prefix = rtrim($prefix, '\\') . '\\';

        static::$paths[$prefix] = rtrim($path, '/\\') . ($suffix !== \null ? DIRECTORY_SEPARATOR . trim($suffix, '/\\') : '');
    }

    public static function getFiles()
    {
        return static::$files;
    }

    public static function setFiles(array $files)
    {
        static::$files = $files;
    }
}