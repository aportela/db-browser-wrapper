<?php

namespace aportela\DatabaseBrowserWrapper;

final class Filter
{
    private $originalParams = array();

    public function __construct(array $params = [])
    {
        $this->originalParams = $params;
    }

    public function getParam(string $field): object
    {
        foreach ($this->originalParams as $param) {
            if ($param["field"] == $field) {
                return ($param);
            }
        }
        return (null);
    }
}
