<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use RuntimeException;
use Util\Lib\DataStructure\Queue;
use Util\Git\Object\Commit;
use Util\Git\Operation\Model\Diff;

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

        if (!$commonCommitId === '') {
            throw new RuntimeException('common commit was not fountd.');
        }

        $diff = new Diff($this->filename('60809c8a496dee0ffbed31bc308b8ae60ad13cc5'), $this->filename('5fa063b101711baf004499b35bc0c35d52ce22e4'));
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
        $baseCommits = [];
        $targetCommits = [];
        $baseCommitsQueue = new Queue();
        $targetCommitsQueue = new Queue();

        $baseCommits[$this->lastmod($baseCommitId)] = $baseCommitId;
        $baseCommitsQueue->push($baseCommitId);
        while ($baseCommitsQueue->size()) {
            $commit = $baseCommitsQueue->front(); $baseCommitsQueue->pop();
            $baseCommits[$this->lastmod($commit)] = $commit;

            $parents = Commit::restore($commit)->parent();
            foreach ($parents as $parent) {
                if ($parent === '') continue;
                $baseCommitsQueue->push($parent);
            }
        }

        $targetCommits[$this->lastmod($targetCommitId)] = $targetCommitId;
        $targetCommitsQueue->push($targetCommitId);
        while ($targetCommitsQueue->size()) {
            $commit = $targetCommitsQueue->front(); $targetCommitsQueue->pop();
            $targetCommits[$this->lastmod($commit)] = $commit;

            $parents = Commit::restore($commit)->parent();
            foreach ($parents as $parent) {
                if ($parent === '') continue;
                $targetCommitsQueue->push($parent);
            }
        }
        $targetCommits = array_flip($targetCommits);

        krsort($baseCommits);
        foreach ($baseCommits as $_ => $commit) {
            if (array_key_exists($commit, $targetCommits)) {
                return $commit;
            }
        }

        return '';
    }

    private function lastmod(string $commitId): int
    {
        $head2 = substr($commitId, 0, 2);
        $name = substr($commitId, 2);
        if (!file_exists('dotgit/objects/'.$head2) || !file_exists('dotgit/objects/'.$head2.'/'.$name)) {
            throw new InvalidArgumentException($commitId.' doesn\'t exist.');
        }

        return filemtime('dotgit/objects/'.$head2.'/'.$name);
    }

    private function filename(string $commitId): string
    {
        $head2 = substr($commitId, 0, 2);
        $name = substr($commitId, 2);
        return 'dotgit/objects/'.$head2.'/'.$name;
    }
}
