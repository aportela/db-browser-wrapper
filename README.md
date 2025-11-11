# db-browser-wrapper

Custom php (pdo) database element browsing wrapper

## Requirements

- mininum php version 8.4

## Limitations

At this time only SQLite | MariaDB | PostgreSQL are supported.

# install

```Shell
composer require "aportela/db-browser-wrapper"
```

# example

```php
<?php
    require ("vendor/autoload.php");

    // this will create a temporal sqlite database with an example schema, you DO NOT NEED THIS WITH A REAL DATABASE WITH DATA
    $databasePath = tempnam(sys_get_temp_dir(), 'sqlite');
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
    $upgradeSchemaPath = tempnam(sys_get_temp_dir(), 'sql');
    file_put_contents($upgradeSchemaPath, trim($upgradeSchema));
    // open database (SQLiTE)
    $dbh = new \aportela\DatabaseWrapper\DB(
        // change PDOSQLiteAdapter with (PDOMariaDBAdapter || PDOPostgreSQLAdapter) for connecting to (MariaDB || PostgreSQL) server
        new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(
            $databasePath,
            [
                    // Turn off persistent connections
                    \PDO::ATTR_PERSISTENT => false,
                    // Enable exceptions
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    // Emulate prepared statements
                    \PDO::ATTR_EMULATE_PREPARES => true,
                    // Set default fetch mode to array
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
            \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter::FLAGS_PRAGMA_JOURNAL_WAL,
            $upgradeSchemaPath,
        ),
        new \Psr\Log\NullLogger()
    );

    // query field definitions (name/alias => real field on sql query)
    // (the fields returned for each row)
    $fieldDefinitions = [
        "id" => "TABLEV1.id",
        "name" => "TABLEV1.name",
        "age" => "TABLEV1.age"
    ];
    // count query field definition (name/alias => real field on sql query)
    // (unique field returned on first row of count query)
    $fieldCountDefinition = [
        "totalResults" => "COUNT(TABLEV1.id)"
    ];
    // set current page = 1 with 2 results / page
    $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 2);
    // sort configuration
    $sort = new \aportela\DatabaseBrowserWrapper\Sort(
        [
            // sort by age DESC
            new \aportela\DatabaseBrowserWrapper\SortItem("age", \aportela\DatabaseBrowserWrapper\Order::DESC, false),
            // sort (secondary) by name ASCENDING (case insensitive)
            new \aportela\DatabaseBrowserWrapper\SortItem("name", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
        ]
    );
    $filter = new \aportela\DatabaseBrowserWrapper\Filter();
    // with this handler (OPTIONAL) we can modify each result item after getting the results
    // in this example we convert the age field to integer but anything can be done
    $afterBrowse = function ($data) {
        array_map(
            function ($item)  {
                $item->age = intval($item->age);
                return ($item);
            },
            $data->items
        );
    };
    $browser = new \aportela\DatabaseBrowserWrapper\Browser(self::$db, $this->fieldDefinitions, $this->fieldCountDefinition, $pager, $sort, $filter, $afterBrowse);
    // this is the main query for getting results data
    // first %s will be replaced with query fields block
    // second %s will be replaced with sort block
    // third %s will be replaced with pagination block
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
    // on data->items array we get "4|DOE|32" and "3|JOHN|24" items (the 2 first of page 1, ordering by age desc, name asc)
    $data = $browser->launch($query, $queryCount);
    // another query with params
    // set param :age with value 25
    $browser->addDBQueryParam(new \aportela\DatabaseWrapper\Param\IntegerParam(":age", 25));
    // build custom query with previous WHERE param
    $query = $browser->buildQuery(
        "
            SELECT %s FROM TABLEV1
            WHERE TABLEV1.age > :age
            %s
            %s
        "
    );
    $queryCount = $browser->buildQueryCount(
        "
            SELECT %s FROM TABLEV1
            WHERE TABLEV1.age > :age
        "
    );
    // on data->items array we get "4|DOE|32" (the only one with age > 25)
    $data = $browser->launch($query, $queryCount);
```

![PHP Composer](https://github.com/aportela/db-browser-wrapper/actions/workflows/php.yml/badge.svg)
