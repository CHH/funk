<?php

namespace Funk\Iterator;

class RangeIterator implements \Iterator
{
    protected $start;
    protected $end;
    protected $step;
    protected $position;

    function __construct($start, $end, $step = 1)
    {
        $this->start = $this->position = $start;
        $this->end = $end;
        $this->step = $step;
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
        $this->position += $this->step;
    }

    function valid()
    {
        return $this->position <= $this->end;
    }
}
