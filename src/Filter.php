<?php

namespace aportela\DatabaseBrowserWrapper;

// TODO: document this
final class Filter implements \JsonSerializable
{
    /**
     * @var array<mixed>
     */
    private array $originalParams = [];


    public function jsonSerialize(): mixed
    {
        return ($this->originalParams);
    }

    /**
     * @param array<mixed> $params
     */
    public function __construct(array $params = [])
    {
        $this->originalParams = $params;
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
