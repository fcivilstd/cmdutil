<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use Util\Git\Index;
use Util\Git\Object\Blob;

class Add
{
    public static function execute(array $args): void
    {
        $filename = $args[0];

        if (!file_exists($filename)) {
            throw new InvalidArgumentException($filename.' doesn\'t exist.');
        }

        $blob = Blob::fromFilename($filename);
        $blob->save();

        $index = new Index();
        $index->update($filename, $blob->hash());
        $index->save();
    }
}
