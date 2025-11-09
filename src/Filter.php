<?php

namespace aportela\DatabaseBrowserWrapper;

// TODO: document this
final readonly class Filter implements \JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return ($this->originalParams);
    }

    /**
     * @param array<mixed> $originalParams
     */
    public function __construct(private array $originalParams = [])
    {
    }

    public function hasParam(string $field): mixed
    {
        return (array_key_exists($field, $this->originalParams));
    }

    public function getParamValue(string $field): mixed
    {
        foreach ($this->originalParams as $key => $value) {
            if ($key == $field) {
                return ($value);
            }
        }

        return (null);
    }
}
