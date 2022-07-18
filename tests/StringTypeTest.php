<?php

use PHPUnit\Framework\TestCase;
use Righson\Types\StringType;

class StringTypeTest extends TestCase {
    public function testArrayAccesImpl() {
        // Exists
        $strT = new StringType('abcdefgh');
        $this->assertTrue(isset($strT['ab']));

        // Get
        $this->assertEquals("ab", $strT[":2"]);
        $this->assertEquals("bc", $strT["1:3"]);
        $this->assertEquals("bc", $strT[[1,3]]);

        // Set
        $strT[0] = "ZXC_";
        $this->assertEquals("ZXC_bcdefgh", $strT->toString());
        $strT[":4"] = "a";
        $this->assertEquals("abcdefgh", $strT->toString());

        // UnSet
        unset($strT[0]);
        $this->assertEquals("bcdefgh", $strT->toString());
    }

    public function testFormat() {
        $strT = new StringType('abcdefg');
        $this->assertEquals('ab.cd.efg', $strT->format('2.2.1', false));
        $this->assertEquals('ab.cd.e', $strT->format('2.2.1'));
    }

    public function testTake() {
        $strT = new StringType('abcdefg');
        $this->assertEquals('ab', $strT->take(2));
        $this->assertEquals('cd', $strT->take(2));
    }

    public function testLenght() {
        $strT = new StringType('abcdefg');
        $this->assertEquals(7, $strT->length());
    }

    public function testCount() {
        $strT = new StringType('abcdefga');
        $this->assertEquals(2, $strT->count('a'));
    }

    public function testJoin() {
        $strT = new StringType('abc');
        $strT->join('def');
        $this->assertEquals('abcdef', $strT->toString());
    }

    public function testJoinSame() {
        $strT = new StringType('abc');
        $strT->join(new StringType('def'));
        $this->assertEquals('abcdef', $strT->toString());
    }

    public function testApply() {
        $strT = new StringType('abcdefg');
        $strT->apply(fn($x) => strtoupper($x));
        $this->assertEquals('ABCDEFG', $strT->toString());
    }
}
