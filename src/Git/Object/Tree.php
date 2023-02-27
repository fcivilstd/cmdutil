<?php

namespace Util\Git\Object;

use Util\Git\Object\Blob;

class Tree
{
    private string $head2 = '';
    private string $name = '';
    private array $content = [];

    public function addTree(self $tree, string $dirname): void
    {
        $this->content[$dirname] = $tree->head2().$tree->name();
    }

    public function addBlob(Blob $blob, string $filename): void
    {
        $this->content[$filename] = $blob->head2().$blob->name();
    }

    public function head2(): string
    {
        assert($this->head2 !== '');
        
        return $this->head2;
    }

    public function name(): string
    {
        assert($this->name !== '');

        return $this->name;
    }

    public function content(): array
    {
        return $this->content;
    }

    public function save(): void
    {
        ksort($this->content);

        $content = json_encode($this->content());

        $hash = sha1('tree '.(string)strlen($content).'\0'.$content);
        $this->head2 = substr($hash, 0, 2);
        $this->name = substr($hash, 2);

        if (!file_exists('dotgit/objects/'.$this->head2())) {
            mkdir('dotgit/objects/'.$this->head2());
        }

        $fp = fopen('dotgit/objects/'.$this->head2().'/'.$this->name(), 'w');
        fwrite($fp, $content);
        fclose($fp);
    }
}
