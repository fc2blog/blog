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
            if ($prop->isPublic()) {
                $this->{$prop->getName()} = $value;
            }
        } catch (ReflectionException $e) {
            throw new LogicException("touch missing property " . $e->getMessage());
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
