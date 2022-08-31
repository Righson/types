<?php


use Righson\Types\ValidStringType;
use PHPUnit\Framework\TestCase;

class ValidStringTypeTest extends TestCase
{
    public function testValidateOk()
    {
        $vString = new ValidStringType('ABCD', fn($x) => $x == 'ABCD');
        $this->assertTrue($vString->validate());
    }

    public function testValidateFail()
    {
        $vString = new ValidStringType('ABCD', fn($x) => $x == 'XYZ');
        $this->assertFalse($vString->validate());
    }
}
