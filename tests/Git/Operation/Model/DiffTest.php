<?php

namespace Test\Git\Operation\Model;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Util\Git\Operation\Model\Diff;

final class DiffTest extends TestCase
{
    public function setUp(): void
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new RuntimeException($errstr . ' on line ' . $errline . ' in file ' . $errfile);
        });
    }
        
    public function tearDown(): void
    {
        restore_error_handler();
    }

    public static function provide(): array
    {
        return [
            [[[1 => 'bb', 2 => 'c', 5 => 'f', 7 => 'h'], [6 => ['iiiii']]], Diff::fromFilename(__DIR__.'./test_case/a1.txt', __DIR__.'./test_case/b1.txt')],
        ];
    }

    /**
     * @dataProvider provide
     */
    public function testDiff(array $expected, Diff $diff): void
    {
        $this->assertSame($expected[0], $diff->deleted());
        $this->assertSame($expected[1], $diff->added());
    }
}