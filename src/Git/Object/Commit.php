<?php

namespace Util\Git\Object;

use InvalidArgumentException;
use Util\Git\Object\GitObject;

class Commit extends GitObject
{
    private string $tree = '';
    private array $parent = [];
    private string $author = '';
    private string $committer = '';
    private string $message = '';
    private array $content = [];

    private function __construct(
        string $tree,
        array $parent,
        string $author,
        string $committer,
        string $message
    )
    {
        $this->tree = $tree;
        $this->parent = $parent;
        $this->author = $author;
        $this->committer = $committer;
        if ($message === '') {
            throw new InvalidArgumentException('no message.');
        }
        $this->message = $message;
        $this->content = [
            'tree' => $this->tree,
            'parent' => $this->parent,
            'author' => $this->author,
            'committer' => $this->committer,
            'message' => $this->message,
        ];
    }

    public static function readHEAD(
        string $tree,
        string $author,
        string $committer,
        string $message
    ): self
    {
        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');

        $parent = '';
        if (file_exists('dotgit/'.$HEAD)) {
            $parent = file_get_contents('dotgit/'.$HEAD);
        }

        return new self($tree, [$parent], $author, $committer, $message);
    }

    public static function withParents(
        string $tree,
        array $parents,
        string $author,
        string $committer,
        string $message
    ): self
    {
        return new self($tree, $parents, $author, $committer, $message);
    }

    public static function restore(string $commitId): self
    {
        $head2 = substr($commitId, 0, 2);
        $name = substr($commitId, 2);
        if (!file_exists('dotgit/objects/'.$head2.'/'.$name)) {
            throw new InvalidArgumentException($commitId.' doesn\'t exist');
        }

        $content = json_decode(file_get_contents('dotgit/objects/'.$head2.'/'.$name), true);
        return new self(
            $content['tree'],
            $content['parent'],
            $content['author'],
            $content['committer'],
            $content['message']);
    }

    public function tree(): string
    {
        assert($this->tree !== '');

        return $this->tree;
    }

    public function parent(): array
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

    public function save(): void
    {
        $content = json_encode($this->content());

        $this->hash = sha1('commit '.(string)strlen($content).'\0'.$content);
        $this->head2 = substr($this->hash, 0, 2);
        $this->name = substr($this->hash, 2);

        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');
        $fp = fopen('dotgit/'.$HEAD, 'w');
        fwrite($fp, $this->hash());
        fclose($fp);

        if ($this->exists()) return;

        if (!file_exists('dotgit/objects/'.$this->head2())) {
            mkdir('dotgit/objects/'.$this->head2());
        }

        $fp = fopen('dotgit/objects/'.$this->head2().'/'.$this->name(), 'w');
        fwrite($fp, $content);
        fclose($fp);
    }
}
