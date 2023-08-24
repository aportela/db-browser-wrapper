<?php

namespace aportela\DatabaseBrowserWrapper;

final class Sort
{
    public array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}
