<?php

namespace Test\Lib\DataStructure;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Util\Lib\Algorithm\LCS;

final class LCSTest extends TestCase
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
            [[['a', 'b', 'hoge', 'e'], [0, 1, 3, 5]], new LCS(['a', 'b', 'z', 'hoge', 'y', 'e', 'piyo'], ['a', 'b', 'c', 'hoge', 'd', 'e', 'f', 'huga', 'g'])],
        ];
    }

    /**
     * @dataProvider provide
     */
    public function testLCS(array $expected, LCS $lcs): void
    {
        var_dump($lcs->lcsSourceKey());

        $this->assertSame($expected[0], $lcs->lcs());
        $this->assertSame(count($expected[0]), $lcs->length());
        $this->assertSame($expected[1], $lcs->lcsSourceKey());
    }
}