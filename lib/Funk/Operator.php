<?php

namespace Funk;

class Operator
{
    protected $value;
    protected $operator;

    static function __callStatic($method, $arguments)
    {
        $class = get_called_class();
        $value = array_shift($arguments);

        return new $class($method, $value);
    }

    function __construct($operator, $value)
    {
        $this->value = $value;
        $this->operator = $operator;
    }

    function __invoke($value, $key)
    {
        switch ($this->operator) {
        case 'is':
            $returnValue = $value === $this->value;
            break;
        case 'eq':
        case '=':
            $returnValue = $value == $this->value;
            break;
        case 'gt':
        case '>':
            $returnValue = $value > $this->value;
            break;
        case 'gte':
        case '>=':
            $returnValue = $value >= $this->value;
            break;
        case 'lt':
        case '<':
            $returnValue = $value < $this->value;
            break;
        case 'lte':
        case '<=':
            $returnValue = $value <= $this->value;
            break;
        }

        return $returnValue;
    }
}
