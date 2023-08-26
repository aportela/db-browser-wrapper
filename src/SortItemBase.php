<?php

namespace aportela\DatabaseBrowserWrapper;

abstract class SortItemBase
{
    protected bool $isRandom;

    public function __construct(bool $isRandom)
    {
        $this->isRandom = $isRandom;
    }
}
