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

    public static function randomSentence($length, $alphabet = "abchefghjkmnpqrstuvwxyz0123456789")
    {
        $length = (int) $length;

        srand((double) microtime() * 1000000);

        $string = '';

        $i = 0;

        while ($i < $length) {
            $num    = rand() % 33;
            $tmp 	= substr($alphabet, $num, 1);
            $string = $string . $tmp;
            $i++;
        }

        return $string;

    }

    public static function buildOptions($optionsInput, $value = 0, $emptyOption = '', $emptyOptionValue = "")
    {
        $options = '';

        if ($emptyOption) {
            $options .= '<option value="' . static::e($emptyOptionValue) . '">' . static::e($emptyOption) . '</option>';
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        foreach ($optionsInput as $optionGroup => $group) {
            if (is_array($group)) {
                $options .= '<optgroup label="' . static::e($optionGroup) . '">';
                $options .= self::buildOptions($group, $value, '', '');
                $options .= '</optgroup>';
            } else {
                $selected = (in_array($optionGroup, $value) ? ' selected="selected"' : '');
                $options .= '<option' . $selected . ' value="' . static::e($optionGroup) . '">' . static::e($group) . '</option>';
            }
        }

        return $options;
    }

    public static function e($value)
    {
        return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
    }
}