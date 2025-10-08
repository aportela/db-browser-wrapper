<?php

namespace aportela\DatabaseBrowserWrapper;

final class BrowserResults
{
    public \aportela\DatabaseBrowserWrapper\Filter $filter;
    public \aportela\DatabaseBrowserWrapper\Sort $sort;
    public \aportela\DatabaseBrowserWrapper\Pager $pager;
    /**
     * @var array<mixed>
     */
    public array $items = [];

    /**
     * @param array<mixed> $items
     */
    public function __construct(\aportela\DatabaseBrowserWrapper\Filter $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager, array $items = [])
    {
        $this->filter = $filter;
        $this->sort = $sort;
        $this->pager = $pager;
        $this->items = $items;
    }
}
