<?php

/**
 * @file
 * Database interface code for PostgreSQL database servers.
 */

/**
 * @ingroup database
 * @{
 */

class kxDBpgsql extends kxDB {

  public function __construct(array $connection_options = array()) {
    $this->transactionSupport = TRUE;

    // Transactional DDL is always available in PostgreSQL,
    $this->transactionalDDLSupport = TRUE;

    $pdo = parent::openConnection(array(
      PDO::ATTR_EMULATE_PREPARES => FALSE,
      // Convert numeric values to strings when fetching.
      PDO::ATTR_STRINGIFY_FETCHES => TRUE,
      // Force column names to lower case.
      PDO::ATTR_CASE => PDO::CASE_LOWER,
    ));

    parent::__construct($pdo);

    // Force PostgreSQL to use the UTF-8 character set by default.
    $pdo->exec("SET NAMES 'UTF8'");
  }

  public function query($query, array $args = array(), $options = array()) {

    $options += $this->defaultOptions();

    // The PDO PostgreSQL driver has a bug which
    // doesn't type cast booleans correctly when
    // parameters are bound using associative
    // arrays.
    // See http://bugs.php.net/bug.php?id=48383
    foreach ($args as &$value) {
      if (is_bool($value)) {
        $value = (int) $value;
      }
    }

    try {
      if ($query instanceof kxDBStatementInterface) {
        $stmt = $query;
        $stmt->execute(NULL, $options);
      }
      else {
        $this->expandArguments($query, $args);
        $stmt = $this->prepareQuery($query);
        $stmt->execute($args, $options);
      }

      switch ($options['return']) {
        case parent::RETURN_STATEMENT:
          return $stmt;
        case parent::RETURN_AFFECTED:
          return $stmt->rowCount();
        case parent::RETURN_INSERT_ID:
          return $this->lastInsertId($options['sequence_name']);
        case parent::RETURN_NULL:
          return;
        default:
          throw new PDOException('Invalid return directive: ' . $options['return']);
      }
    }
    catch (PDOException $e) {
      if ($options['throw_exception']) {
        // Add additional debug information.
        if ($query instanceof kxDBStatementInterface) {
          $e->query_string = $stmt->getQueryString();
        }
        else {
          $e->query_string = $query;
        }
        $e->args = $args;
        throw $e;
      }
      return NULL;
    }
  }

  public function queryRange($query, $from, $count, array $args = array(), array $options = array()) {
    return $this->query($query . ' LIMIT ' . (int) $count . ' OFFSET ' . (int) $from, $args, $options);
  }

  public function queryTemporary($query, array $args = array(), array $options = array()) {
    $tablename = $this->generateTemporaryTableName();
    $this->query(preg_replace('/^SELECT/i', 'CREATE TEMPORARY TABLE {' . $tablename . '} AS SELECT', $query), $args, $options);
    return $tablename;
  }

  public function driver() {
    return 'pgsql';
  }

  public function databaseType() {
    return 'pgsql';
  }

  public function mapConditionOperator($operator) {
    static $specials;

    // Function calls not allowed in static declarations, thus this method.
    if (!isset($specials)) {
      $specials = array(
        // In PostgreSQL, 'LIKE' is case-sensitive. For case-insensitive LIKE
        // statements, we need to use ILIKE instead. Use backslash for escaping
        // wildcard characters.
        'LIKE' => array('operator' => 'ILIKE', 'postfix' => ' ESCAPE ' . $this->connection->quote("\\")),
        'NOT LIKE' => array('operator' => 'NOT ILIKE', 'postfix' => ' ESCAPE ' . $this->connection->quote("\\")),
      );
    }

    return isset($specials[$operator]) ? $specials[$operator] : NULL;
  }
}

/**
 * @} End of "ingroup database".
 */
