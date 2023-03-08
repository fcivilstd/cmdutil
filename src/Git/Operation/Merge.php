<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use RuntimeException;
use Util\Lib\DataStructure\Queue;
use Util\Git\Object\Commit;
use Util\Git\Operation\Model\Diff;
use Util\Git\Operation\Model\RootTree;
use Util\Git\Object\Blob;

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

        $this->mergeDiff($baseCommitId, $targetCommitId, $commonCommitId);
    }

    private function mergeDiff(string $baseCommitId, string $targetCommitId, string $commonCommitId): void
    {
        $baseRootTree = new RootTree($this->rootTree($baseCommitId));
        $targetRootTree = new RootTree($this->rootTree($targetCommitId));
        $commonRootTree = new RootTree($this->rootTree($commonCommitId));

        $deletedFiles = [];
        $baseDeleted = [];
        $baseAdded = [];
        foreach ($commonRootTree->content() as $filename => $hash) {
            if (!isset($baseRootTree->content()[$filename])) {
                $deletedFiles[$filename] = $hash;
            }
            $baseDeleted = [];
            $baseAdded = [];
            if (isset($baseRootTree->content()[$filename]) && $baseRootTree->content()[$filename] !== $hash) {
                $diff = Diff::fromFilename($this->filename($hash), $this->filename($baseRootTree->content()[$filename]));
                $baseDeleted = $diff->deleted();
                $baseAdded = $diff->added();
            }
            $targetDeleted = [];
            $targetAdded = [];
            if (isset($targetRootTree->content()[$filename]) && $targetRootTree->content()[$filename] !== $hash) {
                $diff = Diff::fromFilename($this->filename($hash), $this->filename($targetRootTree->content()[$filename]));
                $targetDeleted = $diff->deleted();
                $targetAdded = $diff->added();
            }

            if (count(array_intersect_key($baseDeleted, $targetDeleted)) > 0 || count(array_intersect_key($baseAdded, $targetAdded)) > 0) {
                throw new RuntimeException('this merge conflicted.');
            }

            $mergedDeleted = $baseDeleted + $targetDeleted;
            $mergedAdded = $baseAdded + $targetAdded;
            ksort($mergedAdded);

            if (count($mergedDeleted) === 0 && count($mergedAdded) === 0) continue;

            $blob = $this->generageMergedBlob($hash, $mergedDeleted, $mergedAdded);
            $blob->save();

            var_dump($blob->content());
        }    
    }

    private function generageMergedBlob(string $commonBlobId, array $mergedDeleted, array $mergedAdded): Blob
    {
        $fp = fopen($this->filename($commonBlobId), 'r');

        $mergedContent = '';
        $lineNo = 0;
        assert($fp !== false);
        while ($line = fgets($fp)) {
            if (!isset($mergedDeleted[$lineNo])) {
                $mergedContent .= trim($line).PHP_EOL;
            }
            if (isset($mergedAdded[$lineNo])) {
                foreach ($mergedAdded[$lineNo] as $addLine) {
                    $mergedContent .= $addLine.PHP_EOL;
                }
            }
            $lineNo++;
        }
        fclose($fp);

        return Blob::fromContent($mergedContent);
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

    private function filename(string $objectId): string
    {
        $head2 = substr($objectId, 0, 2);
        $name = substr($objectId, 2);
        return 'dotgit/objects/'.$head2.'/'.$name;
    }

    private function rootTree(string $commitId): string
    {
        if (!file_exists($this->filename($commitId))) {
            throw new InvalidArgumentException($commitId.' doesn\'t exist.');
        }

        return json_decode(file_get_contents($this->filename($commitId)), true)['tree'];
    }
}
