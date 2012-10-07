# Funk

[Funk](http://github.com/CHH/Funk) is a minimal functional library for
PHP.

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

use Funk\Collection;

echo (new Collection)
    ->extend(range(0, 20))
    ->keep(function($x) { return $x > 5; })
    ->remove(['gt', 7])
    ->map(function($val) { return strval($val); })
    ->join(' ');

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

