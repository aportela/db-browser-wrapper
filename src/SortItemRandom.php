<?php

declare(strict_types=1);

namespace aportela\DatabaseBrowserWrapper;

final class SortItemRandom implements InterfaceSortItem
{
    public function getQuery(\aportela\DatabaseWrapper\Adapter\AdapterType $adapterType): ?string
    {
        switch ($adapterType) {
            case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_SQLite:
            case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_PostgreSQL:
                return " RANDOM() ";
            case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_MariaDB:
                return " RAND() ";
            default:
                return null;
        }
    }
}
