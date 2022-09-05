<?php


use Righson\Types\ArrayType;
use PHPUnit\Framework\TestCase;

class ArrayTypeTest extends TestCase
{

    public function testTake()
    {
        $data = new ArrayType([1,2,3]);
        $this->assertEquals([1], $data->take(1));
    }
}
