<?php

namespace Funk\Iterator;

use IteratorIterator;

class MappingIterator extends IteratorIterator
{
    protected $callback;

    function __construct(\Traversable $iterator, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback');
        }

        $this->callback = $callback;
        parent::__construct($iterator);
    }

    function current()
    {
        $inner = $this->getInnerIterator();

        return call_user_func_array($this->callback, array(
            $inner->current(),
            $inner->key(),
            $inner
        ));
    }
}
