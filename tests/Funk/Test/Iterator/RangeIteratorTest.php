<?php

namespace Funk\Test\Iterator;

use Funk\Iterator\RangeIterator;

class RangeIteratorTest extends \PHPUnit_Framework_TestCase
{
    function testDefaultStep()
    {
        $range = new RangeIterator(0, 10);

        $this->assertEquals(
            range(0, 10),
            iterator_to_array($range, false)
        );
    }

    function testFloatStep()
    {
        $range = new RangeIterator(1, 3, 0.5);

        $this->assertEquals(
            range(1, 3, 0.5),
            iterator_to_array($range, false)
        );
    }
}
