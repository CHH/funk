<?php

namespace Funk\Test;

use Funk\Func;

class FuncTest extends \PHPUnit_Framework_TestCase
{
    function testCurry()
    {
        $cube = function($number) {
            return $number * $number;
        };

        $cube2 = Func::curry($cube, array(2));

        $this->assertEquals(4, $cube2());
    }

    function testOnce()
    {
        $called = 0;

        $fn = Func::once(function() use (&$called) {
            $called += 1;
        });

        for ($i = 0; $i < 3; $i++) {
            $fn();
        }

        $this->assertEquals(1, $called);
    }
}
