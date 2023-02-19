<?php

namespace Util\Git;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class WorkingTree
{
    // ディレクトリ構成をもとに構築したグラフ(隣接リスト形式)
    private array $graph = [];

    private array $gitignore = [
        '/.git/',
        '/dotgit/',
        '/vendor/',
    ];

    public function __construct()
    {
        if (!file_exists('dotgit')) {
            throw new InvalidArgumentException('dotgit directory doesn\'t exist.');
        }

        $iterator = new RecursiveDirectoryIterator(getcwd());
        $iterator = new RecursiveIteratorIterator($iterator);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $filename = str_replace(getcwd().'\\', '', $fileinfo->getPathname());
                $filename = str_replace('\\', '/', $filename);
                $this->addPath($filename);
            }
        }
    }

    public function graph(): array
    {
        return $this->graph;
    }

    private function addPath(string $filename): void
    {
        $explodedFilename = explode('/', $filename);
        $currentPath = '';
        for ($i = 0; $i < count($explodedFilename); $i++) {
            $currentPath .= '/'.$explodedFilename[$i];

            if (!$this->isValidPath($currentPath)) continue;

            if (!isset($this->graph[$currentPath])) {
                $this->graph[$currentPath] = [];
            }
            
            if (count($explodedFilename) === $i + 1) {
                $this->graph[$currentPath] = [];
            } else if (!in_array($explodedFilename[$i + 1], $this->graph[$currentPath])) {
                $this->graph[$currentPath][] = $explodedFilename[$i + 1];
            }
        }
    }

    private function isValidPath(string $path): bool
    {
        foreach ($this->gitignore as $part) {
            $part = preg_replace('/\/\z/u', '', $part);
            $part = str_replace('/', '\\/', $part);
            if (preg_match('/\A'.$part.'/u', $path)) {
                return false;
            }
        }

        return true;
    }
}
