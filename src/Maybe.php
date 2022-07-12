<?php

namespace Righson\Types;

class Maybe {
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function bind(callable $fn): Maybe {
        if (empty($this->value)) {
            return Maybe(null);
        }

        $res = $fn($this->value);
        return new Maybe($res);
    }

    public function unwrap() {
        return $this->value;
    }
}
