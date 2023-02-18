<?php

namespace Util\Git\Operation;

class Init
{
    private string $gitDirName = 'dotgit';

    public function execute(array $args): void
    {
        if (file_exists($this->gitDirName)) {
            return;
        }

        mkdir($this->gitDirName);
    }
}
