<?php

namespace Util\Git;

use InvalidArgumentException;
use RuntimeException;

class Index
{
    // ディレクトリ構成をもとに構築したグラフ(隣接リスト形式)
    private array $graph = [];

    private string $branch = '';
    private array $content = [];

    public function __construct()
    {
        assert(file_exists('dotgit/index') !== false);
        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');

        $this->branch = preg_replace('/\A.*\//u', '', $HEAD);
        $this->content = json_decode(file_get_contents('dotgit/index'), true);

        if (!isset($this->content[$this->branch])) {
            $this->content[$this->branch] = [];
        }

        foreach ($this->content[$this->branch] as $filename => $_) {
            $this->addPath($filename);
        }
    }

    public function update(string $filename, string $hash): void
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException($filename.' doesn\'t exist.');
        } else if (is_dir($filename)) {
            throw new InvalidArgumentException($filename.' is directory name.');
        }

        $this->content[$this->branch()][$filename] = $hash;

        $this->addPath($filename);
    }

    public function remove(string $filename): void
    {
        if (isset($this->content[$this->branch()][$filename])) {
            unset($this->content[$this->branch()][$filename]);
        }
    }

    public function branch(): string
    {
        assert($this->branch !== '');

        return $this->branch;
    }

    public function content(): array
    {
        return $this->content;
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

    public function copyTo(string $targetBranch): void
    {
        $this->content[$targetBranch] = $this->content[$this->branch()];
    }

    public function save(): void
    {
        $fp = fopen('dotgit/index', 'w');
        fwrite($fp, json_encode($this->content()));
        fclose($fp);
    }
}
