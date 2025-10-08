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
            if ($currentPage > 0) {
                $this->currentPageIndex = $currentPage;
            } else {
                throw new \Exception("invalid current page index");
            }
            if ($resultsPage > 0) {
                $this->resultsPage = $resultsPage;
            } else {
                throw new \Exception("invalid results page");
            }
        }
    }

    public function setTotalResults(int $totalResults): void
    {
        if ($totalResults >= 0) {
            $this->totalResults = $totalResults;
            if ($this->enabled) {
                $this->totalPages = intval(ceil($this->totalResults / $this->resultsPage));
            } else {
                $this->totalPages = $totalResults > 0 ? 1 : 0;
            }
        } else {
            throw new \Exception("invalid total results");
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
