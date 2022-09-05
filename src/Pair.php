<?php

namespace Righson\Types;

class Pair
{
    private mixed $left;
    private mixed $right;

    public function __construct($left = null, $right = null)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public static function makeFromArray(array $array): Pair
    {
        list($left, $right) = $array;
        return new Pair($left, $right);
    }

    public function get(): array
    {
        return [$this->left, $this->right];
    }

    public function left(): mixed
    {
        return $this->left;
    }

    public function right(): mixed
    {
        return $this->right;
    }

    public function ifFirst(callable $fn, callable $then = null)
    {
        if($this->left) {
            return $fn($this->left);
        }
        if($then && $this->right) {
            return $then($this->right);
        }

        return null;
    }

    public function ifSecond(callable $fn, callable $then = null)
    {
        if($this->right) {
            return $fn($this->right);
        }
        if($then && $this->left) {
            return $then($this->left);
        }

        return null;
    }
}