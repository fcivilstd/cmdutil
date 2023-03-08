<?php

namespace Util\Git\Operation;

use InvalidArgumentException;
use Util\Git\Index;
use Util\Git\Object\Blob;
use Util\Git\Object\Tree;

class Commit
{
    public function execute(array $args): void
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
            $root->addTree($this->assembleTree($index, $path), $_path);
        }

        $root->save();

        $commit = \Util\Git\Object\Commit::new(
                    $root->head2().$root->name(),
                    'me',
                    'me',
                    $message);
        $commit->save();
    }

    private function assembleTree(Index $index, string $path): Tree
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
            $tree->addTree($this->assembleTree($index, $path.'/'.$target), $_path.'/'.$target);
        }

        $tree->save();

        return $tree;
    }
}
