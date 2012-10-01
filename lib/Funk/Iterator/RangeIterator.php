<?php

namespace Funk\Iterator;

class RangeIterator implements \Iterator
{
    protected $start;
    protected $end;
    protected $position;

    function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    function rewind()
    {
        $this->position = $this->start;
    }

    function current()
    {
        return $this->position;
    }

    function key()
    {
        return $this->position;
    }

    function next()
    {
        $this->position++;
    }

    function valid()
    {
        return $this->position <= $this->end;
    }
}
