<?php

namespace Funk\Test;

use Funk\Collection;
use Funk\Collection\Operator;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    function testAppend()
    {
        $a = new Collection(array('one', 'two', 'three'));

        $this->assertEquals(
            array('one', 'two', 'three', 'four'),
            $a->dup()->append('four')->asArray(false)
        );
    }

    function testMatch()
    {
        $a = new Collection(array('foo', 'boo', 'bar'));

        $this->assertEquals(
            array('foo', 'boo'),
            $a->match('/o{2}/')->asArray()
        );
    }

    function testCount()
    {
        $a = new Collection(array('one', 'two', 'three'));

        # Collection implements the Countable interface
        $this->assertEquals(3, count($a));
        $this->assertEquals(3, $a->count());
    }

    function testIsEmpty()
    {
        $a = new Collection(array(null, 0, 0));

        $this->assertTrue($a->isEmpty(function($val) {
            return !$val;
        }));
    }

    function testJoin()
    {
        $a = new Collection(range(0, 9));

        $this->assertEquals(
            '0123456789',
            $a->join()
        );

        $this->assertEquals(
            join(',', range(0, 9)),
            $a->join(',')
        );

        $a = new Collection();
        $this->assertEquals('', $a->join());
    }

    function testSlice()
    {
        $a = new Collection(range(0, 6));

        $this->assertEquals(
            range(0, 3),
            array_values($a->dup()->slice(0, 4)->asArray())
        );

        $this->assertEquals(
            range(3, 6),
            array_values($a->dup()->slice(3)->asArray())
        );
    }

    function testCompact()
    {
        $a = new Collection(array(
            'a' => 3,
            'b' => 2,
            'c' => array(),
            'd' => null
        ));

        $this->assertEquals(2, count($a->compact()));

        $this->assertEquals(
            3,
            $a->compact(function($val) {
                return $val === null;
            })
            ->tap(function($iter) {
                var_dump(iterator_to_array($iter));
            })
            ->count()
        );
    }

    function testPassesIteratorsThrough()
    {
        $a = new Collection(new \EmptyIterator);
        $this->assertInstanceOf('\\EmptyIterator', $a->getIterator());
    }

    function testKeep()
    {
        $a = new Collection(range(0, 10));

        $this->assertEquals(
            range(0, 6),
            $a->keep(function($val) { return $val < 7; })->asArray()
        );
    }

    function testAttr()
    {
        $a = new Collection(array(
            (object) array('name' => 'John'),
            (object) array('name' => 'Jack'),
            array('name' => 'Jill'),
            (object) array('name' => 'Tim')
        ));

        $this->assertEquals(
            array('John', 'Jack', null, 'Tim'),
            $a->attr('name')->asArray()
        );
    }

    function testItem()
    {
        $a = new Collection(array(
            array('name' => 'John'),
            array('name' => 'Jack'),
            array('foo' => 'bar'),
            array('name' => 'Tim')
        ));

        $this->assertEquals(
            array('John', 'Jack', null, 'Tim'),
            $a->item('name')->asArray(false)
        );
    }

    function testItemEmptyCollection()
    {
        $a = new Collection;
        $this->assertEmpty($a->item("name")->asArray());
    }

    function testTap()
    {
        $array = array('foo', 'bar', 'baz');
        $a = new Collection($array);

        $called = false;

        // Test proper chaining
        $a->append('bab')->tap(function($array) use (&$called) {
            $called = true;

            \PHPUnit_Framework_Assert::assertEquals(
                array('foo', 'bar', 'baz', 'bab'),
                iterator_to_array($array, false)
            );
        })->reverse();

        $this->assertTrue($called);
    }

    function testExtend()
    {
        $a = new Collection(array(1, 2));

        $this->assertEquals(
            array(1, 2, 3, 4),
            $a->extend(array(3, 4))->asArray()
        );
    }

    function testFirst()
    {
        $a = new Collection(array(1, 2, 3, 4));
        $b = new Collection();

        $this->assertEquals(1, $a->first());
        $this->assertEquals(null, $b->first());
    }

    function testLast()
    {
        $a = new Collection(array(1, 2, 3, 4));
        $b = new Collection();

        $this->assertEquals(4, $a->last());
        $this->assertEquals(null, $b->last());
    }

    function testAll()
    {
        $a = new Collection(array(1, 2, 3, 4));

        $this->assertTrue($a->all(function($it) {
            return $it > 0;
        }));

        $this->assertFalse($a->all(function($it) {
            return $it > 1;
        }));
    }

    function testSome()
    {
        $a = new Collection(array(1, 2, 3, 4));

        $this->assertTrue($a->some(1));

        $this->assertTrue($a->some(function($it) {
            return $it > 1;
        }));
    }

    function testRemove()
    {
        $a = new Collection(range(0, 10));

        $a->remove(function($val) { return $val > 7; });

        $this->assertEquals(range(0, 7), $a->asArray());
    }

    function testOperatorShortcut()
    {
        $a = new Collection(range(0, 10));

        $this->assertEquals(
            range(0, 7),
            $a->remove(Operator::gt(7))->asArray()
        );
    }
}
