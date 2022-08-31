<?php

namespace Righson\Types;

class ValidStringType extends StringType
{
    private $conditionP;

    public function __construct($value, callable $conditionP)
    {
        parent::__construct($value);
        $this->conditionP = $conditionP;
    }

    public function validate() {
        $fn = $this->conditionP;
        return $fn($this->__toString());
    }
}