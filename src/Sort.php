<?php

declare(strict_types=1);

namespace aportela\DatabaseBrowserWrapper;

final readonly class Sort
{
    /**
     * @param array<\aportela\DatabaseBrowserWrapper\InterfaceSortItem> $items
     */
    public function __construct(private array $items = []) {}

    public function getQuery(\aportela\DatabaseWrapper\Adapter\AdapterType $adapterType): ?string
    {
        $sortItems = [];
        foreach ($this->items as $item) {
            $sortItems[] = $item->getQuery($adapterType);
        }

        if ($sortItems !== []) {
            return (sprintf(" ORDER BY %s", implode(", ", $sortItems)));
        } else {
            return (null);
        }
    }
}
