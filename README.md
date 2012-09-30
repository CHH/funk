# Funk

## Casting

Example:

    <?php

    use Funk\Type;

    class Foo
    {
        function __toInt()
        {
            return 42;
        }
    }

    echo Type::intval(new Foo) + 1;
    # Output:
    # 43

