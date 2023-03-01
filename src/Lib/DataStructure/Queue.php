<?php

namespace Util\Lib\DataStructure;

class Queue
{
    private $stacks = [[], []];

    public function push(mixed $data): void
    {
        $this->stacks[0][] = $data;
    }

    public function front(): mixed
    {
        if ($this->stacks[1] !== []) {
            return $this->stacks[1][count($this->stacks)];
        }

        if ($this->stacks[0] !== []) {
            return $this->stacks[0][0];
        }
        
        return null;
    }

    public function pop(): mixed
    {
        if ($this->stacks[1] === []) {
            $this->stacks[1] = array_reverse($this->stacks[0]);
            $this->stacks[0] = [];
        }

        return array_pop($this->stacks[1]);
    }

    public function size(): int
    {
        return count($this->stacks[0]) + count($this->stacks[1]);
    }
}
