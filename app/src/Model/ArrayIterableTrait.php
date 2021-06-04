<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use ArrayIterator;
use Iterator;
use LogicException;
use ReflectionClass;
use ReflectionException;

trait ArrayIterableTrait
{
    public static function factory(array $list): ?self
    {
        $self = new static();
        $props = (new ReflectionClass(static::class))->getProperties();
        foreach ($props as $prop) {
            $name = $prop->getName();
            if (preg_match("/@dynamic/u", (string)$prop->getDocComment())) continue;
            $self->{$name} = $list[$name];
        }
        return $self;
    }

    public function asArray(): array
    {
        return (array)$this;
    }

    // ==== for array access ====
    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->{$offset} : null;
    }

    public function offsetSet($offset, $value)
    {
        $r = new ReflectionClass(static::class);
        try {
            $prop = $r->getProperty($offset);
// # Dynamicにプロパティを追加しようとした時の一時的回避パッチ。遭遇したら以下を生やす
// # /** @dynamic */
// # public $url;
//        } catch (ReflectionException $e) {
//            // 取得できない場合、無いプロパティを動的に生やす。最終的にはこの箇所はなくす。
//            $this->{$offset} = $value;
//            error_log("WARN: found undefined dynamic property {$offset} to ".static::class);
//            return;
//        }
//        try {
            if ($prop->isPublic()) {
                $this->{$prop->getName()} = $value;
            }
        } catch (ReflectionException $e) {
            throw new LogicException("touch missing property " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
        }
    }

    public function offsetUnset($offset)
    {
        throw new LogicException("un-settable.");
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator((array)$this);
    }

    public function count(): int
    {
        $r = new ReflectionClass(static::class);
        return count($r->getProperties());
    }
}
