<?php

namespace Righson\Types;

use JetBrains\PhpStorm\Pure;
use ReturnTypeWillChange;

class ArrayType implements \ArrayAccess
{
    const SEND_OPT_ARGS = 0;
    const SEND_OPT_FULL = 1;
    const SEND_OPT_KEY = 2;
    protected array $container;
    protected int $pos;

    public function __construct(array $array = [])
    {
        $this->reset();

        if (!empty($array)) {
            $this->container = $array;
        } else {
            $this->container = [];
        }
    }

    public function reset()
    {
        $this->pos = 0;
    }

    public static function makeFromString(string $str, string $delimiter, ...$keys): ArrayType
    {
        return (new ArrayType())->unpack(explode($delimiter, $str), $keys);
    }


    /**
     * @param string $newKey
     * @param array ...$pieces
     * @return ArrayType
     */
    public static function newFromArrays(string $newKey, ...$pieces): ArrayType
    {
        $at = new ArrayType();
        $cnt = 0;

        foreach ($pieces as $piece) {
            if (count($piece) === 0) continue;
            if ($cnt++ == 0) {
                $basis = $piece;
                $basisKey = $basis[0][$newKey];

                foreach ($basis as $value) {
                    $at->append([$basisKey => $value]);
                }
                continue;
            }

            $at->join($piece, $newKey);
        }

        return $at;
    }


    public function appendF($value, callable $fn, $offset = null): bool
    {
        if($fn($value)) {
            $this->append($value, $offset);
            return true;
        }
        return false;
    }

    public function append($value, $offset = null): ArrayType
    {
        if (empty($offset)) $offset = count($this->container);

        $this->offsetSet($offset, $value);
        return $this;
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->reset();
        }
        $this->container[$offset] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function join(array $add, string $key)
    {
        $newCont = array();
        $cntr = 0;

        foreach ($this->container as $idx_ => $contValue) {
            foreach ($add as $value) {
                $arr = [$value[$key] => $value];
                $newCont[$cntr] = $this->container[$idx_] + $arr;
                $cntr++;
            }
        }

        $this->container = $newCont;
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (is_numeric($offset) && $offset < 0 && abs($offset) < count($this->container)) {
            return $this->container[count($this->container) + $offset];
        }

        if ($this->offsetExists($offset)) {
            return $this->container[$offset];
        } else {
            return false;
        }
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->container[$offset]);
        }
    }

    public function KeyIsEmpty($keys): bool
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (!empty($this->container[$key])) return false;
            }
            return true;
        } else {
            return empty($this->container[$keys]);
        }
    }

    public function unpack(array $from, array $bind): ArrayType
    {

        $restMode = false;
        $restKey = '';

        foreach ($from as $idx => $item) {
            $bnd = new StringType($bind[$idx]);
            if (!is_numeric($idx)) return $this;

            if (!$restMode && isset($bind[$idx]) && $bnd->offsetExists('&rest:')) {

                $rest = explode(':', $bind[$idx]);
                if (empty($rest[1])) return $this;

                $restKey = $rest[1];
                $restMode = true;
            }

            if (is_numeric($item)) $item = $item + 0;

            if ($restMode) {
                $this->container[$restKey][] = $item;
                continue;
            }

            if (isset($bind[$idx])) {
                $this->container[$bind[$idx]] = $item;
                continue;
            }

            return $this;
        }

        return $this;
    }

    public function all($keys = []): bool
    {
        if (empty($keys)) {
            foreach ($this->container as $value) {
                if (empty($value)) return false;
            }
        } else {
            foreach ($keys as $key) {
                if (empty($this->container[$key])) return false;
            }
        }

        return true;
    }

    #[Pure]
    public function any(...$keys): bool
    {
        if ($this->length() == 0) return true;
        if (empty($keys)) {
            foreach ($this->container as $value) {
                if (!empty($value)) return true;
            }
        } else {
            foreach ($keys as $key) {
                if (isset($this->container[$key]) && $this->container[$key]) return true;
            }
        }

        return false;
    }

    public function sortBy($filed): ArrayType
    {
        $dataToSort = array_column($this->container, $filed);
        array_multisort($dataToSort, SORT_ASC, $this->container);

        return $this;
    }

    public function gi($key)
    {
        if ($this->offsetExists($key)) {
            if(is_array($this->container[$key])) {
                return new ArrayType($this->container[$key]);
            }
            return $this->container[$key];
        }
        return null;
    }

    public function get(): array
    {
        return $this->container;
    }

    public function haveKeys(...$keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->container)) return False;
        }

        return True;
    }

    public function notEmpty(...$keys): bool
    {
        foreach ($keys as $key) {
            if (empty($key)) return False;
        }

        return True;
    }

    public function sliceK(...$keys): array
    {
        $ret = array();

        foreach ($keys as $key) {
            $ret[] = $this->container[$key];
        }

        return $ret;
    }

    public function length(): int
    {
        return count($this->container);
    }

    public function take(int $n): array
    {
        $ret = [];

        while ($n > 0) {
            if (isset($this->container[$this->pos])) {
                $item = $this->container[$this->pos];
                if (empty($item)) break;

                $ret[] = $item;
            }
            $this->pos++;
            $n--;

        }

        return $ret;
    }

    public function drop()
    {
        $this->container = [];
        $this->pos = 0;
    }

    public function apply(array $basis)
    {
        $this->container = $basis;
    }

    public function implode(string $sep = ''): string
    {
        return implode($sep, $this->container);
    }

    public function have($value): bool
    {
        return in_array($value, $this->container, true);
    }

    public function map(\Closure $closure, $sendArgs = self::SEND_OPT_ARGS): array
    {
        $ret = [];
        foreach ($this->container as $idx => $item) {
            switch ($sendArgs) {
                case self::SEND_OPT_ARGS:
                    $ret[] = $closure($item);
                    break;
                case self::SEND_OPT_FULL:
                    $ret[] = $closure($idx, $item);
                    break;
                case self::SEND_OPT_KEY:
                    $ret[] = $closure($idx);
                    break;
                default:
                    return [];
            }
        }

        return $ret;
    }

    public function reduce(\Closure $closure, $target)
    {

        foreach ($this->container as $item) {
            $target = $closure($target, $item);
        }

        return $target;
    }

    public function del($offset): bool
    {
        if (isset($this->container[$offset])) {
            unset($this->container[$offset]);
            return true;
        }
        $pos = array_search($offset, $this->container);
        if(!isset($this->container[$pos])) return false;
        unset($this->container[$pos]);
        return true;
    }

    public function slice(int $start, int $end)
    {
        $finish = $end - $start + 1;
        if ($end < 0) $finish = count($this->container) + $end;

        return new ArrayType(array_slice($this->container, $start, $finish));
    }

    public function head()
    {
        $firstK = array_key_first($this->container);
        return new ArrayType($this->container[$firstK]);
    }

    public function values()
    {
        return array_values($this->container);
    }

    public function getDefault(string $key, $default=false)
    {
        if(isset($this->container[$key])) {
            return $this->container[$key];
        }
        return $default;
    }

    public function appendJoin($value, string $sep, $key): ArrayType
    {
        if(empty($value)) return $this;
        if(isset($this->container[$key])) {
            $this->container[$key] .= $sep . $value;
            return $this;
        }
        $this->container[$key] = $value;
        return $this;
    }

    public function iter(): \Generator
    {
        foreach ($this->container as $key => $value) {
            yield $key => $value;
        }
    }

    public function pop()
    {
        return array_pop($this->container);
    }

    public function shift()
    {
        return array_shift($this->container);
    }
}