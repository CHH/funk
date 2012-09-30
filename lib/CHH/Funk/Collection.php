<?php

namespace CHH\Funk;

use AppendIterator;
use ArrayIterator;
use EmptyIterator;
use LimitIterator;

class Collection implements \IteratorAggregate, \Countable
{
    protected $iterator;

    static function identityFn($predicate, $inverse = false)
    {
        return function($val, $key) use ($predicate, $inverse) {
            if (is_callable($predicate)) {
                $returnValue = (bool) call_user_func($predicate, $val, $key);
            } else if (is_array($predicate)) {
                list($operator, $val2) = $predicate;

                switch ($operator) {
                case 'eg':
                case '=':
                    $returnValue = $val === $val2;
                    break;
                case 'gt':
                case '>':
                    $returnValue = $val > $val2;
                    break;
                case 'gte':
                case '>=':
                    $returnValue = $val >= $val2;
                    break;
                case 'lt':
                case '<':
                    $returnValue = $val < $val2;
                    break;
                case 'lte':
                case '<=':
                    $returnValue = $val <= $val2;
                    break;
                }
            } else {
                $returnValue = $val === $predicate;
            }

            if ($inverse) {
                return !$returnValue;
            } else {
                return $returnValue;
            }
        };
    }

    function __construct($iterable = null)
    {
        $this->iterator = $this->toIterator($iterable);
    }

    function dup()
    {
        return clone $this;
    }

    function count()
    {
        if ($this->iterator instanceof \Countable) {
            return $this->iterator->count();
        }

        return iterator_count($this->iterator);
    }

    function getIterator()
    {
        return $this->iterator;
    }

    function asArray($associative = true)
    {
        return iterator_to_array($this->getIterator(), $associative);
    }

    function append($value)
    {
        $appendIterator = new AppendIterator();
        $appendIterator->append($this->iterator);
        $appendIterator->append($this->toIterator($value));

        $this->iterator = $appendIterator;
        return $this;
    }

    function slice($offset, $count = -1)
    {
        $this->iterator = new LimitIterator($this->iterator, $offset, $count);
        return $this;
    }

    function join($glue = '')
    {
        return join($glue, $this->asArray());
    }

    function tap($callback, $arguments = array())
    {
        array_unshift($arguments, $this->getIterator());

        call_user_func_array($callback, $arguments);

        return $this;
    }

    function all($predicate = null)
    {
        $fn = static::identityFn($predicate);

        foreach ($this as $key => $val) {
            if (!$fn($val, $key)) {
                return false;
            }
        }

        return true;
    }

    function some($predicate = null)
    {
        $fn = static::identityFn($predicate);

        foreach ($this as $key => $value) {
            if ($fn($value, $key)) {
                return true;
            }
        }

        return false;
    }

    function compact($callback = null)
    {
        if (null === $callback) {
            $callback = function($val) {
                return empty($val);
            };
        }

        return $this->remove($callback);
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
                return isset($val[$offset]) ? $val[$offset] : null;
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
        $this->iterator = new Iterator\MappingIterator($this->iterator, $callback);
        return $this;
    }

    function isEmpty($predicate = null)
    {
        return $this->compact($predicate)->count() === 0;
    }

    function extend($array)
    {
        $this->iterator = new ArrayIterator(array_merge(
            $this->asArray(),
            $array
        ));

        return $this;
    }

    function reverse()
    {
        $this->iterator = new ArrayIterator(array_reverse($this->asArray()));
        return $this;
    }

    function keep($predicate)
    {
        $keep = static::identityFn($predicate);
        $this->iterator = $this->createCallbackFilterIterator($this->iterator, $keep);

        return $this;
    }

    function remove($predicate)
    {
        $fn = static::identityFn($predicate, true);
        $this->iterator = $this->createCallbackFilterIterator($this->iterator, $fn);

        return $this;
    }

    protected function createCallbackFilterIterator($iterator, $callback)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return new \CallbackFilterIterator($iterator, $callback);
        } else {
            return new Iterator\CallbackFilterIterator($iterator, $callback);
        }
    }

    protected function toIterator($value)
    {
        if ($value instanceof \Iterator) {
            return $value;

        } else if (is_array($value)) {
            return new ArrayIterator($value);

        } else if ($value instanceof \Traversable) {
            return new \IteratorIterator($value);

        } else if (null === $value) {
            return new EmptyIterator;

        } else {
            return new ArrayIterator((array) $value);
        }
    }
}

