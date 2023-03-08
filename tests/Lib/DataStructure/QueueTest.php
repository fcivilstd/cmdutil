<?php

namespace Test\Lib\DataStructure;

use PHPUnit\Framework\TestCase;
use Util\Lib\DataStructure\Queue;

final class QueueTest extends TestCase
{
    public function testAll(): void
    {
        $q = new Queue();
        $q->push(3);
        $q->push(1);
        $q->push(4);
        $q->push(1);

        $this->assertSame(4, $q->size());
        $this->assertSame(3, $q->front());
        $this->assertSame(3, $q->pop());
        $this->assertSame(1, $q->pop());
        
        $q->push(5);
        $q->push(9);

        $this->assertSame(4, $q->front());
        $this->assertSame(4, $q->pop());
        $this->assertSame(3, $q->size());
        $this->assertSame(1, $q->pop());
        $this->assertSame(5, $q->pop());
        $this->assertSame(9, $q->pop());
        $this->assertSame(0, $q->size());
        $this->assertSame(null, $q->pop());
        $this->assertSame(null, $q->front());
    }

    public function testPerformance(): void
    {
        $q = new Queue();

        $start = microtime(true);
        for ($i = 0; $i < 1 << 20; $i++) $q->push($i);
        for ($i = 0; $i < 1 << 19; $i++) $q->pop($i);
        for ($i = 0; $i < 1 << 19; $i++) $q->push($i);
        while ($q->size()) $q->pop($i);
        $end = microtime(true);

        $this->assertTrue($end - $start < 1.);
    }
}