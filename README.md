# Funk

[Funk](http://github.com/CHH/Funk) is a minimal functional library for
PHP.

## Install

Install with [Composer](http://getcomposer.org):

    php composer.phar require chh/funk:*@dev

Then require `vendor/autoload.php` in your application.

## Collection

The `Funk\Collection` class wraps a PHP iterable value and provides
methods to manipulate it.

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

