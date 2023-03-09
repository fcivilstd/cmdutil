<?php

namespace Util\Git\Object;

use InvalidArgumentException;
use Util\Git\Object\GitObject;

class Blob extends GitObject
{
    private string $content = '';

    private function __construct(string $content)
    {
        $this->content = $content;
        $this->hash = sha1('blob '.(string)strlen($content).'\0'.$content);
        $this->head2 = substr($this->hash, 0, 2);
        $this->name = substr($this->hash, 2);
    }

    public static function fromFilename(string $filename): self
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException($filename.' doesn\'t exist.');
        }

        $content = file_get_contents($filename);
        return new self($content);
    }

    public static function fromContent(string $content): self
    {
        return new self($content);
    }
    
    public function content(): string
    {
        return $this->content;
    }

    public function save(): void
    {
        if ($this->exists()) return;

        if (!file_exists('dotgit/objects/'.$this->head2())) {
            mkdir('dotgit/objects/'.$this->head2());
        }

        $fp = fopen('dotgit/objects/'.$this->head2().'/'.$this->name(), 'w');
        fwrite($fp, $this->content());
        fclose($fp);  
    }
}
