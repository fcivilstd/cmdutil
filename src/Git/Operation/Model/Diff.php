<?php

namespace Util\Git\Operation\Model;

use InvalidArgumentException;
use Util\Lib\Algorithm\LCS;

class Diff
{
    private array $source = [];
    private array $target = [];
    private LCS $lcs;
    private array $deleted = [];
    private array $added = [];

    private function __construct(array $source, array $target)
    {
        $this->source = $source;
        $this->target = $target;

        $this->solve();
    }

    public static function fromArray(array $source, array $target): self
    {
        return new self($source, $target);
    }

    public static function fromFilename(string $file1, string $file2): self
    {
        if (!file_exists($file1)) {
            throw new InvalidArgumentException($file1.' doesn\'t exist.');
        }

        if (!file_exists($file2)) {
            throw new InvalidArgumentException($file2.' doesn\'t exist.');
        }

        $source = [];
        $target = [];

        $fp1 = fopen($file1, 'r');
        assert($fp1 !== false);
        while ($line = fgets($fp1)) {
            $source[] = rtrim($line);
        }
        fclose($fp1);

        $fp2 = fopen($file2, 'r');
        assert($fp2 !== false);
        while ($line = fgets($fp2)) {
            $target[] = rtrim($line);
        }
        fclose($fp2);

        return new self($source, $target);
    }

    public function solve(): void
    {
        $this->lcs = new LCS($this->source, $this->target);

        $ptr = 0;
        foreach ($this->source as $lineNo => $line) {
            if ($ptr < count($this->lcs->lcs()) && $line === $this->lcs->lcs()[$ptr]) {
                $ptr++;
                continue;
            }
            $this->deleted[$lineNo] = $line;
        }

        $ptr = 0;
        foreach ($this->target as $lineNo => $line) {
            if ($ptr < $this->lcs->length() && $line === $this->lcs->lcs()[$ptr]) {
                $ptr++;
                continue;
            }
            if ($ptr === 0) {
                if (!isset($this->added[0])) $this->added[0] = [];
                $this->added[0][] = $line;
                continue;
            }
            if (!isset($this->added[$this->lcs->lcsSourceKey()[$ptr - 1]])) $this->added[$this->lcs->lcsSourceKey()[$ptr - 1]] = [];
            $this->added[$this->lcs->lcsSourceKey()[$ptr - 1]][] = $line;
        }
    }

    public function deleted(): array
    {
        return $this->deleted;
    }

    public function added(): array
    {
        return $this->added;
    }

    public function lcs(): LCS
    {
        return $this->lcs;
    }
}
