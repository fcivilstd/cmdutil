<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use Util\Git\Object\Commit;

class Merge
{
    public function execute(array $args): void
    {
        if ($args === []) {
            echo 'specify branch name'.PHP_EOL;
            return;
        }

        $branch = str_replace(' ', '', $args[0]);

        $baseCommitId = $this->findBaseCommitId();
        $targetCommitId = $this->findTargetCommitId($branch);
        $commonCommitId = $this->findCommonCommitId($baseCommitId, $targetCommitId);

        var_dump($commonCommitId);
    }

    private function findBaseCommitId(): string
    {
        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');
        assert(file_exists('dotgit/'.$HEAD) !== false);
        return file_get_contents('dotgit/'.$HEAD);
    }

    private function findTargetCommitId(string $branch): string
    {
        if (!file_exists('dotgit/refs/heads/'.$branch)) {
            throw new InvalidArgumentException($branch.' doesn\'t exist.');
        }

        return file_get_contents('dotgit/refs/heads/'.$branch);
    }

    private function findCommonCommitId(string $baseCommitId, string $targetCommitId): string
    {
        return '';
    }
}
