<?php

namespace Util\Git\Operation\Model;

use InvalidArgumentException;

class RootTree
{
    private string $id = '';
    private array $content = [];

    public function __construct(string $id)
    {
        $this->id = $id;
        
        $this->solve();
    }

    private function solve(): void
    {
        if (!file_exists($this->filename($this->id))) {
            throw new InvalidArgumentException($this->id. ' doesn\'t exist.');
        }
        
        $this->findFiles($this->id);
    }

    private function findFiles(string $treeId): void
    {
        $content = json_decode(file_get_contents($this->filename($treeId)), true);
        foreach ($content['blob'] as $filename => $blobId) {
            $this->content[$filename] = $blobId;
        }
        foreach ($content['tree'] as $dirname => $targetTreeId) {
            $this->findFiles($targetTreeId);
        }
    }

    private function filename(string $commitId): string
    {
        $head2 = substr($commitId, 0, 2);
        $name = substr($commitId, 2);
        return 'dotgit/objects/'.$head2.'/'.$name;
    }

    public function content(): array
    {
        return $this->content;
    }
}
