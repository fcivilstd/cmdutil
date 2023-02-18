<?php

namespace Util;

use InvalidArgumentException;

class Main
{
    private array $commands = [
        'git' => \Util\Git\GitController::class,
    ];

    private string $rootDir = '';

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    private function rootDir(): string
    {
        assert($this->rootDir !== '');

        return $this->rootDir;
    }

    public function execute(array $args): void
    {
        $cmd = '';
        foreach ($args as $arg) {
            if (preg_match('/\A--command=.+/u', $arg) === 1) {
                $cmd = str_replace('--command=', '', $arg);
                break;
            }
        }

        if (!isset($this->commands[$cmd])) {
            throw new InvalidArgumentException($cmd.' doesn\'t exist.');
        }

        (new $this->commands[$cmd])->execute(array_slice($args, 2));
    }
}
