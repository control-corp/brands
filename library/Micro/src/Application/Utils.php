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
        $value = preg_replace('/[^a-z0-9-._]/ius', '', $value);

        if (strpos($value, '-') !== \false) {
            $value = str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));
        }

        if (strpos($value, '_') !== \false) {
            $value = str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
        }

        if (strpos($value, '.') !== \false) {
            $value = str_replace(' ', '', ucwords(str_replace('.', ' ', $value)));
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

    public static function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64urlDecode($data, $strict = \false)
    {
        $mod = strlen($data) % 4;
        return base64_decode(strtr($data, '-_', '+/') . ($mod ? substr('====', $mod) : ''), $strict);
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

        if (!is_array($value)) {
            $value = [$value];
        }

        if ($emptyOption) {
            $optionsInput = [escape($emptyOptionValue) => escape($emptyOption)] + $optionsInput;
        }

        foreach ($optionsInput as $optionGroup => $group) {
            if (is_array($group)) {
                $options .= '<optgroup label="' . escape($optionGroup) . '">';
                $options .= self::buildOptions($group, $value, '', '');
                $options .= '</optgroup>';
            } else {
                $selected = (in_array($optionGroup, $value) ? ' selected="selected"' : '');
                $options .= '<option' . $selected . ' value="' . escape($optionGroup) . '">' . escape($group) . '</option>';
            }
        }

        return $options;
    }

    public static function iteratorToArray($iterator, $recursive = \true)
    {
        if (!is_array($iterator) && !$iterator instanceof \Traversable) {
            throw new \InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        if (!$recursive) {
            if (is_array($iterator)) {
                return $iterator;
            }
            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray')) {
            return $iterator->toArray();
        }

        $array = [];

        foreach ($iterator as $key => $value) {
            if (is_scalar($value)) {
                $array[$key] = $value;
                continue;
            }
            if ($value instanceof \Traversable) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }
            if (is_array($value)) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }
            $array[$key] = $value;
        }

        return $array;
    }
}