# Funk

[Funk](http://github.com/CHH/Funk) is a minimal functional library for
PHP.

It's a collection of some things, I've felt the need for in many
projects:

* Dealing with Currying, memoizing and limiting calls to functions
  (`Funk\Func`).
* Common collection operators which deal in a uniform way with both
  arrays __and__ Iterators (`Funk\Collection`).
* A tight wrapper around casting operations, which also deals with
  casting objects to scalars (`Funk\Type`).

## Install

Install with [Composer](http://getcomposer.org):

    php composer.phar require chh/funk:*@dev

Then require `vendor/autoload.php` in your application.

## Collection

The `Funk\Collection` class wraps a PHP iterable value and provides
methods to manipulate it. The collection provides nearly all operations
using Iterators and exposes this iterator via `getIterator` (also
implements `IteratorAggregate`). That means that nearly all operations
are lazy evaluated.

You can see this in action, if you wrap a PDO statement in a collection
and then call `map`. This wraps the PDO Statement in a special
"MappingIterator", which applies the callback function on each element
and yields the callback's return value. But this happens only when the
"MappingIterator" is iterated, until then nothing happens.

Example:

```php
<?php

use Funk\Collection,
    Funk\Collection\Operator as op;

echo (new Collection(range(0, 20)))
    ->keep(function($x) { return $x > 5; })
    ->remove(op::gt(7))
    ->map(function($val) { return strval($val); })
    ->join(' ');
```

Everywhere where the argument is `predicate` the operation takes several
different values with different characteristics:

* An operator shortcut, which is an instance of the `Operator` class.
  Instances can be conveniently created by calling the operator as
  static method on this class. Valid operators include: `is`, `eq`, `gt`, `gte`, `lt` and `lte`.
* Value, which is then compared against the current iterator's value by
  using `===`.
* Callback, the return value is used.
* Null, which translates to `$value == true`.

### \_\_construct($iterable = null)

### dup()

### extend($value)

### append($value)

### map($fn)

### reduce($callback, $initial = null)

### count()

### match($pattern)

### asArray($assoc = true)

### slice($offset, $count = -1)

### join($glue = '')

### tap($callback, $arguments = array())

### all($predicate = null)

### some($predicate = null)

### compact($callback = null)

### attr($attribute)

### item($offset)

### invoke($method, $arguments = array())

### isEmpty($predicate = null)

### reverse()

### keep($predicate)

### remove($predicate)

## Casting

Example:

    <?php

    use Funk\Type;

    class Foo
    {
        function __toInt()
        {
            return 42;
        }
    }

    echo Type::intval(new Foo) + 1;
    # Output:
    # 43

