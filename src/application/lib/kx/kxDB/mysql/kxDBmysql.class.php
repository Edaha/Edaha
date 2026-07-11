<?php

/**
 * @file
 * Database interface code for MySQL database servers.
 */

/**
 * @ingroup database
 *
 * @{
 */
class kxDBmysql extends kxDB
{
    public function __construct(array $connection_options = [])
    {
        $this->transactionSupport = false;

        // MySQL never supports transactional DDL.
        $this->transactionalDDLSupport = false;

        $pdo = parent::openConnection([
            // So we don't have to mess around with cursors and unbuffered queries by default.
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            // Because MySQL's prepared statements skip the query cache, because it's dumb.
            PDO::ATTR_EMULATE_PREPARES => true,
            // Force column names to lower case.
            PDO::ATTR_CASE => PDO::CASE_LOWER,
        ]);

        parent::__construct($pdo);

        // Force MySQL to use the UTF-8 character set.
        $pdo->exec('SET NAMES utf8');

        // Force MySQL's behavior to conform more closely to SQL standards.
        // This allows Drupal to run almost seamlessly on many different
        // kinds of database systems. These settings force MySQL to behave
        // the same as postgresql, or sqlite in regards to syntax interpretation
        // and invalid data handling. See http://drupal.org/node/344575 for further discussion.
        $pdo->exec("SET sql_mode='ANSI,TRADITIONAL'");
    }

    public function queryRange($query, $from, $count, array $args = [], array $options = [])
    {
        return $this->query($query.' LIMIT '.(int) $from.', '.(int) $count, $args, $options);
    }

    public function queryTemporary($query, array $args = [], array $options = [])
    {
        $tablename = $this->generateTemporaryTableName();
        $this->query(preg_replace('/^SELECT/i', 'CREATE TEMPORARY TABLE {'.$tablename.'} Engine=MEMORY SELECT', $query), $args, $options);

        return $tablename;
    }

    public function driver()
    {
        return 'mysql';
    }

    public function databaseType()
    {
        return 'mysql';
    }

    public function mapConditionOperator($operator)
    {
        // We don't want to override any of the defaults.
        return null;
    }
}

// @} End of "ingroup database".
