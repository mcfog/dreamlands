<?php namespace Dreamlands\Utility;

class Utility
{
    public static function getNanotime()
    {
        list($ms, $s) = explode(' ', microtime());
        return intval($ms * 1000000) + $s * 1000000;
    }
}
