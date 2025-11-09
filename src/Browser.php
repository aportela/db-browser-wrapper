<?php

namespace aportela\DatabaseBrowserWrapper;

final class Browser
{
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
    private ?array $fieldCountDefinition = [];
    private readonly \aportela\DatabaseBrowserWrapper\Pager $pager;
    private readonly mixed $afterBrowseFunction;

    /**
     * @param array<string, string> $fieldDefinitions
     * @param array<string, string> $fieldCountDefinition
     */
    public function __construct(private readonly \aportela\DatabaseWrapper\DB $dbh, array $fieldDefinitions, ?array $fieldCountDefinition, \aportela\DatabaseBrowserWrapper\Pager $pager, private readonly \aportela\DatabaseBrowserWrapper\Sort $sort, private readonly \aportela\DatabaseBrowserWrapper\Filter $filter, ?callable $afterBrowseFunction = null)
    {
        if (count($fieldDefinitions) > 0) {
            $this->fieldDefinitions = $fieldDefinitions;
        } else {
            throw new \Exception("invalid fieldDefinitions");
        }
        if ($pager->isEnabled()) {
            if (count($fieldCountDefinition) == 1) {
                $this->fieldCountDefinition = $fieldCountDefinition;
            } else {
                throw new \Exception("invalid fieldCountDefinition");
            }
        }
        $this->pager = $pager;
        $this->afterBrowseFunction = $afterBrowseFunction;
    }

    private function getQueryFields(): string
    {
        $queryFields = [];
        foreach ($this->fieldDefinitions as $alias => $field) {
            $queryFields[] = $field . " AS " . $alias;
        }
        return (implode(", ", $queryFields));
    }

    private function getQueryCountSQLField(): string
    {
        if ($this->pager->isEnabled()) {
            return (current(array_values($this->fieldCountDefinition)));
        } else {
            throw new \Exception("pager is disabled");
        }
    }

    private function getQueryCountAlias(): string
    {
        if ($this->pager->isEnabled()) {
            return (current(array_keys($this->fieldCountDefinition)));
        } else {
            throw new \Exception("pager is disabled");
        }
    }

    private function getQueryCountFields(): string
    {
        if ($this->pager->isEnabled()) {
            return (sprintf(" %s AS %s ", $this->getQueryCountSQLField(), $this->getQueryCountAlias()));
        } else {
            throw new \Exception("pager is disabled");
        }
    }

    private function getQuerySort(): ?string
    {
        return ($this->sort->getQuery($this->dbh->getAdapterType()));
    }

    private function getQueryPager(): ?string
    {
        return ($this->pager->getQuery($this->dbh->getAdapterType()));
    }

    /**
     * build / complete query with $fieldDefinitions, $pager & $sort set on constructor
     *
     * you must supply a query with three "%s" ex:
     * 1st: query fields
     * 2st: sort block
     * 3rd: pagination block
     *
     * SELECT %s FROM TABLEV1 WHERE 1 = 1 %s %s
     */
    public function buildQuery(string $sql): string
    {
        return (sprintf(
            $sql,
            $this->getQueryFields(),
            $this->getQuerySort(),
            $this->getQueryPager()
        ));
    }

    /**
     * build / complete (COUNT) query with $fieldCountDefinition set on constructor
     *
     * you must supply a query with one "%s" ex:
     * 1st: query fields
     *
     * SELECT %s FROM TABLEV1 WHERE 1 = 1
     */
    public function buildQueryCount(string $sql): string
    {
        return (sprintf(
            $sql,
            $this->getQueryCountFields()
        ));
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

    public function launch(string $query, string $countQuery, bool $skipCount = false): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $countRequired = false;
        $results = $this->dbh->query($query, $this->queryParams);
        $totalQueryResults = count($results);
        if (!$this->pager->isEnabled()) {
            // if pager is disabled, total results is length of main query results array
            $this->pager->setTotalResults($totalQueryResults);
            $this->pager->setTotalPages($totalQueryResults > 0 ? 1 : 0);
        } else {
            // pager is enabled
            if ($this->pager->getCurrentPageIndex() == 1) {
                // requested first page
                if ($totalQueryResults >= $this->pager->getResultsPage()) {
                    // main query results array length >= resultsPage
                    // we are not sure that this page is last, so count is required
                    $countRequired = true;
                } else {
                    // main query results array length < resultsPage
                    // this first page is the unique/last page
                    $this->pager->setTotalResults($totalQueryResults);
                    $this->pager->setTotalPages($totalQueryResults > 0 ? 1 : 0);
                }
            } elseif ($this->pager->getCurrentPageIndex() > 1) {
                // requested page index > 1
                if ($totalQueryResults >= $this->pager->getResultsPage()) {
                    // main query results array length >= resultsPage
                    // we are not sure that this page is last, so count is required
                    $countRequired = true;
                } else {
                    // main query results array length < resultsPage
                    if ($totalQueryResults > 0) {
                        // there are results for this page, we can assume that is last page
                        $this->pager->setTotalResults($totalQueryResults + ($this->pager->getResultsPage() * ($this->pager->getCurrentPageIndex() - 1)));
                        $this->pager->setTotalPages($this->pager->getCurrentPageIndex());
                    } else {
                        // no results for this page, invalid page (ex page 5 of 3)
                        // we are not sure that this page is last, so count is required
                        $countRequired = true;
                    }
                }
            } else {
                // currentPageindex <= 0 (invalid value)
                throw new \Exception("invalid current page index");
            }
            if (! $skipCount && $countRequired) {
                $countResults = $this->dbh->query($countQuery, $this->queryParams);
                $this->pager->setTotalResults($countResults[0]->{$this->getQueryCountAlias()}, true);
            }
        }
        $data = new \aportela\DatabaseBrowserWrapper\BrowserResults($this->filter, $this->sort, $this->pager, $results);
        if ($this->afterBrowseFunction != null) {
            call_user_func($this->afterBrowseFunction, $data);
        }
        return ($data);
    }
}
