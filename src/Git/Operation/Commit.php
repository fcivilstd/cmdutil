<?php

namespace Util\Git\Operation;

use Util\Git\Index;
use Util\Git\WorkingTree;

class Commit
{
    public function execute(array $args): void
    {
        $index = new Index();
        $workingTree = new WorkingTree();
    }
}
