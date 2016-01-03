<?php

MicroLoader::register();

include __DIR__ . '/src/Micro/helpers.php';

class MicroLoader
{
    protected static $paths;
    protected static $files = [];

    public static function register()
    {
        static::addPath(__DIR__ . '/src');

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

        foreach (static::$paths as $path) {

            $file = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

            if (file_exists($file)) {
                include $file;
                static::$files[$class] = $file;
                return \true;
            }
        }
    }

    public static function addPath($paths)
    {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                static::addPath($path);
            }
            return;
        }

        static::$paths[] = $paths;
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