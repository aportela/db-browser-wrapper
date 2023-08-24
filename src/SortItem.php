<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItem
{
    public string $field;
    public \aportela\DatabaseBrowserWrapper\Order $order;

    public function __construct(string $field, \aportela\DatabaseBrowserWrapper\Order $order)
    {
        $this->field = $field;
        $this->order = $order;
    }
}
