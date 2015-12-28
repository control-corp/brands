<?php

namespace Micro;

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
}