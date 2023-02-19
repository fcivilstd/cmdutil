<?php

namespace Util\Git\Object;

use InvalidArgumentException;

class Commit
{
    private string $tree = '';
    private string $parent = '';
    private string $author = '';
    private string $committer = '';
    private string $message = '';
    private array $content = [];

    public function __construct(
        string $tree,
        string $parent,
        string $author,
        string $committer,
        string $message
    )
    {
        $this->tree = $tree;
        if ($message === '') {
            throw new InvalidArgumentException('you can\'t commit without message.');
        }
        $this->message = $message;
        $this->content = [
            'tree' => $tree,
            'parent' => $parent,
            'author' => $author,
            'committer' => $committer,
            'message' => $message,
        ];
    }

    public function tree(): string
    {
        assert($this->tree !== '');

        return $this->tree;
    }

    public function parent(): string
    {
        return $this->parent;
    }

    public function author(): string
    {
        assert($this->author !== '');

        return $this->author;
    }

    public function committer(): string
    {
        assert($this->committer !== '');

        return $this->committer;
    }

    public function message(): string
    {
        assert($this->message !== '');

        return $this->message;
    }

    public function content(): array
    {
        assert($this->content !== []);

        return $this->content;
    }
}
