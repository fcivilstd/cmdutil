<?php

namespace Util\Git;

use InvalidArgumentException;

class GitController
{
    private static array $operation = [
        'init' => \Util\Git\Operation\Init::class,
        'add' => \Util\Git\Operation\Add::class,
        'commit' => \Util\Git\Operation\Commit::class,
        'rm' => \Util\Git\Operation\Remove::class,
        'branch' => \Util\Git\Operation\Branch::class,
        'checkout' => \Util\Git\Operation\Checkout::class,
        'merge' => \Util\Git\Operation\Merge::class,
    ];

    public static function execute(array $args): void
    {
        if (!isset(self::$operation[$args[0]])) {
            throw new InvalidArgumentException($args[0].' doesn\'t exist.');
        }

        self::$operation[$args[0]]::execute(array_slice($args, 1));
    }
}
