<?php

namespace aportela\DatabaseBrowserWrapper;

final class Pager
{
    public bool $enabled = true;
    public int $currentPageIndex = 1;
    public int $totalPages = 0;
    public int $resultsPage = 32;
    public int $totalResults = 0;

    public function __construct(bool $enabled = true, int $currentPage = 1, int $resultsPage = 32)
    {
        $this->enabled = $enabled;
        if ($this->enabled) {
            $this->currentPageIndex = $currentPage;
            $this->resultsPage = $resultsPage;
        }
    }

    public function setTotalResults(int $totalResults): void
    {
        if ($this->enabled) {
            $this->totalResults = $totalResults;
            $this->totalPages = ceil($this->totalResults / $this->resultsPage);
        }
    }

    public function getQueryLimit(): ?string
    {
        if ($this->enabled) {
            $start = ($this->currentPageIndex - 1) * $this->resultsPage;
            return (sprintf(" LIMIT %d, %d ", $start, $this->resultsPage));
        } else {
            return (null);
        }
    }
}
