<?php

namespace aportela\DatabaseBrowserWrapper;

final class Pager
{
    private bool $enabled = true;
    private int $currentPageIndex = 1;
    private int $totalPages = 0;
    private int $resultsPage = 32;
    private int $totalResults = 0;

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

    public function isEnabled(): bool
    {
        return ($this->enabled);
    }

    public function getCurrentPageIndex(): int
    {
        return ($this->currentPageIndex);
    }

    public function getTotalPages(): int
    {
        return ($this->totalPages);
    }

    public function setTotalPages(int $totalPages): void
    {
        if ($totalPages >= 0) {
            $this->totalPages = $totalPages;
        } else {
            throw new \Exception("invalid totalPages (negative) value ");
        }
    }

    public function getResultsPage(): int
    {
        return ($this->resultsPage);
    }

    public function setResultsPage(int $resultsPage): void
    {
        if ($resultsPage >= 0) {
            $this->resultsPage = $resultsPage;
        } else {
            throw new \Exception("invalid resultsPage (negative) value ");
        }
    }

    public function getTotalResults(): int
    {
        return ($this->totalResults);
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
            throw new \Exception("invalid totalResults (negative) value");
        }
    }

    public function getQuery(): ?string
    {
        if ($this->enabled) {
            // TODO: sqlite && mariadb && postgresql
            $start = ($this->currentPageIndex - 1) * $this->resultsPage;
            return (sprintf(" LIMIT %d, %d ", $start, $this->resultsPage));
        } else {
            return (null);
        }
    }
}
