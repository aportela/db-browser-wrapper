<?php

namespace aportela\DatabaseBrowserWrapper;

final class Browser
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \aportela\DatabaseBrowserWrapper\Pager $pager;
    private \aportela\DatabaseBrowserWrapper\Sort $sort;
    private \aportela\DatabaseBrowserWrapper\Filter $filter;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Filter $filter)
    {
        $this->dbh = $dbh;
        $this->pager = $pager;
        $this->sort = $sort;
        $this->filter = $filter;
    }

    public function getQueryFields(): string
    {
        return (implode(", ", [" * "]));
    }

    public function getQueryCountFields(string $field): string
    {
        return (sprintf(" COUNT(%s) AS totalResults ", $field));
    }

    public function launch(string $query, string $countQuery): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $results = $this->dbh->query($query);
        $this->pager->totalResults = count($results);
        if (!$this->pager->enabled) {
            $this->pager->totalPages = 1;
        } else {
            if ($this->pager->totalResults >= $this->pager->resultsPage) {
                $countResults = $this->dbh->query($countQuery);
                $this->pager->totalResults = $countResults[0]->totalResults;
                $this->pager->totalPages = ceil($this->pager->totalResults / $this->pager->resultsPage);
            } else {
                if ($this->pager->totalResults == 0) {
                    $this->pager->totalPages = 0;
                } else {
                    $this->pager->totalPages = $this->pager->currentPageIndex;
                }
            }
        }
        $data = new \aportela\DatabaseBrowserWrapper\BrowserResults($this->pager, $this->sort, $this->filter, $results);
        return ($data);
    }
}
