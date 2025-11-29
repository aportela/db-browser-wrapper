<?php

declare(strict_types=1);

namespace aportela\DatabaseBrowserWrapper;

enum Order: string
{
    case ASC = "ASC";

    case DESC = "DESC";
}
