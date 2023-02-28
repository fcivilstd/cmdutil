<?php

namespace Util\Git\Operation;

use InvalidArgumentException;

class Branch
{
    public function execute(array $args): void
    {
        if ($args === []) {
            echo 'specify branch name'.PHP_EOL;
            return;
        }

        $branch = str_replace(' ', '', $args[0]);

        assert(file_exists('dotgit/refs/heads') !== false);
        if (file_exists('dotgit/refs/heads/'.$branch)) {
            throw new InvalidArgumentException($branch.' has already existed.');
        }

        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');
        $fp = fopen('dotgit/refs/heads/'.$branch, 'w');
        fwrite($fp, file_get_contents('dotgit/'.$HEAD));
        fclose($fp);
    }
}
