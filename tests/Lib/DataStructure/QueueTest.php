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
        $q->push(5);

        $this->assertSame(5, $q->size());
        $this->assertSame(3, $q->front());
        $this->assertSame(3, $q->pop());
        $this->assertSame(1, $q->pop());
        $this->assertSame(4, $q->pop());
        $this->assertSame(2, $q->size());
    }

    public function testPerformance(): void
    {
        $q = new Queue();
        $times = 1001001;

        $start = microtime(true);
        while ($times--) {
            $q->push($times);
        }
        while ($q->size()) {
            $q->pop();
        }
        $end = microtime(true);

        $this->assertTrue($end - $start < 1.);
    }
}