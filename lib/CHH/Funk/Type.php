<?php

namespace CHH\Funk;

class Type
{
    static function toBool($var)
    {
        return static::castTo("boolean", $var);
    }

    static function toArray($var)
    {
        return static::castTo("array", $var);
    }

    static function toInteger($var)
    {
        return static::castTo("integer", $var);
    }

    static function toFloat($var)
    {
        return static::castTo("float", $var);
    }

    static function toString($var)
    {
        return static::castTo("string", $var);
    }

    static function toResource($var)
    {
        return static::castTo("resource", $var);
    }

    static protected function castTo($type, $value)
    {
        if (is_object($value)) {
            return static::castObjectToScalar($type, $value);
        }

        switch ($type) {
        case "string":
            return strval($value);
        case "int":
        case "integer":
            return intval($value);
        case "float":
            return floatval($value);
        case "boolean":
            return (bool) $value;
        case "array":
            return (array) $value;
        default:
            throw new \InvalidArgumentException("Invalid type '$type'");
        }
    }

    static protected function castObjectToScalar($type, $value)
    {
        switch ($type) {
        case "string":
            return strval($value);
        case "array":
            if ($value instanceof \Iterator) {
                return iterator_to_array($value);
            }

            return $value->__toArray();
        case "int":
        case "integer":
            return $value->__toInteger();
        case "float":
            return $value->__toFloat();
        case "resource":
            return $value->__toResource();
        case "boolean":
            return $value->__toBool();
        default:
            throw new \InvalidArgumentException("Invalid type '$type'");
        }
    }
}
