<?php

namespace aportela\DatabaseBrowserWrapper;

final class Browser
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private array $fieldDefinitions = [];
    private array $fieldCountDefition = [];
    private \aportela\DatabaseBrowserWrapper\Pager $pager;
    private \aportela\DatabaseBrowserWrapper\Sort $sort;
    private \aportela\DatabaseBrowserWrapper\Filter $filter;
    private $afterBrowseFunction;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, array $fieldDefinitions, array $fieldCountDefition, \aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Filter $filter, callable $afterBrowseFunction = null)
    {
        $this->dbh = $dbh;
        if (count($fieldDefinitions) > 0) {
            $this->fieldDefinitions = $fieldDefinitions;
        } else {
            throw new \Exception("invalid fieldDefinitions");
        }
        if (count($fieldCountDefition) == 1) {
            $this->fieldCountDefition = $fieldCountDefition;
        } else {
            throw new \Exception("invalid fieldCountDefition");
        }
        $this->pager = $pager;
        $this->sort = $sort;
        $this->filter = $filter;
        $this->afterBrowseFunction = $afterBrowseFunction;
    }

    public function getQueryFields(): string
    {
        $queryFields = array();
        foreach ($this->fieldDefinitions as $alias => $field) {
            $queryFields[] = $field . " AS " . $alias;
        }
        return (implode(", ", $queryFields));
    }

    private function getQueryCountSQLField(): string
    {
        return (current(array_values($this->fieldCountDefition)));
    }

    private function getQueryCountAlias(): string
    {
        return (current(array_keys($this->fieldCountDefition)));
    }

    public function getQueryCountFields(): string
    {
        return (sprintf(" %s AS %s ", $this->getQueryCountSQLField(), $this->getQueryCountAlias()));
    }

    public function getQuerySort(): ?string
    {
        $sortItems = [];
        foreach ($this->sort->items as $item) {
            if ($item->caseInsensitive) {
                $sortItems[] = sprintf(" %s COLLATE NOCASE %s", $item->field, $item->order->value);
            } else {
                $sortItems[] = sprintf(" %s %s", $item->field, $item->order->value);
            }
        }
        if (count($sortItems) > 0) {
            return (sprintf(" ORDER BY %s", implode(", ", $sortItems)));
        } else {
            return (null);
        }
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
                $this->pager->totalResults = $countResults[0]->{$this->getQueryCountAlias()};
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
        if ($this->afterBrowseFunction != null && is_callable($this->afterBrowseFunction)) {
            call_user_func($this->afterBrowseFunction, $data);
        }
        return ($data);
    }
}
