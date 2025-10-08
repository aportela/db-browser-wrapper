<?php

namespace aportela\DatabaseBrowserWrapper;

final class Sort
{
    /**
     * @var array <\aportela\DatabaseBrowserWrapper\InterfaceSortItem>
     *
     */
    private array $items = [];

    /**
     * @param array<\aportela\DatabaseBrowserWrapper\InterfaceSortItem> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getQuery(\aportela\DatabaseWrapper\Adapter\AdapterType $adapterType): ?string
    {
        $sortItems = [];
        foreach ($this->items as $item) {
            $sortItems[] = $item->getQuery($adapterType);
        }
        if (count($sortItems) > 0) {
            return (sprintf(" ORDER BY %s", implode(", ", $sortItems)));
        } else {
            return (null);
        }
    }
}
