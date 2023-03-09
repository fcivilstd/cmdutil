<?php

namespace Util;

use InvalidArgumentException;

class Main
{
    private static array $commands = [
        'git' => \Util\Git\GitController::class,
    ];

    public static function execute(array $args): void
    {
        $cmd = '';
        foreach ($args as $arg) {
            if (preg_match('/\A--command=.+/u', $arg) === 1) {
                $cmd = str_replace('--command=', '', $arg);
                break;
            }
        }

        if (!isset(self::$commands[$cmd])) {
            throw new InvalidArgumentException($cmd.' doesn\'t exist.');
        }

        self::$commands[$cmd]::execute(array_slice($args, 2));
    }
}
