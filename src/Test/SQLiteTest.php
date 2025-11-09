<?php

declare(strict_types=1);

namespace aportela\DatabaseBrowserWrapper\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

#[\PHPUnit\Framework\Attributes\RequiresPhpExtension('pdo_sqlite')]
final class SQLiteTest extends \PHPUnit\Framework\TestCase
{
    private static \aportela\DatabaseWrapper\DB $db;

    private static string $databasePath;
    private static string $upgradeSchemaPath;

    /**
     * @var array<string, string>
     */
    private array $fieldDefinitions = [
        "id" => "TABLEV1.id",
        "name" => "TABLEV1.name",
        "age" => "TABLEV1.age"
    ];

    /**
     * @var array<string, string>
     */
    private array $fieldCountDefinition = [
        "totalResults" => "COUNT(TABLEV1.id)"
    ];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$databasePath = tempnam(sys_get_temp_dir(), 'sqlite');
        self::$upgradeSchemaPath = tempnam(sys_get_temp_dir(), 'sql');
        $upgradeSchema = "
            <?php
                return
                (
                    array
                    (
                        1 => array
                        (
                            \" CREATE TABLE IF NOT EXISTS TABLEV1 (id INTEGER PRIMARY KEY, name VARCHAR(128), age INTEGER); \",
                            \" INSERT INTO TABLEV1 VALUES (1, 'FOO', 8); \",
                            \" INSERT INTO TABLEV1 VALUES (2, 'BAR', 16); \",
                            \" INSERT INTO TABLEV1 VALUES (3, 'JOHN', 24); \",
                            \" INSERT INTO TABLEV1 VALUES (4, 'DOE', 32); \"
                        )
                    )
                );
        ";
        file_put_contents(self::$upgradeSchemaPath, trim($upgradeSchema));
        // main object
        self::$db = new \aportela\DatabaseWrapper\DB(
            new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(self::$databasePath, self::$upgradeSchemaPath, \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter::FLAGS_PRAGMA_JOURNAL_WAL),
            new \Psr\Log\NullLogger()
        );
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void
    {
        parent::setUp();
        if (!self::$db->isSchemaInstalled()) {
            self::$db->installSchema();
            self::$db->upgradeSchema(false);
        }
    }

    /**
     * Clean up the test case, called for every defined test
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        if (file_exists(self::$databasePath)) {
            unlink(self::$databasePath);
        }
    }

    public function testPaginationEnabled(): void
    {
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 2);
        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
            [
                new \aportela\DatabaseBrowserWrapper\SortItem("age", \aportela\DatabaseBrowserWrapper\Order::DESC, false),
                new \aportela\DatabaseBrowserWrapper\SortItem("name", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
            ]
        );
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(self::$db, $this->fieldDefinitions, $this->fieldCountDefinition, $pager, $sort, $filter);
        $query = $browser->buildQuery(
            "
                SELECT %s FROM TABLEV1
                %s
                %s
            "
        );
        $queryCount = $browser->buildQueryCount(
            "
                SELECT %s FROM TABLEV1

            "
        );
        $browserResults = $browser->launch($query, $queryCount);
        $this->assertEquals($browserResults->pager->getTotalResults(), 4);
        $this->assertEquals($browserResults->pager->getTotalPages(), 2);
        $this->assertCount(2, $browserResults->items);
        $this->assertEquals($browserResults->items[0]->id, 4);
        $this->assertEquals($browserResults->items[0]->name, "DOE");
        $this->assertEquals($browserResults->items[0]->age, 32);
        $this->assertEquals($browserResults->items[1]->id, 3);
        $this->assertEquals($browserResults->items[1]->name, "JOHN");
        $this->assertEquals($browserResults->items[1]->age, 24);
    }

    public function testPaginationEnabledNoQueryCountRequired(): void
    {
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, 2, 3);
        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
            [
                new \aportela\DatabaseBrowserWrapper\SortItem("age", \aportela\DatabaseBrowserWrapper\Order::DESC, false),
                new \aportela\DatabaseBrowserWrapper\SortItem("name", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
            ]
        );
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(self::$db, $this->fieldDefinitions, $this->fieldCountDefinition, $pager, $sort, $filter);
        $query = $browser->buildQuery(
            "
                SELECT %s FROM TABLEV1
                %s
                %s
            "
        );
        // in this "special case" (last page => totalPages = 2, currentPage == 2 && resultsPage == 3) we can avoid executing the count call against the database
        $queryCount = $browser->buildQueryCount(
            "
                SELECT %s FROM TABLEV1
            "
        );
        $browserResults = $browser->launch($query, $queryCount);
        $this->assertEquals($browserResults->pager->getTotalResults(), 4);
        $this->assertEquals($browserResults->pager->getTotalPages(), 2);
        $this->assertCount(1, $browserResults->items);
        $this->assertEquals($browserResults->items[0]->id, 1);
        $this->assertEquals($browserResults->items[0]->name, "FOO");
        $this->assertEquals($browserResults->items[0]->age, 8);
    }

    public function testPaginationDisabled(): void
    {
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(false);
        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
            [
                new \aportela\DatabaseBrowserWrapper\SortItem("id", \aportela\DatabaseBrowserWrapper\Order::ASC, false),
            ]
        );
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(self::$db, $this->fieldDefinitions, $this->fieldCountDefinition, $pager, $sort, $filter);
        $query = $browser->buildQuery(
            "
                SELECT %s FROM TABLEV1
                %s
                %s
            "
        );
        $browserResults = $browser->launch($query, "");
        $this->assertEquals($browserResults->pager->getTotalResults(), 4);
        $this->assertEquals($browserResults->pager->getTotalPages(), 1);
        $this->assertCount(4, $browserResults->items);
        $this->assertEquals($browserResults->items[0]->id, 1);
        $this->assertEquals($browserResults->items[0]->name, "FOO");
        $this->assertEquals($browserResults->items[0]->age, 8);
        $this->assertEquals($browserResults->items[1]->id, 2);
        $this->assertEquals($browserResults->items[1]->name, "BAR");
        $this->assertEquals($browserResults->items[1]->age, 16);
        $this->assertEquals($browserResults->items[2]->id, 3);
        $this->assertEquals($browserResults->items[2]->name, "JOHN");
        $this->assertEquals($browserResults->items[2]->age, 24);
        $this->assertEquals($browserResults->items[3]->id, 4);
        $this->assertEquals($browserResults->items[3]->name, "DOE");
        $this->assertEquals($browserResults->items[3]->age, 32);
    }

    public function testWithQueryParams(): void
    {
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(false);
        $sort = new \aportela\DatabaseBrowserWrapper\Sort();
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(self::$db, $this->fieldDefinitions, $this->fieldCountDefinition, $pager, $sort, $filter);
        $browser->addDBQueryParam(new \aportela\DatabaseWrapper\Param\IntegerParam(":id", 3));
        $query = $browser->buildQuery(
            "
                SELECT %s FROM TABLEV1
                WHERE id = :id
                %s
                %s
            "
        );
        $browserResults = $browser->launch($query, "");
        $this->assertEquals($browserResults->pager->getTotalResults(), 1);
        $this->assertEquals($browserResults->pager->getTotalPages(), 1);
        $this->assertCount(1, $browserResults->items);
        $this->assertEquals($browserResults->items[0]->id, 3);
        $this->assertEquals($browserResults->items[0]->name, "JOHN");
        $this->assertEquals($browserResults->items[0]->age, 24);
    }

    public function testWithRandomSort(): void
    {
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, 1);
        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
            [
                new \aportela\DatabaseBrowserWrapper\SortItemRandom()
            ]
        );
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(self::$db, $this->fieldDefinitions, $this->fieldCountDefinition, $pager, $sort, $filter);
        $browser->addDBQueryParam(new \aportela\DatabaseWrapper\Param\IntegerParam(":id", 3));
        $query = $browser->buildQuery(
            "
                SELECT %s FROM TABLEV1
                WHERE id = :id
                %s
                %s
            "
        );
        $browserResults = $browser->launch($query, "");
        $this->assertEquals($browserResults->pager->getTotalResults(), 1);
        $this->assertEquals($browserResults->pager->getTotalPages(), 1);
        $this->assertCount(1, $browserResults->items);
    }

    // this needs to be the final test
    public function testCloseAtEnd(): void
    {
        $this->expectNotToPerformAssertions();
        self::$db->close();
    }
}
