<?php

namespace Funk;

use AppendIterator;
use ArrayIterator;
use EmptyIterator;
use LimitIterator;
use RegexIterator;
use itertools;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $iterator;

    # Returns a callback functions suitable for filter operations.
    #
    # The predicate could look as follows:
    #
    # - A plain scalar, then the identity ("===") operator is used for compare to
    #   the collection item.
    # - A callback, then the result of the call is returned.
    #
    # predicate - Specification of the predicate.
    # inverse   - Inverse the result of the predicate.
    #
    # Returns a Closure.
    static function identityFn($predicate, $inverse = false)
    {
        return function($val, $key) use ($predicate, $inverse) {
            if (is_callable($predicate)) {
                $returnValue = (bool) call_user_func($predicate, $val, $key);
            } elseif ($predicate === null) {
                $returnValue = $val == true;
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

    # Constructor
    #
    # iterable - Value for initializing the collection, can be anything traversable
    #            or an array. Scalars are converted to an array with one element.
    function __construct($iterable = null)
    {
        $this->iterator = $this->toIterator($iterable);
    }

    function offsetSet($offset, $value)
    {
        $this->assertArrayIterator();
        $this->iterator->offsetSet($offset, $value);
    }

    function offsetGet($offset)
    {
        $this->assertArrayIterator();
        return $this->iterator->offsetGet($offset);
    }

    function offsetExists($offset)
    {
        $this->assertArrayIterator();
        return $this->iterator->offsetExists($offset);
    }

    function offsetUnset($offset)
    {
        $this->assertArrayIterator();
        return $this->iterator->offsetUnset($offset);
    }

    # Public: Chainable version of "clone".
    #
    # Returns a clone of current the Collection
    function dup()
    {
        return clone $this;
    }

    # Public: Counts items in the current collection.
    #
    # Returns the count as an Integer.
    function count()
    {
        if ($this->iterator instanceof \Countable) {
            return $this->iterator->count();
        }

        return iterator_count($this->iterator);
    }

    # Public: Satisfies the "IteratorAggregate" interface.
    #
    # Returns an Iterator.
    function getIterator()
    {
        return $this->iterator;
    }

    # Public: Converts the wrapped iterator into an array.
    #
    # associative - If True respect keys returned by the iterator (default: true)
    #
    # Returns an Array.
    function asArray($associative = true)
    {
        return iterator_to_array($this->getIterator(), $associative);
    }

    # Public: Match each value against the regular expression pattern.
    #
    # pattern - PCRE compatible Regular Expression pattern.
    #
    # Examples
    #
    #   <?php
    #
    #   $c = new Collection(array('foo', 'bar', 'boo'));
    #   var_export(
    #     $c->match('/o+/')->asArray()
    #   );
    #   # Output:
    #   # array("foo", "boo")
    #
    # Returns the Collection.
    function match($pattern)
    {
        $this->iterator = new RegexIterator($this->iterator, $pattern);

        return $this;
    }

    # Public: Invokes the callback for each element yielded by the iterator
    # and passes the current value and key to the callback.
    #
    # callback - Callback, gets passed the current value and key as arguments.
    # context  - Bind this context when a Closure is given as callback.
    #
    # Returns the Collection.
    function each($callback, $context = null)
    {
        if ($context !== null and $callback instanceof \Closure) {
            $callback = \Closure::bind($callback, $context);
        }

        $iterator = $this->getIterator();

        itertools\walk($iterator, $callback);

        return $this;
    }

    # Public: Converts the value to an Iterator and appends it to the 
    # current iterator via an AppendIterator.
    #
    # value - Value which gets converted to an Iterator. See toIterator().
    #
    # Returns the Collection.
    function append($value)
    {
        $appendIterator = new AppendIterator();
        $appendIterator->append($this->iterator);
        $appendIterator->append($this->toIterator($value));

        $this->iterator = $appendIterator;

        return $this;
    }

    # Public: Slices the collection beginning with an offset to a
    # given length.
    #
    # offset - First item to include.
    # count  - Count of items to include.
    #
    # Returns the Collection.
    function slice($offset, $count = -1)
    {
        $this->iterator = itertools\slice($this->iterator, $offset, $count);

        return $this;
    }

    function head($elements)
    {
        return $this->slice(0, $elements);
    }

    function tail($elements)
    {
        $count = $this->count();
        return $this->slice($count > 0 ? $count - $elements : 0);
    }

    function first()
    {
        $it = $this->dup()->head(1)->getIterator();
        $it->rewind();

        return $it->current();
    }

    function last()
    {
        $it = $this->dup()->tail(1)->getIterator();
        $it->rewind();

        return $it->current();
    }

    function flip()
    {
        $this->iterator = itertools\flip($this->iterator);

        return $this;
    }

    # Public: Joins all collection items with the given glue.
    #
    # glue - String which is used to join the items (default: '')
    #
    # Returns a String.
    function join($glue = '')
    {
        return join($glue, $this->asArray());
    }

    # Public: Call the callback and pass the iterator. Useful for 
    # inserting debug statements into a chain of operations.
    #
    # callback  - Callback, gets passed the current iterator.
    # arguments - Additional arguments passed to the callback.
    #
    # Examples
    #
    #   <?php
    #   $a = new Collection(array("a", "b", "c"));
    #
    #   echo $a->keep(array("gte", "b"))
    #     ->tap(function($iterator) {
    #       var_dump($iterator);
    #     })
    #     ->join(', ');
    #
    # Returns the Collection.
    function tap($callback, $arguments = array())
    {
        array_unshift($arguments, $this->getIterator());

        call_user_func_array($callback, $arguments);

        return $this;
    }

    # Public: Checks if all collection members satisfy the provided 
    # condition.
    #
    # predicate - Value, array or callback. See identityFn().
    #
    # Returns a Boolean.
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

    # Public: Checks if at least one collection member satisfies the
    # provided predicate.
    #
    # predicate - See identityFn().
    #
    # Returns a Boolean.
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

    # Public: Removes all falsy members. Optionally accepts a callback 
    # which determines falsyness.
    #
    # callback - Optional callback, which determines if the member is 
    #            falsy.
    # 
    # Returns the Collection.
    function compact($callback = null)
    {
        if (null === $callback) {
            $callback = function($val) {
                return empty($val);
            };
        }

        return $this->remove($callback);
    }

    # Public: Picks the given attribute from each object member in the 
    # collection.
    #
    # attribute - Name of the object attribute.
    #
    # Examples
    #
    #   <?php
    #   $a = new Collection(array(
    #      (object) array("name" => "John"),
    #      (object) array("name" => "Tim"),
    #      array("name" => "Foo")
    #   ));
    #
    #   var_export(
    #     $a->attr('name')->asArray()
    #   );
    #   # Output:
    #   # array("John", "Tim")
    #
    # Returns the Collection.
    function attr($attribute)
    {
        return $this->map(function($val) use ($attribute) {
            if (is_object($val)) {
                return $val->{$attribute};
            }
        });
    }

    # Public: Picks the given array offset from each array member in the 
    # collection.
    #
    # offset - Array key which should be put into the collection.
    #
    # Examples
    #
    #   <?php
    #   $a = new Collection(array(
    #      (object) array("name" => "John"),
    #      (object) array("name" => "Tim"),
    #      array("name" => "Foo")
    #   ));
    #
    #   var_export(
    #     $a->item('name')->asArray()
    #   );
    #   # Output:
    #   # array("Foo")
    #
    # Returns the Collection.
    function item($offset)
    {
        return $this->map(function($val) use ($offset) {
            if (is_array($val)) {
                return isset($val[$offset]) ? $val[$offset] : null;
            }
        });
    }

    # Public: Invokes the method on each object in the collection.
    #
    # method    - Name of the method.
    # arguments - Additional arguments passed to the method.
    #
    # Returns the Collection.
    function invoke($method, $arguments = array())
    {
        return $this->map(function($value) use ($arguments) {
            return call_user_func_array(array($value, $method), $arguments);
        });
    }

    function map($callback)
    {
        $this->iterator = itertools\map($callback, $this->getIterator());
        return $this;
    }

    function reduce($callback, $initial = null)
    {
        return array_reduce($this->asArray(false), $callback, $initial);
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
        $this->iterator = itertools\filter($this->getIterator(), static::identityFn($predicate));

        return $this;
    }

    function remove($predicate)
    {
        $this->iterator = itertools\filter($this->getIterator(), static::identityFn($predicate, true));

        return $this;
    }

    protected function assertArrayIterator()
    {
        if (!$this->iterator instanceof ArrayIterator) {
            $this->iterator = new ArrayIterator($this->asArray());
        }
    }

    # Converts the value to an Iterator.
    #
    # All collection operations operate on iterators, so here we convert
    # all input values (passed to the constructor, or to append() for example)
    # to valid Iterators.
    #
    # Conversion is done with the following rules:
    #
    # - Instances of "Iterator" get returned as-is.
    # - Arrays get wrapped in an ArrayIterator.
    # - Instances of "Traversable" get wrapped in an IteratorIterator.
    # - Null values are turned into an EmptyIterator
    # - Everything else is casted to Array and wrapped in an ArrayIterator.
    #
    # value - Value to convert.
    #
    # Returns an Iterator.
    protected function toIterator($value)
    {
        return itertools\to_iterator($value);
    }
}

