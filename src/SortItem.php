<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItem implements InterfaceSortItem
{
    private string $field;
    private \aportela\DatabaseBrowserWrapper\Order $order;
    private bool $caseInsensitive = false;

    public function __construct(string $field, \aportela\DatabaseBrowserWrapper\Order $order, bool $caseInsensitive)
    {
        $this->field = $field;
        $this->order = $order;
        $this->caseInsensitive = $caseInsensitive;
    }

    public function getQuery(\aportela\DatabaseWrapper\Adapter\AdapterType $adapterType): ?string
    {
        if ($this->caseInsensitive) {
            switch ($adapterType) {
                case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_SQLite:
                    return sprintf(" %s COLLATE NOCASE %s", $this->field, $this->order->value);
                case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_MariaDB:
                    return sprintf(" %s COLLATE utf8_general_ci %s", $this->field, $this->order->value);
                case \aportela\DatabaseWrapper\Adapter\AdapterType::PDO_PostgreSQL:
                    return sprintf(' %s COLLATE "C" %s', $this->field, $this->order->value);
                default:
                    return (null);
            }
        } else {
            return sprintf(" %s %s", $this->field, $this->order->value);
        }
    }
}
