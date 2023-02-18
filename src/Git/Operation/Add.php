<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use Util\Git\Object\Blob;

class Add
{
    public function execute(array $args): void
    {
        if (!file_exists('dotgit')) {
            throw new InvalidArgumentException('dotgit directory doesn\'t exist.');
        }

        $filename = $args[0];

        if (!file_exists($filename)) {
            throw new InvalidArgumentException($filename.' doesn\'t exist.');
        }

        $blob = new Blob($filename);

        if (!file_exists('dotgit/objects/'.$blob->head2())) {
            mkdir('dotgit/objects/'.$blob->head2());
        }

        $fp = fopen('dotgit/objects/'.$blob->head2().'/'.$blob->name(), 'w');
        fwrite($fp, $blob->content());
        fclose($fp);
    }
}
