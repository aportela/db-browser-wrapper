<?php

namespace aportela\DatabaseBrowserWrapper;

final class Sort
{
    /**
     * @var array <\aportela\DatabaseBrowserWrapper\SortItemBase>
     *
     */
    private array $items = [];

    /**
     * @param array<\aportela\DatabaseBrowserWrapper\SortItemBase> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getQuery(): ?string
    {
        $sortItems = [];
        foreach ($this->items as $item) {
            $sortItems[] = $item->getQuery();
        }
        if (count($sortItems) > 0) {
            return (sprintf(" ORDER BY %s", implode(", ", $sortItems)));
        } else {
            return (null);
        }
    }
}
