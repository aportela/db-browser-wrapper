<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItem extends SortItemBase
{
    public string $field;
    public \aportela\DatabaseBrowserWrapper\Order $order;
    public bool $caseInsensitive = false;

    public function __construct(string $field, \aportela\DatabaseBrowserWrapper\Order $order, bool $caseInsensitive)
    {
        parent::__construct(false);
        $this->field = $field;
        $this->order = $order;
        $this->caseInsensitive = $caseInsensitive;
    }
}
