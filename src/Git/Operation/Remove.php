<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use Util\Git\Index;

class Remove
{
    public function execute(array $args): void
    {
        $filename = $args[0];

        $index = new Index();
        $index->remove($filename);
        $fp = fopen('dotgit/index', 'w');
        fwrite($fp, json_encode($index->content()));
        fclose($fp);
    }
}
