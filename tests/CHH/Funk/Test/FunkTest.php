<?php

namespace CHH\Funk\Test;

use CHH\Funk;

class FunkTest extends \PHPUnit_Framework_TestCase
{
    function testCurry()
    {
        $cube = function($number) {
            return $number * $number;
        };

        $cube2 = Funk::curry($cube, array(2));

        $this->assertEquals(4, $cube2());
    }

    function testOnce()
    {
        $called = 0;

        $fn = Funk::once(function() use (&$called) {
            $called += 1;
        });

        for ($i = 0; $i < 3; $i++) {
            $fn();
        }

        $this->assertEquals(1, $called);
    }
}
