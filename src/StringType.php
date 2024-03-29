<?php

namespace Righson\Types;

use JetBrains\PhpStorm\Pure;
use ReturnTypeWillChange;

class StringType implements \ArrayAccess {
    private string $content;

    public function __construct($value) {
        $this->content = (string) $value;
    }

    public function offsetExists($offset): bool
    {
        if (is_string($offset)) {
            return str_contains($this->content, $offset);
        }
        $offsetInt = (int)$offset;
        return mb_strlen($this->content) > $offsetInt;
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (is_array($offset) && count($offset) == 2) {
            return mb_substr($this->content, $offset[0], $offset[1] - 1);
        } elseif (is_string($offset)) {
            list($start, $length) = explode(':', $offset);
            $start = (int)$start;
            $length = (int)$length;
            return ($length) ? mb_substr($this->content, $start, $length - $start) : mb_substr($this->content, $start);
        } else {
            $offsetInt = (int)$offset;
            return mb_substr($this->content, $offsetInt, 1);
        }
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $offsetInt = (int)$offset;
        if (is_integer($offset)) {
            $length = mb_strlen($this->content);
            $before = ($offsetInt) ? mb_substr($this->content, 0, $offsetInt) : '';
            $after = ($offsetInt == $length) ? '' : mb_substr($this->content, $offsetInt + 1);

            $this->content = $before . $value . $after;
        } elseif (is_string($offset)) {
            list($start, $length) = explode(':', $offset);
            $start = (int)$start;
            $length = (int)$length;

            $before = ($start) ? mb_substr($this->content, 0, $start) : '';
            $after = ($length) ? mb_substr($this->content, $length) : '';

            $this->content = $before . $value . $after;
        }
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if (is_integer($offset)) {
            $offsetInt = (int)$offset;

            $length = mb_strlen($this->content);
            $before = ($offsetInt) ? mb_substr($this->content, 0, $offsetInt) : '';
            $after = ($offsetInt == $length) ? '' : mb_substr($this->content, $offsetInt + 1);

            $this->content = $before . $after;
        }
    }

    public function toString(): string
    {
        return $this->content;
    }

    public function iter($by = 1): \Generator
    {
        foreach (str_split($this->content, $by) as $char) {
            yield $char;
        }
    }

    #[Pure]
    public function __toString()
    {
        return $this->toString();
    }

    public function format(string $string, bool $dropTail = true): string
    {
        $tempStr = '';
        $resultStr = '';
        $tmpContent = $this->content;

        foreach (str_split($string) as $char) {
            if ($this->content === '') break;

            if (is_numeric($char)) {
                $tempStr .= $char;
            } else {
                $resultStr .= $this[":$tempStr"] . $char;
                $this->content = $this["$tempStr:"];
                $tempStr = '';
            }
        }

        if (!$dropTail) {
            $resultStr .= $this->content;
            $this->content = $tmpContent;
            return $resultStr;
        }

        $resultStr .= $this[":$tempStr"];
        $this->content = $this["$tempStr:"];
        return $resultStr;
    }

    public function take(int $chunk)
    {
        if (strlen($this->content) <= $chunk) {
            $ret = $this->content;
            $this->content = '';
        } else {
            $ret = $this[":$chunk"];
            $this->content = $this["$chunk:"];
        }
        return $ret;
    }

    public function length(): int
    {
        return strlen($this->content);
    }

    public function count(string $string): int
    {
        return substr_count($this->content, $string);
    }

    public function split(string $delimiter)
    {
        return explode($delimiter, $this->content);
    }

    public function join($joinStr)
    {
        if(is_string($joinStr)) {
            $this->content .= $joinStr;
            return;
        }
        if($joinStr instanceof $this) {
            $this->content .= $joinStr->toString();
        }
    }

    public function apply(callable $fn): StringType
    {
        $cont = $this->content;
        $this->content = $fn($cont);
        return $this;
    }
}
