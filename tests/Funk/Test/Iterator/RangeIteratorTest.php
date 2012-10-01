<?php

namespace Funk\Test\Iterator;

use Funk\Iterator\RangeIterator;

class RangeIteratorTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $range = new RangeIterator(0, 10);

        $this->assertEquals(
            range(0, 10),
            iterator_to_array($range)
        );
    }
}
