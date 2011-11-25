<?php

namespace Moka;

function identityFn($predicate)
{
    return(function($val, $key) use ($predicate) {
        if (is_callable($predicate)) {
            return(call_user_func($predicate, $val, $key) ? true : false);
        }
        return($val === $predicate);
    });
}

function Arr($array)
{
    return makeArray($array);
}

function makeArray($array)
{
    return new ArrayDecorator($array);
}

class ArrayDecorator implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $array;

    function __construct($array)
    {
        $this->array = $array;
    }

    /*
     * implements \IteratorAggregate
     */
    function getIterator()
    {
        return new \ArrayIterator($this->array);
    }

    /*
     * implements \ArrayAccess
     */

    function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /*
     * implements \Countable
     */
    function count() 
    {
        return count($this->array);
    }

    function append($val)
    {
        $a = $this->array;
        $a[] = $val;

        return new static($a);
    }

    function join($glue)
    {
        return join($glue, $this->array);
    }

    function tap($callback, $argv = array())
    {
        array_unshift($argv, $this->array);
        call_user_func_array($callback, $argv);
        return $this;
    }

    function all($predicate)
    {
        $fn = identityFn($predicate);

        foreach ($this->array as $key => $val) {
            if (!$fn($val, $key)) {
                return false;
            }
        }
        return true;
    }

    function some($predicate)
    {
        $fn = identityFn($predicate);

        foreach ($this->array as $key => $val) {
            if ($fn($val, $key)) {
                return true;
            }
        }
        return false;
    }

    function compact($callback = null)
    {
        if (null === $callback) {
            return new static(array_filter($this->array));
        }
        return $this->keep($callback);
    }

    function attr($attribute)
    {
        return $this->map(function($val) use ($attribute) {
            if (is_object($val)) {
                return $val->{$attribute};
            }
        });
    }

    function item($offset)
    {
        return $this->map(function($val) use ($offset) {
            if (is_array($val)) {
                return $val[$offset];
            }
        });
    }

    function invoke($method, $argv = array())
    {
        return $this->map(function($val) use ($argv) {
            $callArgv = $argv;
            array_unshift($callArgv, $val);
            return call_user_func_array(array($val, $method), $callArgv);
        });
    }

    function map($callback)
    {
        return new static(array_map($callback, $this->array));
    }

    function isEmpty($predicate = null)
    {
        if (null === $predicate) {
            return count($this->array) === 0;
        }

        if (!is_callable($predicate)) {
            throw new \InvalidArgumentException(
                'Predicate should be a callback, which returns the element\'s truthness'
            );
        }

        $empty = true;
        foreach ($this->array as $key => $val) {
            $empty = call_user_func($predicate, $val) ? true : false;
        }

        return $empty;
    }

    function extend($array)
    {
        return new static(array_merge($this->array, $array));
    }

    function reverse()
    {
        return new static(array_reverse($this->array));
    }

    function keep($predicate) 
    {
        $keep = identityFn($predicate);

        $result = array();
        foreach ($this->array as $key => $val) {
            if ($keep($val, $key)) {
                $result[$key] = $val;
            }
        }
        return new static($result);
    }

    function remove($predicate)
    {
        $rem = identityFn($predicate);

        $result = array();
        foreach ($this->array as $key => $val) {
            if (!$rem($val, $key)) {
                $result[$key] = $val;
            }
        }
        return new static($result);
    }
}
