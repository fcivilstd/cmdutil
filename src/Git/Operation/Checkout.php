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

        $files = json_decode(file_get_contents('dotgit/index'), true)[$branch];
        foreach ($files as $filename => $hash) {
            $head2 = substr($hash, 0, 2);
            $name = substr($hash, 2);

            assert(file_exists('dotgit/objects/'.$head2.'/'.$name) !== false);
            copy('dotgit/objects/'.$head2.'/'.$name, $filename);
        }
    }
}
