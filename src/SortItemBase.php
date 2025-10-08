<?php

namespace aportela\DatabaseBrowserWrapper;

abstract class SortItemBase
{
    public function __construct() {}

    public abstract function getQuery(): string;
}
