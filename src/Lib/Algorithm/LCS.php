<?php

namespace Util\Lib\Algorithm;

class LCS
{
    private array $dp = [];
    private string $s = '';
    private string $t = '';
    private int $sLength = 0;
    private int $tLength = 0;
    private string $lcs = '';
    private int $lcsLength = 0;

    public function __construct(string $s, string $t)
    {
        $this->s = $s;
        $this->sLength = mb_strlen($s);

        $this->t = $t;
        $this->tLength = mb_strlen($t);

        $this->dp = array_fill(0, $this->sLength + 1, array_fill(0, $this->tLength + 1, 0));
        $this->solve();
    }

    private function solve(): void
    {
        for ($i = 0; $i < $this->sLength; $i++) {
            for ($j = 0; $j < $this->tLength; $j++) {
                if ($this->s[$i] === $this->t[$j]) {
                    $this->dp[$i + 1][$j + 1] = $this->dp[$i][$j] + 1;
                } else {
                    $this->dp[$i + 1][$j + 1] = max($this->dp[$i][$j + 1], $this->dp[$i + 1][$j]);
                }
            }
        }

        $i = $this->sLength; $j = $this->tLength;
        while ($i > 0 && $j > 0) {
            if ($this->s[$i - 1] === $this->t[$j - 1]) {
                $this->lcs .= $this->s[$i - 1];
                $i--; $j--;
            } else if ($this->dp[$i][$j] === $this->dp[$i - 1][$j]) {
                $i--;
            } else if ($this->dp[$i][$j] === $this->dp[$i][$j - 1]) {
                $j--;
            }
        }

        $this->lcs = strrev($this->lcs);
        $this->lcsLength = mb_strlen($this->lcs);
    }

    public function length(): int
    {
        return $this->lcsLength;
    }

    public function lcs(): string
    {
        return $this->lcs;
    }
}
