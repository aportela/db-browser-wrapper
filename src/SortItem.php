<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItem
{
    public string $field;
    public \aportela\DatabaseBrowserWrapper\Order $order;
    public bool $caseInsensitive = true;

    public function __construct(string $field, \aportela\DatabaseBrowserWrapper\Order $order, bool $caseInsensitive)
    {
        $this->field = $field;
        $this->order = $order;
        $this->caseInsensitive = $caseInsensitive;
    }
}
