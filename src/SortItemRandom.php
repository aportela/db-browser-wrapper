<?php

namespace aportela\DatabaseBrowserWrapper;

final class SortItemRandom extends SortItemBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getQuery(): string
    {
        return (" RANDOM() ");
    }
}
