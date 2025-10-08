<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItemRandom implements InterfaceSortItem
{
    public function __construct() {}

    public function getQuery(\aportela\DatabaseWrapper\Adapter\AdapterType $adapterType): ?string
    {
        switch ($adapterType) {
            case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_SQLite:
                return (" RANDOM() ");
                break;
            case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_MariaDB:
                return (" RAND() ");
                break;
            case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_PostgreSQL:
                return (" RANDOM() ");
                break;
            default:
                return (null);
                break;
        }
    }
}
