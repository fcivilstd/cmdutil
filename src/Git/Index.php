<?php

namespace Util\Git;

use InvalidArgumentException;
use RuntimeException;

class Index
{
    // ディレクトリ構成をもとに構築したグラフ(隣接リスト形式)
    private array $graph = [];

    private array $content = [];

    public function __construct()
    {
        if (!file_exists('dotgit/index')) {
            throw new RuntimeException('index doesn\'t exist.');
        }

        $this->content = json_decode(file_get_contents('dotgit/index'), true);

        foreach ($this->content as $filename => $_) {
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

        $this->content[$filename] = $hash;

        $this->addPath($filename);
    }

    public function remove(string $filename): void
    {
        if (isset($this->content[$filename])) {
            unset($this->content[$filename]);
        }
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

    public function save(): void
    {
        $fp = fopen('dotgit/index', 'w');
        fwrite($fp, json_encode($this->content()));
        fclose($fp);
    }
}
