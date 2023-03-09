<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use Util\Git\Index;
use Util\Git\Object\Blob;
use Util\Git\Object\Tree;

class Commit
{
    public static function execute(array $args): void
    {
        $message = '';
        foreach ($args as $arg) {
            if (preg_match('/\A--message=.+/u', $arg) === 1) {
                $message = str_replace('--message=', '', $arg);
                break;
            }
        }

        if ($message === '') {
            throw new InvalidArgumentException('input message.');
        }

        $index = new Index();
        $root = self::assembleRootTree($index);
        $root->save();

        $commit = \Util\Git\Object\Commit::readHEAD(
                    $root->hash(),
                    'me',
                    'me',
                    $message);
        $commit->save();
    }

    public static function assembleRootTree(Index $index): Tree
    {
        $root = new Tree();
        foreach ($index->graph() as $path => $target) {
            $_path = preg_replace('/\A\//u', '', $path);
            if (count(explode('/', $_path)) !== 1) continue;
            // ファイル
            if ($target === []) {
                $root->addBlob(Blob::fromFilename($_path), $_path);
                continue;
            }
            // ディレクトリ
            $root->addTree(self::assembleTree($index, $path), $_path);
        }
        return $root;
    }

    private static function assembleTree(Index $index, string $path): Tree
    {
        $tree = new Tree();
        $_path = preg_replace('/\A\//u', '', $path);
        foreach ($index->graph()[$path] as $target) {
            $_path = preg_replace('/\A\//u', '', $path);
            // ファイル
            if ($index->graph()[$path.'/'.$target] === []) {
                $tree->addBlob(Blob::fromFilename($_path.'/'.$target), $_path.'/'.$target);
                continue;
            }
            // ディレクトリ
            $tree->addTree(self::assembleTree($index, $path.'/'.$target), $_path.'/'.$target);
        }

        $tree->save();

        return $tree;
    }
}
