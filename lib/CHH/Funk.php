<?php

namespace CHH;

class Funk
{
    static function curry($callback, $arguments = array())
    {
        return function() use ($callback, $arguments) {
            return call_user_func_array(
                $callback, array_merge($arguments, func_get_args())
            );
        };
    }

    static function once($callback)
    {
        return function() use ($callback) {
            static $called = false;
            static $returnValue;

            if (!$called) {
                $returnValue = call_user_func_array($callback, func_get_args());
                $called = true;
            }

            return $returnValue;
        };
    }
}
