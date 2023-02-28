<?php

namespace Util\Git\Operation;

use InvalidArgumentException;

class Checkout
{
    public function execute(array $args): void
    {
        if ($args === []) {
            echo 'specify branch name.';
            return;
        }

        $branch = str_replace(' ', '', $args[0]);

        if (!file_exists('dotgit/refs/heads/'.$branch)) {
            throw new InvalidArgumentException($branch.' doesn\'t exist.');
        }

        $fp = fopen('dotgit/HEAD', 'w');
        fwrite($fp, 'refs/heads/'.$branch);
        fclose($fp);
    }
}
