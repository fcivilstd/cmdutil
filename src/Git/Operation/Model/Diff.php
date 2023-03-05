<?php

namespace Util\Git\Operation\Model;

use InvalidArgumentException;
use Util\Lib\Algorithm\LCS;

class Diff
{
    private string $file1 = '';
    private string $file2 = '';
    private array $lcs = [];
    private array $deleted = [];
    private array $added = [];

    public function __construct(string $file1, string $file2)
    {
        $this->file1 = $file1;
        $this->file2 = $file2;

        $this->solve();
    }

    public function solve(): void
    {
        if (!file_exists($this->file1)) {
            throw new InvalidArgumentException($this->file1.' doesn\'t exist.');
        }

        if (!file_exists($this->file2)) {
            throw new InvalidArgumentException($this->file2.' doesn\'t exist.');
        }

        $source = [];
        $target = [];

        $fp1 = fopen($this->file1, 'r');
        assert($fp1 !== false);
        while ($line = fgets($fp1)) {
            $source[] = rtrim($line);
        }
        fclose($fp1);

        $fp2 = fopen($this->file2, 'r');
        assert($fp2 !== false);
        while ($line = fgets($fp2)) {
            $target[] = rtrim($line);
        }
        fclose($fp2);

        $this->lcs = (new LCS($source, $target))->lcs();

        $offset = 0;
        foreach ($this->lcs as $lcsLine) {
            for ($i = $offset; $i < count($source); $i++) {
                if ($lcsLine === $source[$i]) {
                    $offset = $i + 1;
                    break;
                }
                $this->deleted[] = $source[$i];
            }
        }

        $offset = 0;
        foreach ($this->lcs as $lcsLine) {
            for ($i = $offset; $i < count($target); $i++) {
                if ($lcsLine === $target[$i]) {
                    $offset = $i + 1;
                    break;
                }
                $this->added[] = $target[$i];
            }
        }
    }
}
