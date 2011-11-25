<?php

use Moka as m;

$l = m\makeArray(['foo', 'bar', 'baz']);

var_dump($l->count());
// int(3)

// Each method returns a new wrapper object
$withoutB = $l->remove(function($it) {
    return $it[0] == 'b';
});

var_dump($withoutB);
// => ['foo']

