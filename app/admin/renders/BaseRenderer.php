<?php

namespace app\admin\renders;

class BaseRenderer implements \JsonSerializable
{
    public string $type;

    public static function make(): static
    {
        return new static();
    }

    public function __call($name, $arguments): static
    {
        $this->$name = array_shift($arguments);

        return $this;
    }

    public function set($name, $value): static
    {
        $this->$name = $value;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function toJson(): bool|string
    {
        return json_encode($this);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
