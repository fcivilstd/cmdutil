<?php

namespace Util\Git\Object;

use InvalidArgumentException;

class Commit
{
    private string $head2 = '';
    private string $name = '';

    private string $tree = '';
    private string $parent = '';
    private string $author = '';
    private string $committer = '';
    private string $message = '';
    private array $content = [];

    public function __construct(
        string $tree,
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

        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');

        $parent = '';
        if (file_exists('dotgit/'.$HEAD)) {
            $parent = file_get_contents('dotgit/'.$HEAD);
        }

        $this->content = [
            'tree' => $tree,
            'parent' => $parent,
            'author' => $author,
            'committer' => $committer,
            'message' => $message,
        ];
    }

    public function head2(): string
    {
        return $this->head2;
    }

    public function name(): string
    {
        return $this->name;
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

    public function save(): void
    {
        $content = json_encode($this->content());

        $hash = sha1('commit '.(string)strlen($content).'\0'.$content);
        $this->head2 = substr($hash, 0, 2);
        $this->name = substr($hash, 2);

        if (!file_exists('dotgit/objects/'.$this->head2())) {
            mkdir('dotgit/objects/'.$this->head2());
        }

        $fp = fopen('dotgit/objects/'.$this->head2().'/'.$this->name(), 'w');
        fwrite($fp, $content);
        fclose($fp);

        assert(file_exists('dotgit/HEAD') !== false);
        $HEAD = file_get_contents('dotgit/HEAD');
        $fp = fopen('dotgit/'.$HEAD, 'w');
        fwrite($fp, $this->head2().$this->name());
        fclose($fp);
    }
}
