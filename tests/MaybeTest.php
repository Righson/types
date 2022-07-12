<?php

use PHPUnit\Framework\TestCase;
use Righson\Types\Maybe;

class MaybeTest extends TestCase {
    public function testBind() {
        $div = function($lst) {
            $left  = $lst[0];
            $right = $lst[1];

            if ($right == 0) return null;
            return $left / $right;
        };

        $res = (new Maybe([2, 0]))->bind($div)->unwrap();
        $this->assertEmpty($res);

        $res = (new Maybe(3))->bind(fn($x) => $x +1)->bind(fn($x) => "$x!")->unwrap();
        $this->assertEquals("4!", $res);
    }
}
