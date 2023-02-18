<?php

namespace Util\Git\Operation;

class Init
{
    public function execute(array $args): void
    {
        if (!file_exists('dotgit')) {
            mkdir('dotgit');
        }

        if (!file_exists('objects')) {
            mkdir('dotgit/objects');
        }
    }
}
