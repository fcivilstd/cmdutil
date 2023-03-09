<?php

namespace Util\Git\Operation;

use Util\Git\Index;

class Remove
{
    public static function execute(array $args): void
    {
        $filename = $args[0];

        $index = new Index();
        $index->remove($filename);
        $index->save();
    }
}
