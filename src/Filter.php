<?php

namespace aportela\DatabaseBrowserWrapper;

final class Filter implements \JsonSerializable
{
    public $originalParams = array();


    public function jsonSerialize(): mixed
    {
        return ($this->originalParams);
    }

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
