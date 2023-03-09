<?php

namespace Util\Git\Object;

abstract class GitObject
{
    protected string $hash = '';
    protected string $head2 = '';
    protected string $name = '';

    public function hash(): string
    {
        assert($this->hash !== '' && strlen($this->hash) === 40);

        return $this->hash;
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

    public function exists(): bool
    {
        if (!file_exists('dotgit/objects/'.$this->head2())) return false;
        if (file_exists('dotgit/objects/'.$this->head2().'/'.$this->name())) return true;
        return false;
    }
}
