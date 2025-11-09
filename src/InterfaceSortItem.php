<?php

declare(strict_types=1);

namespace aportela\DatabaseBrowserWrapper;

interface InterfaceSortItem
{
    public function getQuery(\aportela\DatabaseWrapper\Adapter\AdapterType $adapterType): ?string;
}
