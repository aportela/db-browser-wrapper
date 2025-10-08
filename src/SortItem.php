<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItem extends SortItemBase
{
    private string $field;
    private \aportela\DatabaseBrowserWrapper\Order $order;
    private bool $caseInsensitive = false;

    public function __construct(string $field, \aportela\DatabaseBrowserWrapper\Order $order, bool $caseInsensitive)
    {
        parent::__construct();
        $this->field = $field;
        $this->order = $order;
        $this->caseInsensitive = $caseInsensitive;
    }

    public function getQuery(): string
    {
        if ($this->caseInsensitive) {
            // TODO: sqlite && mariadb && postgresql
            return (sprintf(" %s COLLATE NOCASE %s", $this->field, $this->order->value));
        } else {
            return (sprintf(" %s %s", $this->field, $this->order->value));
        }
    }
}
