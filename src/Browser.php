<?php

namespace aportela\DatabaseBrowserWrapper;

final class Browser
{
    private \aportela\DatabaseWrapper\DB $dbh;

    /**
     * @var array<\aportela\DatabaseWrapper\Param\InterfaceParam>
     */
    private array $queryParams = [];
    /**
     * @var array<string, string>
     */
    private array $fieldDefinitions = [];
    /**
     * @var array<string, string>
     */
    private array $fieldCountDefinition = [];
    private \aportela\DatabaseBrowserWrapper\Pager $pager;
    private \aportela\DatabaseBrowserWrapper\Sort $sort;
    private \aportela\DatabaseBrowserWrapper\Filter $filter;
    private mixed $afterBrowseFunction;

    /**
     * @param array<string, string> $fieldDefinitions
     * @param array<string, string> $fieldCountDefinition
     */
    public function __construct(\aportela\DatabaseWrapper\DB $dbh, array $fieldDefinitions, array $fieldCountDefinition, \aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Filter $filter, ?callable $afterBrowseFunction = null)
    {
        $this->dbh = $dbh;
        if (count($fieldDefinitions) > 0) {
            $this->fieldDefinitions = $fieldDefinitions;
        } else {
            throw new \Exception("invalid fieldDefinitions");
        }
        if (count($fieldCountDefinition) == 1) {
            $this->fieldCountDefinition = $fieldCountDefinition;
        } else {
            throw new \Exception("invalid fieldCountDefinition");
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
        return (current(array_values($this->fieldCountDefinition)));
    }

    private function getQueryCountAlias(): string
    {
        return (current(array_keys($this->fieldCountDefinition)));
    }

    public function getQueryCountFields(): string
    {
        return (sprintf(" %s AS %s ", $this->getQueryCountSQLField(), $this->getQueryCountAlias()));
    }

    public function isSortedBy(string $fieldName): bool
    {
        foreach ($this->sort->items as $item) {
            if ($item->field == $fieldName) {
                return (true);
            }
        }
        return (false);
    }

    public function getSortOrder(string $fieldName): ?string
    {
        foreach ($this->sort->items as $item) {
            if ($item->field == $fieldName) {
                if ($item->caseInsensitive) {
                    return (sprintf(" COLLATE NOCASE %s", $item->order->value));
                } else {
                    return (sprintf(" %s", $item->order->value));
                }
            }
        }
        return (null);
    }

    public function getQuerySort(): ?string
    {
        $sortItems = [];
        foreach ($this->sort->items as $item) {
            switch (get_class($item)) {
                case "aportela\DatabaseBrowserWrapper\SortItem":
                    if ($item->caseInsensitive) {
                        $sortItems[] = sprintf(" %s COLLATE NOCASE %s", $item->field, $item->order->value);
                    } else {
                        $sortItems[] = sprintf(" %s %s", $item->field, $item->order->value);
                    }
                    break;
                case "aportela\DatabaseBrowserWrapper\SortItemRandom":
                    $sortItems[] = " RANDOM() ";
                    break;
            }
        }
        if (count($sortItems) > 0) {
            return (sprintf(" ORDER BY %s", implode(", ", $sortItems)));
        } else {
            return (null);
        }
    }

    public function addDBQueryParam(\aportela\DatabaseWrapper\Param\InterfaceParam $param): void
    {
        $this->queryParams[] = $param;
    }

    /**
     * @param array<\aportela\DatabaseWrapper\Param\InterfaceParam> $params
     */
    public function addDBQueryParams(array $params = []): void
    {
        foreach ($params as $param) {
            $this->queryParams[] = $param;
        }
    }

    public function launch(string $query, string $countQuery): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $results = $this->dbh->query($query, $this->queryParams);
        $this->pager->totalResults = count($results);
        if (!$this->pager->enabled) {
            $this->pager->totalPages = 1;
        } else {
            if ($this->pager->totalResults >= $this->pager->resultsPage || count($this->queryParams) > 0) {
                $countResults = $this->dbh->query($countQuery, $this->queryParams);
                $this->pager->totalResults = $countResults[0]->{$this->getQueryCountAlias()};
                $this->pager->totalPages = intval(ceil($this->pager->totalResults / $this->pager->resultsPage));
            } else {
                if ($this->pager->totalResults == 0) {
                    $this->pager->totalPages = 0;
                } else {
                    $this->pager->totalResults += $this->pager->resultsPage * ($this->pager->currentPageIndex - 1);
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
