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
    private ?array $fieldCountDefinition = [];
    private \aportela\DatabaseBrowserWrapper\Pager $pager;
    private \aportela\DatabaseBrowserWrapper\Sort $sort;
    private \aportela\DatabaseBrowserWrapper\Filter $filter;
    private mixed $afterBrowseFunction;

    /**
     * @param array<string, string> $fieldDefinitions
     * @param array<string, string> $fieldCountDefinition
     */
    public function __construct(\aportela\DatabaseWrapper\DB $dbh, array $fieldDefinitions, ?array $fieldCountDefinition, \aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Filter $filter, ?callable $afterBrowseFunction = null)
    {
        $this->dbh = $dbh;
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
        $this->sort = $sort;
        $this->filter = $filter;
        $this->afterBrowseFunction = $afterBrowseFunction;
    }

    private function getQueryFields(): string
    {
        $queryFields = array();
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

    public function buildQuery($sql): string
    {
        return (sprintf(
            $sql,
            $this->getQueryFields(),
            $this->getQuerySort(),
            $this->getQueryPager()
        ));
    }

    public function buildQueryCount($sql): string
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

    public function launch(string $query, string $countQuery): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $results = $this->dbh->query($query, $this->queryParams);
        $this->pager->setTotalResults(count($results));
        if (!$this->pager->isEnabled()) {
            $this->pager->setTotalPages(1);
        } else {
            // WHY count($this->queryParams) > 0 ???
            if ($this->pager->getTotalResults() >= $this->pager->getResultsPage() || count($this->queryParams) > 0) {
                $countResults = $this->dbh->query($countQuery, $this->queryParams);
                $this->pager->setTotalResults($countResults[0]->{$this->getQueryCountAlias()});
                $this->pager->setTotalPages(intval(ceil($this->pager->getTotalResults() / $this->pager->getResultsPage())));
            } else {
                if ($this->pager->getTotalResults() == 0) {
                    $this->pager->setTotalPages(0);
                } else {
                    $this->pager->setTotalResults(
                        $this->pager->getTotalResults() +
                            ($this->pager->getResultsPage() * ($this->pager->getCurrentPageIndex() - 1))
                    );
                    $this->pager->setTotalPages($this->pager->getCurrentPageIndex());
                }
            }
        }
        $data = new \aportela\DatabaseBrowserWrapper\BrowserResults($this->filter, $this->sort, $this->pager, $results);
        if ($this->afterBrowseFunction != null && is_callable($this->afterBrowseFunction)) {
            call_user_func($this->afterBrowseFunction, $data);
        }
        return ($data);
    }
}
