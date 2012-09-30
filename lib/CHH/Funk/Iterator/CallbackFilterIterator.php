<?php

namespace CHH\Funk\Iterator;

class CallbackFilterIterator extends \FilterIterator
{
    protected $callback;

    function __construct(\Iterator $iterator, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Invalid callback");
        }

        $this->callback = $callback;
        parent::__construct($iterator);
    }

    function accept()
    {
        return call_user_func_array($this->callback, array(
            $this->current(), $this->key(), $this->getInnerIterator()
        ));
    }
}
