<?php


use Righson\Types\Pair;
use PHPUnit\Framework\TestCase;

class PairTest extends TestCase
{
    public function testMakeFromArray()
    {
        $pair = Pair::makeFromArray([1,2]);
        $this->assertEquals(1, $pair->get()[0]);
        $this->assertEquals(2, $pair->get()[1]);

        $pair = Pair::makeFromArray([1,2,3]);
        $this->assertEquals(1, $pair->get()[0]);
        $this->assertEquals(2, $pair->get()[1]);
    }

    public function testIfLeft() {
        $pair = new Pair(false, true);
        $test = $pair->ifFirst(function($x) {return 2;}, function ($x) {return 3;});

        $this->assertEquals(3, $test);
    }
}
