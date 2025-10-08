<?php

namespace aportela\DatabaseBrowserWrapper;

final class Sort
{
    /**
     * @var array <\aportela\DatabaseBrowserWrapper\SortItemBase>
     *
     */
    public array $items = [];

    /**
     * @param array<\aportela\DatabaseBrowserWrapper\SortItemBase> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}
