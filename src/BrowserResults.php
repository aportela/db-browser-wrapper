<?php

declare(strict_types=1);

namespace aportela\DatabaseBrowserWrapper;

final class BrowserResults
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(public \aportela\DatabaseBrowserWrapper\Filter $filter, public \aportela\DatabaseBrowserWrapper\Sort $sort, public \aportela\DatabaseBrowserWrapper\Pager $pager, public array $items = [])
    {
    }
}
