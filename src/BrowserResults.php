<?php

namespace aportela\DatabaseBrowserWrapper;

final class BrowserResults
{
    public \aportela\DatabaseBrowserWrapper\Pager $pager;
    public \aportela\DatabaseBrowserWrapper\Sort $sort;
    public \aportela\DatabaseBrowserWrapper\Filter $filter;
    /**
     * @var array<mixed>
     */
    public array $items = [];

    /**
     * @param array<mixed> $items
     */
    public function __construct(\aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Filter $filter, array $items = [])
    {
        $this->pager = $pager;
        $this->sort = $sort;
        $this->filter = $filter;
        $this->items = $items;
    }
}
