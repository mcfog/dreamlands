<?php namespace Dreamlands\Utility;

class Utility
{
    public static function getNanotime()
    {
        list($ms, $s) = explode(' ', microtime());
        return intval($ms * 1000000) + $s * 1000000;
    }

    public static function base36($num)
    {
        return base_convert($num, 10, 36);
    }

    public static function base36_decode($str)
    {
        return (int)base_convert($str, 36, 10);
    }

    public static function pluckObj($object, array $keys)
    {
        $result = new \stdClass();
        foreach ($keys as $k => $v) {
            if (is_int($k)) {
                $result->{$v} = $object->{$v};
            } else {
                $result->{$k} = $v;
            }
        }
        return $result;
    }
}
