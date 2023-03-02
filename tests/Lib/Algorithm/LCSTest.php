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

    public function testLcs(): void
    {
        $this->assertSame('abc', (new LCS('abc', 'abcdefg'))->lcs());
        $this->assertSame(3, (new LCS('abc', 'abcdefg'))->length());
        $this->assertSame('adef', (new LCS('adbecf', 'abcdefg'))->lcs());
        $this->assertSame(4, (new LCS('adbecf', 'abcdefg'))->length());
    }
}