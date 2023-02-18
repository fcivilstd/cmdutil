<?php

namespace Util\Git\Object;

use InvalidArgumentException;

class Blob
{
    private string $head2 = '';
    private string $name = '';
    private string $content = '';

    public function __construct(string $filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException($filename.' doesn\'t exist.');
        }

        $this->content = file_get_contents($filename);
        $hash = sha1('blob '.(string)filesize($filename).'\0'.$this->content);
        $this->head2 = substr($hash, 0, 2);
        $this->name = substr($hash, 2);
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
    
    public function content(): string
    {
        assert($this->content !== '');

        return $this->content;
    }
}
