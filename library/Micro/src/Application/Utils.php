<?php

namespace Micro\Application;

class Utils
{
    public static function decamelize($value)
    {
        return strtolower(trim(preg_replace('/([A-Z])/', '-$1', $value), '-'));
    }

    public static function camelize($value)
    {
        $value = preg_replace('/[^a-z0-9-]/ius', '', $value);

        if (strpos($value, '-') !== false) {
            $value = str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));
        }

        return $value;
    }

    public static function safeSerialize($s)
    {
        return base64_encode(serialize($s));
    }

    public static function safeUnserialize($s)
    {
        return unserialize(base64_decode($s));
    }
}