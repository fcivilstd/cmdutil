<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use RuntimeException;
use Util\Git\Index;
use Util\Lib\DataStructure\Queue;
use Util\Git\Object\Commit;
use Util\Git\Operation\Model\Diff;
use Util\Git\Operation\Model\RootTree;
use Util\Git\Object\Blob;

class Merge
{
    public static function execute(array $args): void
    {
        if ($args === []) {
            throw new InvalidArgumentException('specify branch name');
        }

        $branch = str_replace(' ', '', $args[0]);

        $baseCommitId = self::findBaseCommitId();
        $targetCommitId = self::findTargetCommitId($branch);
        $commonCommitId = self::findCommonCommitId($baseCommitId, $targetCommitId);

        if (!$commonCommitId === '') {
            throw new RuntimeException('common commit was not fountd.');
        }

        $index = new Index();

        self::mergeDiff($baseCommitId, $targetCommitId, $commonCommitId, $index);

        $root = \Util\Git\Operation\Commit::assembleRootTree($index);
        $root->save();

        $commit = \Util\Git\Object\Commit::withParents(
                    $root->hash(),
                    [$baseCommitId, $targetCommitId],
                    'me',
                    'me',
                    'merge commit');
        $commit->save();

        Checkout::executeWithBranch($index->branch());
    }

    private static function mergeDiff(string $baseCommitId, string $targetCommitId, string $commonCommitId, Index $index): void
    {
        $baseRootTree = new RootTree(self::rootTree($baseCommitId));
        $targetRootTree = new RootTree(self::rootTree($targetCommitId));
        $commonRootTree = new RootTree(self::rootTree($commonCommitId));

        foreach ($commonRootTree->content() as $filename => $hash) {
            if (!isset($baseRootTree->content()[$filename]) || !isset($targetRootTree->content()[$filename])) {
                $index->remove($filename);
            }
            $baseDeleted = [];
            $baseAdded = [];
            if (isset($baseRootTree->content()[$filename]) && $baseRootTree->content()[$filename] !== $hash) {
                $diff = Diff::fromFilename(self::filename($hash), self::filename($baseRootTree->content()[$filename]));
                $baseDeleted = $diff->deleted();
                $baseAdded = $diff->added();
            }
            $targetDeleted = [];
            $targetAdded = [];
            if (isset($targetRootTree->content()[$filename]) && $targetRootTree->content()[$filename] !== $hash) {
                $diff = Diff::fromFilename(self::filename($hash), self::filename($targetRootTree->content()[$filename]));
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

            $blob = self::generageMergedBlob($hash, $mergedDeleted, $mergedAdded);
            $blob->save();

            $index->update($filename, $blob->hash());
        }

        foreach ($baseRootTree->content() as $filename => $hash) {
            if (!isset($targetRootTree->content()[$filename]) || isset($commonRootTree->content()[$filename])) continue;
            $baseDeleted = [];
            $baseAdded = [];
            if (isset($baseRootTree->content()[$filename]) && $baseRootTree->content()[$filename] !== $hash) {
                $diff = Diff::fromFilename(self::filename($hash), self::filename($baseRootTree->content()[$filename]));
                $baseDeleted = $diff->deleted();
                $baseAdded = $diff->added();
            }
            $targetDeleted = [];
            $targetAdded = [];
            if (isset($targetRootTree->content()[$filename]) && $targetRootTree->content()[$filename] !== $hash) {
                $diff = Diff::fromFilename(self::filename($hash), self::filename($targetRootTree->content()[$filename]));
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

            $blob = self::generageMergedBlob($hash, $mergedDeleted, $mergedAdded);
            $blob->save();

            $index->update($filename, $blob->hash());
        }

        $index->save();
    }

    private static function generageMergedBlob(string $commonBlobId, array $mergedDeleted, array $mergedAdded): Blob
    {
        $fp = fopen(self::filename($commonBlobId), 'r');

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

    private static function findBaseCommitId(): string
    {
        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');
        assert(file_exists('dotgit/'.$HEAD) !== false);
        return file_get_contents('dotgit/'.$HEAD);
    }

    private static function findTargetCommitId(string $branch): string
    {
        if (!file_exists('dotgit/refs/heads/'.$branch)) {
            throw new InvalidArgumentException($branch.' doesn\'t exist.');
        }

        return file_get_contents('dotgit/refs/heads/'.$branch);
    }

    private static function findCommonCommitId(string $baseCommitId, string $targetCommitId): string
    {
        $baseCommits = [];
        $targetCommits = [];
        $baseCommitsQueue = new Queue();
        $targetCommitsQueue = new Queue();

        $baseCommits[self::lastmod($baseCommitId)] = $baseCommitId;
        $baseCommitsQueue->push($baseCommitId);
        while ($baseCommitsQueue->size()) {
            $commit = $baseCommitsQueue->front(); $baseCommitsQueue->pop();
            $baseCommits[self::lastmod($commit)] = $commit;

            $parents = Commit::restore($commit)->parent();
            foreach ($parents as $parent) {
                if ($parent === '') continue;
                $baseCommitsQueue->push($parent);
            }
        }

        $targetCommits[self::lastmod($targetCommitId)] = $targetCommitId;
        $targetCommitsQueue->push($targetCommitId);
        while ($targetCommitsQueue->size()) {
            $commit = $targetCommitsQueue->front(); $targetCommitsQueue->pop();
            $targetCommits[self::lastmod($commit)] = $commit;

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

    private static function lastmod(string $commitId): int
    {
        $head2 = substr($commitId, 0, 2);
        $name = substr($commitId, 2);
        if (!file_exists('dotgit/objects/'.$head2) || !file_exists('dotgit/objects/'.$head2.'/'.$name)) {
            throw new InvalidArgumentException($commitId.' doesn\'t exist.');
        }

        return filemtime('dotgit/objects/'.$head2.'/'.$name);
    }

    private static function filename(string $objectId): string
    {
        $head2 = substr($objectId, 0, 2);
        $name = substr($objectId, 2);
        return 'dotgit/objects/'.$head2.'/'.$name;
    }

    private static function rootTree(string $commitId): string
    {
        if (!file_exists(self::filename($commitId))) {
            throw new InvalidArgumentException($commitId.' doesn\'t exist.');
        }

        return json_decode(file_get_contents(self::filename($commitId)), true)['tree'];
    }
}
