<?php

abstract class kxDB {
    private static $instance = null;
    
    /**
     * Flag to indicate a query call should simply return NULL.
     *
     * This is used for queries that have no reasonable return value anyway, such
     * as INSERT statements to a table without a serial primary key.
     */
    const RETURN_NULL = 0;
    
    /**
     * Flag to indicate a query call should return the prepared statement.
     */
    const RETURN_STATEMENT = 1;
    
    /**
     * Flag to indicate a query call should return the number of affected rows.
     */
    const RETURN_AFFECTED = 2;
    
    /**
     * Flag to indicate a query call should return the "last insert id".
     */
    const RETURN_INSERT_ID = 3;
    
    /**
     * The PDO connection
     * 
     * @var PDO
     */
    protected $connection;

    /**
     * Tracks the number of "layers" of transactions currently active.
     *
     * On many databases transactions cannot nest.  Instead, we track
     * nested calls to transactions and collapse them into a single
     * transaction.
     *
     * @var array
     */
    protected $transactionLayers = array();
    
    /**
     * Index of what driver-specific class to use for various operations.
     *
     * @var array
     */
    protected $driverClasses = array();
    
    /**
     * The name of the Statement class for this connection.
     *
     * @var string
     */
    protected $statementClass = 'kxDBStatementBase';
    
    /**
     * Whether this database connection supports transactions.
     *
     * @var bool
     */
    protected $transactionSupport = TRUE;
    
    /**
     * Whether this database connection supports transactional DDL.
     *
     * Set to FALSE by default because few databases support this feature.
     *
     * @var bool
     */
    protected $transactionalDDLSupport = FALSE;
    
    /**
     * An index used to generate unique temporary table names.
     *
     * @var integer
     */
    protected $temporaryNameIndex = 0;
    
    /**
     * The connection information for this connection object.
     *
     * @var array
     */
    protected $connectionOptions = array();
    
    /**
     * The schema object for this connection.
     *
     * @var object
     */
    protected $schema = NULL;
    
    /**
     * The table prefix used by this database connection.
     *
     * @var string
     */
    protected $prefix = '';
    
    protected function __construct(PDO $pdo) {
        $this->connection = $pdo;
        $this->setPrefix(kxEnv::get('kx:db:prefix'));
        if (!empty($this->statementClass)) {
            $this->connection->setAttribute(PDO::ATTR_STATEMENT_CLASS, array($this->statementClass, array($this)));
        }
    }
    private static function initialize() {
        $driver_class = 'kxDB' .  substr(kxEnv::get('kx:db:dsn'), 0, strpos(kxEnv::get('kx:db:dsn'), ':'));
        $new_connection = new $driver_class();
        self::$instance = $new_connection;
    }
    final public static function openConnection($driver_options) {
        $driver_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        try {
            $pdo = new PDO(
                kxEnv::get('kx:db:dsn', 'mysql:host=db;dbname=edaha;charset=utf8mb4'),
                kxEnv::get('kx:db:username', 'edaha'),
                kxEnv::get('kx:db:password', 'edaha'),
                $driver_options
            );
        } catch(PDOException $e) {
            throw new kxPDOException($e->getMessage());
        }
        return $pdo;
    }
    
    /**
     * Returns the default query options for any given query.
     *
     * A given query can be customized with a number of option flags in an
     * associative array:
     * - fetch: This element controls how rows from a result set will be
     *   returned. Legal values include PDO::FETCH_ASSOC, PDO::FETCH_BOTH,
     *   PDO::FETCH_OBJ, PDO::FETCH_NUM, or a string representing the name of a
     *   class. If a string is specified, each record will be fetched into a new
     *   object of that class. The behavior of all other values is defined by PDO.
     *   See http://www.php.net/PDOStatement-fetch
     * - return: Depending on the type of query, different return values may be
     *   meaningful. This directive instructs the system which type of return
     *   value is desired. The system will generally set the correct value
     *   automatically, so it is extremely rare that a module developer will ever
     *   need to specify this value. Setting it incorrectly will likely lead to
     *   unpredictable results or fatal errors. Legal values include:
     *   - kxDB::RETURN_STATEMENT: Return the prepared statement object for
     *     the query. This is usually only meaningful for SELECT queries, where
     *     the statement object is how one accesses the result set returned by the
     *     query.
     *   - kxDB::RETURN_AFFECTED: Return the number of rows affected by an
     *     UPDATE or DELETE query. Be aware that means the number of rows actually
     *     changed, not the number of rows matched by the WHERE clause.
     *   - kxDB::RETURN_INSERT_ID: Return the sequence ID (primary key)
     *     created by an INSERT statement on a table that contains a serial
     *     column.
     *   - kxDB::RETURN_NULL: Do not return anything, as there is no
     *     meaningful value to return. That is the case for INSERT queries on
     *     tables that do not contain a serial column.
     * - throw_exception: By default, the database system will catch any errors
     *   on a query as an Exception, log it, and then rethrow it so that code
     *   further up the call chain can take an appropriate action. To suppress
     *   that behavior and simply return NULL on failure, set this option to
     *   FALSE.
     *
     * @return
     *   An array of default query options.
     */
    protected function defaultOptions() {
        return array(
            'fetch' => PDO::FETCH_OBJ,
            'return' => self::RETURN_STATEMENT,
            'throw_exception' => TRUE,
        );
    }
    
    /**
     * Preprocess the prefixes used by this database connection.
     *
     * @param $prefix
     *   The prefixes, in any of the multiple forms documented in
     *   default.settings.php.
     */
    protected function setPrefix($prefix) {
        $this->prefix = $prefix;
    }
    
    /**
     * Appends a database prefix to all tables in a query.
     *
     * Queries sent to Drupal should wrap all table names in curly brackets. This
     * function searches for this syntax and adds Drupal's table prefix to all
     * tables, allowing Drupal to coexist with other systems in the same database
     * and/or schema if necessary.
     *
     * @param $sql
     *   A string containing a partial or entire SQL query.
     *
     * @return
     *   The properly-prefixed string.
     */
    public function prefixTables($sql) {
        return strtr($sql, array('{' => $this->prefix , '}' => ''));
    }
    
    /**
     * Find the prefix for a table.
     *
     * This function is for when you want to know the prefix of a table. This
     * is not used in prefixTables due to performance reasons.
     */
    public function tablePrefix($table = 'default') {
        return $this->prefix;
    }
    
    /**
     * Prepares a query string and returns the prepared statement.
     *
     * This method caches prepared statements, reusing them when
     * possible. It also prefixes tables names enclosed in curly-braces.
     *
     * @param $query
     *   The query string as SQL, with curly-braces surrounding the
     *   table names.
     *
     * @return kxDBStatementInterface
     *   A PDO prepared statement ready for its execute() method.
     */
    public function prepareQuery($query) {
		//echo "<br />".$query;
        return $this->prepare($query);
    }
    
    public function prepare($query) {
        $query = $this->prefixTables($query);
        
        // Call PDO::prepare.
        return self::getInstance()->getPDO()->prepare($query);
    }
    
    /**
     * Creates the appropriate sequence name for a given table and serial field.
     *
     * This information is exposed to all database drivers, although it is only
     * useful on some of them. This method is table prefix-aware.
     *
     * @param $table
     *   The table name to use for the sequence.
     * @param $field
     *   The field name to use for the sequence.
     *
     * @return
     *   A table prefix-parsed string for the sequence name.
     */
    public function makeSequenceName($table, $field) {
        return $this->prefixTables('{' . $table . '}_' . $field . '_seq');
    }
    
    /**
     * Executes a query string against the database.
     *
     * This method provides a central handler for the actual execution of every
     * query. All queries executed by Drupal are executed as PDO prepared
     * statements.
     *
     * @param $query
     *   The query to execute. In most cases this will be a string containing
     *   an SQL query with placeholders. An already-prepared instance of
     *   kxDBStatementInterface may also be passed in order to allow calling
     *   code to manually bind variables to a query. If a
     *   kxDBStatementInterface is passed, the $args array will be ignored.
     *   It is extremely rare that module code will need to pass a statement
     *   object to this method. It is used primarily for database drivers for
     *   databases that require special LOB field handling.
     * @param $args
     *   An array of arguments for the prepared statement. If the prepared
     *   statement uses ? placeholders, this array must be an indexed array.
     *   If it contains named placeholders, it must be an associative array.
     * @param $options
     *   An associative array of options to control how the query is run. See
     *   the documentation for kxDB::defaultOptions() for details.
     *
     * @return kxDBStatementInterface
     *   This method will return one of: the executed statement, the number of
     *   rows affected by the query (not the number matched), or the generated
     *   insert IT of the last query, depending on the value of
     *   $options['return']. Typically that value will be set by default or a
     *   query builder and should not be set by a user. If there is an error,
     *   this method will return NULL and may throw an exception if
     *   $options['throw_exception'] is TRUE.
     *
     * @throws PDOException
     */
    public function query($query, array $args = array(), $options = array()) {
        
        // Use default values if not already set.
        $options += $this->defaultOptions();
        
        try {
            // We allow either a pre-bound statement object or a literal string.
            // In either case, we want to end up with an executed statement object,
            // which we pass to PDOStatement::execute.
            if ($query instanceof kxDBStatementInterface) {
                $stmt = $query;
                $stmt->execute(NULL, $options);
            }
            else {
                $this->expandArguments($query, $args);
                $stmt = $this->prepareQuery($query);
				//echo "<pre>";
				//print_r($stmt);
				//echo "</pre>";
                $stmt->execute($args, $options);
            }
            
            // Depending on the type of query we may need to return a different value.
            // See kxDB::defaultOptions() for a description of each
            // value.
            switch ($options['return']) {
                case self::RETURN_STATEMENT:
                    return $stmt;
                case self::RETURN_AFFECTED:
                    return $stmt->rowCount();
                case self::RETURN_INSERT_ID:
                    return $this->getPDO()->lastInsertId();
                case self::RETURN_NULL:
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
    
    /**
     * Expands out shorthand placeholders.
     *
     * Drupal supports an alternate syntax for doing arrays of values. We
     * therefore need to expand them out into a full, executable query string.
     *
     * @param $query
     *   The query string to modify.
     * @param $args
     *   The arguments for the query.
     *
     * @return
     *   TRUE if the query was modified, FALSE otherwise.
     */
    protected function expandArguments(&$query, &$args) {
        $modified = FALSE;
        
        // If the placeholder value to insert is an array, assume that we need
        // to expand it out into a comma-delimited set of placeholders.
        foreach (array_filter($args, 'is_array') as $key => $data) {
            $new_keys = array();
            foreach ($data as $i => $value) {
                // This assumes that there are no other placeholders that use the same
                // name.  For example, if the array placeholder is defined as :example
                // and there is already an :example_2 placeholder, this will generate
                // a duplicate key.  We do not account for that as the calling code
                // is already broken if that happens.
                $new_keys[$key . '_' . $i] = $value;
            }
            
            // Update the query with the new placeholders.
            // preg_replace is necessary to ensure the replacement does not affect
            // placeholders that start with the same exact text. For example, if the
            // query contains the placeholders :foo and :foobar, and :foo has an
            // array of values, using str_replace would affect both placeholders,
            // but using the following preg_replace would only affect :foo because
            // it is followed by a non-word character.
            $query = preg_replace('#' . $key . '\b#', implode(', ', array_keys($new_keys)), $query);
            
            // Update the args array with the new placeholders.
            unset($args[$key]);
            $args += $new_keys;
            
            $modified = TRUE;
        }
        
        return $modified;
    }
    
    /**
     * Gets the driver-specific override class if any for the specified class.
     *
     * @param string $class
     *   The class for which we want the potentially driver-specific class.
     * @param array $files
     *   The name of the files in which the driver-specific class can be.
     * @param $use_autoload
     *   If TRUE, attempt to load classes using PHP's autoload capability
     *   as well as the manual approach here.
     * @return string
     *   The name of the class that should be used for this driver.
     */
    public function getDriverClass($class, array $files = array(), $use_autoload = FALSE) {
        if (empty($this->driverClasses[$class])) {
            $driver = $this->driver();
            $this->driverClasses[$class] = $class . '_' . $driver;
            $this->loadDriverFile($driver, $files);
            if (!class_exists($this->driverClasses[$class], $use_autoload)) {
                $this->driverClasses[$class] = $class;
            }
        }
        return $this->driverClasses[$class];
    }
    
    /**
     * Returns a new DatabaseCondition.
     * @param string $conjunction
     *   The operator to use to combine conditions: 'AND' or 'OR'.
     * @return DatabaseCondition
     */
    public function condition($conjuction = "AND") {
        return new DatabaseCondition($conjunction);
    }
    
    /**
     * Prepares and returns a SELECT query object.
     *
     * @param $table
     *   The base table for this query, that is, the first table in the FROM
     *   clause. This table will also be used as the "base" table for query_alter
     *   hook implementations.
     * @param $alias
     *   The alias of the base table of this query.
     * @param $options
     *   An array of options on the query.
     *
     * @return SelectQueryInterface
     *   An appropriate SelectQuery object for this database connection. Note that
     *   it may be a driver-specific subclass of SelectQuery, depending on the
     *   driver.
     *
     * @see SelectQuery
     */
    public function select($table, $alias = NULL, array $options = array()) {
        $class = $this->getDriverClass('SelectQuery', array('query.php', 'select.php'));
        return new $class($table, $alias, $this, $options);
    }
    
    /**
     * Prepares and returns an INSERT query object.
     *
     * @param $options
     *   An array of options on the query.
     *
     * @return InsertQuery
     *   A new InsertQuery object.
     *
     * @see InsertQuery
     */
    public function insert($table, array $options = array()) {
        $class = $this->getDriverClass('InsertQuery', array('query.php'));
        return new $class($this, $table, $options);
    }
    
    /**
     * Prepares and returns a MERGE query object.
     *
     * @param $options
     *   An array of options on the query.
     *
     * @return MergeQuery
     *   A new MergeQuery object.
     *
     * @see MergeQuery
     */
    public function merge($table, array $options = array()) {
        $class = $this->getDriverClass('MergeQuery', array('query.php'));
        return new $class($this, $table, $options);
    }
    
    
    /**
     * Prepares and returns an UPDATE query object.
     *
     * @param $options
     *   An array of options on the query.
     *
     * @return UpdateQuery
     *   A new UpdateQuery object.
     *
     * @see UpdateQuery
     */
    public function update($table, array $options = array()) {
        $class = $this->getDriverClass('UpdateQuery', array('query.php'));
        return new $class($this, $table, $options);
    }
    
    /**
     * Prepares and returns a DELETE query object.
     *
     * @param $options
     *   An array of options on the query.
     *
     * @return DeleteQuery
     *   A new DeleteQuery object.
     *
     * @see DeleteQuery
     */
    public function delete($table, array $options = array()) {
        $class = $this->getDriverClass('DeleteQuery', array('query.php'));
        return new $class($this, $table, $options);
    }
    
    /**
     * Prepares and returns a TRUNCATE query object.
     *
     * @param $options
     *   An array of options on the query.
     *
     * @return TruncateQuery
     *   A new TruncateQuery object.
     *
     * @see TruncateQuery
     */
    public function truncate($table, array $options = array()) {
        $class = $this->getDriverClass('TruncateQuery', array('query.php'));
        return new $class($this, $table, $options);
    }
    
    /**
     * Returns a DatabaseSchema object for manipulating the schema.
     *
     * This method will lazy-load the appropriate schema library file.
     *
     * @return DatabaseSchema
     *   The DatabaseSchema object for this connection.
     */
    public function schema() {
        if (empty($this->schema)) {
            $class = $this->getDriverClass('DatabaseSchema', array('schema.php'));
            if (class_exists($class)) {
                $this->schema = new $class($this);
            }
        }
        return $this->schema;
    }
    
    /**
     * Escapes a table name string.
     *
     * Force all table names to be strictly alphanumeric-plus-underscore.
     * For some database drivers, it may also wrap the table name in
     * database-specific escape characters.
     *
     * @return
     *   The sanitized table name string.
     */
    public function escapeTable($table) {
        return preg_replace('/[^A-Za-z0-9_.]+/', '', $table);
    }
    
    /**
     * Escapes a field name string.
     *
     * Force all field names to be strictly alphanumeric-plus-underscore.
     * For some database drivers, it may also wrap the field name in
     * database-specific escape characters.
     *
     * @return
     *   The sanitized field name string.
     */
    public function escapeField($field) {
        return preg_replace('/[^A-Za-z0-9_.]+/', '', $field);
    }
    
    /**
     * Escapes an alias name string.
     *
     * Force all alias names to be strictly alphanumeric-plus-underscore. In
     * contrast to kxDB::escapeField() /
     * kxDB::escapeTable(), this doesn't allow the period (".")
     * because that is not allowed in aliases.
     *
     * @return
     *   The sanitized field name string.
     */
    public function escapeAlias($field) {
        return preg_replace('/[^A-Za-z0-9_]+/', '', $field);
    }
    
    /**
     * Escapes characters that work as wildcard characters in a LIKE pattern.
     *
     * The wildcard characters "%" and "_" as well as backslash are prefixed with
     * a backslash. Use this to do a search for a verbatim string without any
     * wildcard behavior.
     *
     * For example, the following does a case-insensitive query for all rows whose
     * name starts with $prefix:
     * @code
     * $result = db_query(
     *   'SELECT * FROM person WHERE name LIKE :pattern',
     *   array(':pattern' => db_like($prefix) . '%')
     * );
     * @endcode
     *
     * Backslash is defined as escape character for LIKE patterns in
     * DatabaseCondition::mapConditionOperator().
     *
     * @param $string
     *   The string to escape.
     *
     * @return
     *   The escaped string.
     */
    public function escapeLike($string) {
        return addcslashes($string, '\%_');
    }
    
    /**
     * Determines if there is an active transaction open.
     *
     * @return
     *   TRUE if we're currently in a transaction, FALSE otherwise.
     */
    public function inTransaction() {
        return ($this->transactionDepth() > 0);
    }
    
    /**
     * Determines current transaction depth.
     */
    public function transactionDepth() {
        return count($this->transactionLayers);
    }
    
    /**
     * Returns a new DatabaseTransaction object on this connection.
     *
     * @param $name
     *   Optional name of the savepoint.
     *
     * @see DatabaseTransaction
     */
    public function startTransaction($name = '') {
        $class = $this->getDriverClass('DatabaseTransaction');
        return new $class($this, $name);
    }
    
    /**
     * Rolls back the transaction entirely or to a named savepoint.
     *
     * This method throws an exception if no transaction is active.
     *
     * @param $savepoint_name
     *   The name of the savepoint. The default, 'drupal_transaction', will roll
     *   the entire transaction back.
     *
     * @throws DatabaseTransactionNoActiveException
     *
     * @see DatabaseTransaction::rollback()
     */
    public function rollback($savepoint_name = 'drupal_transaction') {
        if (!$this->supportsTransactions()) {
            return;
        }
        if (!$this->inTransaction()) {
            throw new DatabaseTransactionNoActiveException();
        }
        // A previous rollback to an earlier savepoint may mean that the savepoint
        // in question has already been rolled back.
        if (!in_array($savepoint_name, $this->transactionLayers)) {
            return;
        }
        
        // We need to find the point we're rolling back to, all other savepoints
        // before are no longer needed.
        while ($savepoint = array_pop($this->transactionLayers)) {
            if ($savepoint == $savepoint_name) {
                // If it is the last the transaction in the stack, then it is not a
                // savepoint, it is the transaction itself so we will need to roll back
                // the transaction rather than a savepoint.
                if (empty($this->transactionLayers)) {
                    break;
                }
                $this->query('ROLLBACK TO SAVEPOINT ' . $savepoint);
                return;
            }
        }
        self::getInstance()->getPDO()->rollBack();
    }
    
    /**
     * Increases the depth of transaction nesting.
     *
     * If no transaction is already active, we begin a new transaction.
     *
     * @throws DatabaseTransactionNameNonUniqueException
     *
     * @see DatabaseTransaction
     */
    public function pushTransaction($name) {
        if (!$this->supportsTransactions()) {
            return;
        }
        if (isset($this->transactionLayers[$name])) {
            throw new DatabaseTransactionNameNonUniqueException($name . " is already in use.");
        }
        // If we're already in a transaction then we want to create a savepoint
        // rather than try to create another transaction.
        if ($this->inTransaction()) {
            $this->query('SAVEPOINT ' . $name);
        }
        else {
            self::getInstance()->getPDO()->beginTransaction();
        }
        $this->transactionLayers[$name] = $name;
    }
    
    /**
     * Decreases the depth of transaction nesting.
     *
     * If we pop off the last transaction layer, then we either commit or roll
     * back the transaction as necessary. If no transaction is active, we return
     * because the transaction may have manually been rolled back.
     *
     * @param $name
     *   The name of the savepoint
     *
     * @throws DatabaseTransactionNoActiveException
     * @throws DatabaseTransactionCommitFailedException
     *
     * @see DatabaseTransaction
     */
    public function popTransaction($name) {
        if (!$this->supportsTransactions()) {
            return;
        }
        if (!$this->inTransaction()) {
            throw new DatabaseTransactionNoActiveException();
        }
        
        // Commit everything since SAVEPOINT $name.
        while($savepoint = array_pop($this->transactionLayers)) {
            if ($savepoint != $name) continue;
            
            // If there are no more layers left then we should commit.
            if (empty($this->transactionLayers)) {
                if (!self::getInstance()->getPDO()->commit()) {
                    throw new DatabaseTransactionCommitFailedException();
                }
            }
            else {
                $this->query('RELEASE SAVEPOINT ' . $name);
                break;
            }
        }
    }
    
    /**
     * Runs a limited-range query on this database object.
     *
     * Use this as a substitute for ->query() when a subset of the query is to be
     * returned. User-supplied arguments to the query should be passed in as
     * separate parameters so that they can be properly escaped to avoid SQL
     * injection attacks.
     *
     * @param $query
     *   A string containing an SQL query.
     * @param $args
     *   An array of values to substitute into the query at placeholder markers.
     * @param $from
     *   The first result row to return.
     * @param $count
     *   The maximum number of result rows to return.
     * @param $options
     *   An array of options on the query.
     *
     * @return kxDBStatementInterface
     *   A database query result resource, or NULL if the query was not executed
     *   correctly.
     */
    abstract public function queryRange($query, $from, $count, array $args = array(), array $options = array());
    
    /**
     * Generates a temporary table name.
     *
     * @return
     *   A table name.
     */
    protected function generateTemporaryTableName() {
        return "db_temporary_" . $this->temporaryNameIndex++;
    }
    
    /**
     * Runs a SELECT query and stores its results in a temporary table.
     *
     * Use this as a substitute for ->query() when the results need to stored
     * in a temporary table. Temporary tables exist for the duration of the page
     * request. User-supplied arguments to the query should be passed in as
     * separate parameters so that they can be properly escaped to avoid SQL
     * injection attacks.
     *
     * Note that if you need to know how many results were returned, you should do
     * a SELECT COUNT(*) on the temporary table afterwards.
     *
     * @param $query
     *   A string containing a normal SELECT SQL query.
     * @param $args
     *   An array of values to substitute into the query at placeholder markers.
     * @param $options
     *   An associative array of options to control how the query is run. See
     *   the documentation for kxDB::defaultOptions() for details.
     *
     * @return
     *   The name of the temporary table.
     */
    abstract function queryTemporary($query, array $args = array(), array $options = array());
    
    /**
     * Returns the type of database driver.
     *
     * This is not necessarily the same as the type of the database itself. For
     * instance, there could be two MySQL drivers, mysql and mysql_mock. This
     * function would return different values for each, but both would return
     * "mysql" for databaseType().
     */
    abstract public function driver();
    
    /**
     * Returns the version of the database server.
     */
    public function version() {
        return self::getPDO()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
    
    /**
     * Determines if this driver supports transactions.
     *
     * @return
     *   TRUE if this connection supports transactions, FALSE otherwise.
     */
    public function supportsTransactions() {
        return $this->transactionSupport;
    }
    
    /**
     * Determines if this driver supports transactional DDL.
     *
     * DDL queries are those that change the schema, such as ALTER queries.
     *
     * @return
     *   TRUE if this connection supports transactions for DDL queries, FALSE
     *   otherwise.
     */
    public function supportsTransactionalDDL() {
        return $this->transactionalDDLSupport;
    }
    
    /**
     * Returns the name of the PDO driver for this connection.
     */
    abstract public function databaseType();
    
    
    /**
     * Gets any special processing requirements for the condition operator.
     *
     * Some condition types require special processing, such as IN, because
     * the value data they pass in is not a simple value. This is a simple
     * overridable lookup function. Database connections should define only
     * those operators they wish to be handled differently than the default.
     *
     * @param $operator
     *   The condition operator, such as "IN", "BETWEEN", etc. Case-sensitive.
     *
     * @return
     *   The extra handling directives for the specified operator, or NULL.
     *
     * @see DatabaseCondition::compile()
     */
    abstract public function mapConditionOperator($operator);
    
    /**
     * Throws an exception to deny direct access to transaction commits.
     *
     * We do not want to allow users to commit transactions at any time, only
     * by destroying the transaction object or allowing it to go out of scope.
     * A direct commit bypasses all of the safety checks we've built on top of
     * PDO's transaction routines.
     *
     * @throws DatabaseTransactionExplicitCommitNotAllowedException
     *
     * @see DatabaseTransaction
     */
    public function commit() {
        throw new DatabaseTransactionExplicitCommitNotAllowedException();
    }
    /**
     * Load a file for the database that might hold a class.
     *
     * @param $driver
     *   The name of the driver.
     * @param array $files
     *   The name of the files the driver specific class can be.
     */
    final public static function loadDriverFile($driver, array $files = array()) {
        static $base_path;
        
        if (empty($base_path)) {
            $base_path = dirname(realpath(__FILE__));
        }
        
        $driver_base_path = "$base_path/$driver";
        foreach ($files as $file) {
            // Load the base file first so that classes extending base classes will
            // have the base class loaded.
            foreach (array("$base_path/$file", "$driver_base_path/$file") as $filename) {
                // The OS caches file_exists() and PHP caches require_once(), so
                // we'll let both of those take care of performance here.
                if (file_exists($filename)) {
                    require_once $filename;
                }
            }
        }
    }
    
    final public static function getInstance() {
        if(!self::$instance instanceof kxDB) self::initialize();
        
        return self::$instance;
    }
    
    final public function getPDO() {
        return $this->connection;
    }
}

/**
 * Exception for when popTransaction() is called with no active transaction.
 */
class DatabaseTransactionNoActiveException extends Exception { }

/**
 * Exception thrown when a savepoint or transaction name occurs twice.
 */
class DatabaseTransactionNameNonUniqueException extends Exception { }

/**
 * Exception thrown when a commit() function fails.
 */
class DatabaseTransactionCommitFailedException extends Exception { }

/**
 * Exception to deny attempts to explicitly manage transactions.
 *
 * This exception will be thrown when the PDO connection commit() is called.
 * Code should never call this method directly.
 */
class DatabaseTransactionExplicitCommitNotAllowedException extends Exception { }

/**
 * Exception thrown for merge queries that do not make semantic sense.
 *
 * There are many ways that a merge query could be malformed.  They should all
 * throw this exception and set an appropriately descriptive message.
 */
class InvalidMergeQueryException extends Exception {}

/**
 * Exception thrown if an insert query specifies a field twice.
 *
 * It is not allowed to specify a field as default and insert field, this
 * exception is thrown if that is the case.
 */
class FieldsOverlapException extends Exception {}

/**
 * Exception thrown if an insert query doesn't specify insert or default fields.
 */
class NoFieldsException extends Exception {}

/**
 * Exception thrown if an undefined database connection is requested.
 */
class kxDBNotDefinedException extends Exception {}

/**
 * Exception thrown if no driver is specified for a database connection.
 */
class DatabaseDriverNotSpecifiedException extends Exception {}


/**
 * A wrapper class for creating and managing database transactions.
 *
 * Not all databases or database configurations support transactions. For
 * example, MySQL MyISAM tables do not. It is also easy to begin a transaction
 * and then forget to commit it, which can lead to connection errors when
 * another transaction is started.
 *
 * This class acts as a wrapper for transactions. To begin a transaction,
 * simply instantiate it. When the object goes out of scope and is destroyed
 * it will automatically commit. It also will check to see if the specified
 * connection supports transactions. If not, it will simply skip any transaction
 * commands, allowing user-space code to proceed normally. The only difference
 * is that rollbacks won't actually do anything.
 *
 * In the vast majority of cases, you should not instantiate this class
 * directly. Instead, call ->startTransaction(), from the appropriate connection
 * object.
 */
class DatabaseTransaction {
    
    /**
     * The connection object for this transaction.
     *
     * @var kxDB
     */
    protected $connection;
    
    /**
     * A boolean value to indicate whether this transaction has been rolled back.
     *
     * @var Boolean
     */
    protected $rolledBack = FALSE;
    
    /**
     * The name of the transaction.
     *
     * This is used to label the transaction savepoint. It will be overridden to
     * 'drupal_transaction' if there is no transaction depth.
     */
    protected $name;
    
    public function __construct(kxDB &$connection, $name = NULL) {
        $this->connection = &$connection;
        // If there is no transaction depth, then no transaction has started. Name
        // the transaction 'drupal_transaction'.
        if (!$depth = $connection->transactionDepth()) {
            $this->name = 'drupal_transaction';
        }
        // Within transactions, savepoints are used. Each savepoint requires a
        // name. So if no name is present we need to create one.
        elseif (!$name) {
            $this->name = 'savepoint_' . $depth;
        }
        else {
            $this->name = $name;
        }
        $this->connection->pushTransaction($this->name);
    }
    
    public function __destruct() {
        // If we rolled back then the transaction would have already been popped.
        if ($this->connection->inTransaction() && !$this->rolledBack) {
            $this->connection->popTransaction($this->name);
        }
    }
    
    /**
     * Retrieves the name of the transaction or savepoint.
     */
    public function name() {
        return $this->name;
    }
    
    /**
     * Rolls back the current transaction.
     *
     * This is just a wrapper method to rollback whatever transaction stack we are
     * currently in, which is managed by the connection object itself. Note that
     * logging (preferable with watchdog_exception()) needs to happen after a
     * transaction has been rolled back or the log messages will be rolled back
     * too.
     *
     * @see kxDB::rollback()
     * @see watchdog_exception()
     */
    public function rollback() {
        $this->rolledBack = TRUE;
        $this->connection->rollback($this->name);
    }
}

/**
 * A prepared statement.
 *
 * Some methods in that class are purposely commented out. Due to a change in
 * how PHP defines PDOStatement, we can't define a signature for those methods
 * that will work the same way between versions older than 5.2.6 and later
 * versions.
 *
 * Please refer to http://bugs.php.net/bug.php?id=42452 for more details.
 *
 * Child implementations should either extend PDOStatement:
 * @code
 * class kxDBStatement_oracle extends PDOStatement implements kxDBStatementInterface {}
 * @endcode
 * or implement their own class, but in that case they will also have to
 * implement the Iterator or IteratorArray interfaces before
 * kxDBStatementInterface:
 * @code
 * class kxDBStatement_oracle implements Iterator, kxDBStatementInterface {}
 * @endcode
 */
interface kxDBStatementInterface extends Traversable {
    
    /**
     * Executes a prepared statement
     *
     * @param $args
     *   An array of values with as many elements as there are bound parameters in
     *   the SQL statement being executed.
     * @param $options
     *   An array of options for this query.
     *
     * @return
     *   TRUE on success, or FALSE on failure.
     */
    public function execute($args = array(), $options = array());
    
    /**
     * Gets the query string of this statement.
     *
     * @return
     *   The query string, in its form with placeholders.
     */
    public function getQueryString();
    
    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return
     *   The number of rows affected by the last DELETE, INSERT, or UPDATE
     *   statement executed.
     */
    public function rowCount();
    
    /**
     * Sets the default fetch mode for this statement.
     *
     * See http://php.net/manual/en/pdo.constants.php for the definition of the
     * constants used.
     *
     * @param $mode
     *   One of the PDO::FETCH_* constants.
     * @param $a1
     *   An option depending of the fetch mode specified by $mode:
     *   - for PDO::FETCH_COLUMN, the index of the column to fetch
     *   - for PDO::FETCH_CLASS, the name of the class to create
     *   - for PDO::FETCH_INTO, the object to add the data to
     * @param $a2
     *   If $mode is PDO::FETCH_CLASS, the optional arguments to pass to the
     *   constructor.
     */
    // public function setFetchMode($mode, $a1 = NULL, $a2 = array());
    
    /**
     * Fetches the next row from a result set.
     *
     * See http://php.net/manual/en/pdo.constants.php for the definition of the
     * constants used.
     *
     * @param $mode
     *   One of the PDO::FETCH_* constants.
     *   Default to what was specified by setFetchMode().
     * @param $cursor_orientation
     *   Not implemented in all database drivers, don't use.
     * @param $cursor_offset
     *   Not implemented in all database drivers, don't use.
     *
     * @return
     *   A result, formatted according to $mode.
     */
    // public function fetch($mode = NULL, $cursor_orientation = NULL, $cursor_offset = NULL);
    
    /**
     * Returns a single field from the next record of a result set.
     *
     * @param $index
     *   The numeric index of the field to return. Defaults to the first field.
     *
     * @return
     *   A single field from the next record.
     */
    public function fetchField($index = 0);
    
    /**
     * Fetches the next row and returns it as an object.
     *
     * The object will be of the class specified by kxDBStatementInterface::setFetchMode()
     * or stdClass if not specified.
     */
    // public function fetchObject();
    
    /**
     * Fetches the next row and returns it as an associative array.
     *
     * This method corresponds to PDOStatement::fetchObject(), but for associative
     * arrays. For some reason PDOStatement does not have a corresponding array
     * helper method, so one is added.
     *
     * @return
     *   An associative array.
     */
    public function fetchAssoc();
    
    /**
     * Returns an array containing all of the result set rows.
     *
     * @param $mode
     *   One of the PDO::FETCH_* constants.
     * @param $column_index
     *   If $mode is PDO::FETCH_COLUMN, the index of the column to fetch.
     * @param $constructor_arguments
     *   If $mode is PDO::FETCH_CLASS, the arguments to pass to the constructor.
     *
     * @return
     *   An array of results.
     */
    // function fetchAll($mode = NULL, $column_index = NULL, array $constructor_arguments);
    
    /**
     * Returns an entire single column of a result set as an indexed array.
     *
     * Note that this method will run the result set to the end.
     *
     * @param $index
     *   The index of the column number to fetch.
     *
     * @return
     *   An indexed array.
     */
    public function fetchCol($index = 0);
    
    /**
     * Returns the entire result set as a single associative array.
     *
     * This method is only useful for two-column result sets. It will return an
     * associative array where the key is one column from the result set and the
     * value is another field. In most cases, the default of the first two columns
     * is appropriate.
     *
     * Note that this method will run the result set to the end.
     *
     * @param $key_index
     *   The numeric index of the field to use as the array key.
     * @param $value_index
     *   The numeric index of the field to use as the array value.
     *
     * @return
     *   An associative array.
     */
    public function fetchAllKeyed($key_index = 0, $value_index = 1);
    
    /**
     * Returns the result set as an associative array keyed by the given field.
     *
     * If the given key appears multiple times, later records will overwrite
     * earlier ones.
     *
     * @param $key
     *   The name of the field on which to index the array.
     * @param $fetch
     *   The fetchmode to use. If set to PDO::FETCH_ASSOC, PDO::FETCH_NUM, or
     *   PDO::FETCH_BOTH the returned value with be an array of arrays. For any
     *   other value it will be an array of objects. By default, the fetch mode
     *   set for the query will be used.
     *
     * @return
     *   An associative array.
     */
    public function fetchAllAssoc($key, $fetch = NULL);
}

/**
 * Default implementation of kxDBStatementInterface.
 *
 * PDO allows us to extend the PDOStatement class to provide additional
 * functionality beyond that offered by default. We do need extra
 * functionality. By default, this class is not driver-specific. If a given
 * driver needs to set a custom statement class, it may do so in its
 * constructor.
 *
 * @see http://us.php.net/pdostatement
 */
class kxDBStatementBase extends PDOStatement implements kxDBStatementInterface {
    
    /**
     * Reference to the database connection object for this statement.
     *
     * The name $dbh is inherited from PDOStatement.
     *
     * @var kxDB
     */
    public $dbh;
    
    protected function __construct($dbh) {
        $this->dbh = $dbh;
        $this->setFetchMode(PDO::FETCH_OBJ);
    }
    
    public function execute($args = array(), $options = array()): bool {
        if (isset($options['fetch'])) {
            if (is_string($options['fetch'])) {
                // Default to an object. Note: db fields will be added to the object
                // before the constructor is run. If you need to assign fields after
                // the constructor is run, see http://drupal.org/node/315092.
                $this->setFetchMode(PDO::FETCH_CLASS, $options['fetch']);
            }
            else {
                $this->setFetchMode($options['fetch']);
            }
        }
		//echo "<br />";
		//print_r($args);     
        $return = parent::execute($args);
        
        return $return;
    }
    
    public function getQueryString() {
        return $this->queryString;
    }
    
    public function fetchCol($index = 0) {
        return $this->fetchAll(PDO::FETCH_COLUMN, $index);
    }
    
    public function fetchAllAssoc($key, $fetch = NULL) {
        $return = array();
        if (isset($fetch)) {
            if (is_string($fetch)) {
                $this->setFetchMode(PDO::FETCH_CLASS, $fetch);
            }
            else {
                $this->setFetchMode($fetch);
            }
        }
        
        foreach ($this as $record) {
            $record_key = is_object($record) ? $record->$key : $record[$key];
            $return[$record_key] = $record;
        }
        
        return $return;
    }
    
    public function fetchAllKeyed($key_index = 0, $value_index = 1) {
        $return = array();
        $this->setFetchMode(PDO::FETCH_NUM);
        foreach ($this as $record) {
            $return[$record[$key_index]] = $record[$value_index];
        }
        return $return;
    }
    
    public function fetchField($index = 0) {
        // Call PDOStatement::fetchColumn to fetch the field.
        return $this->fetchColumn($index);
    }
    
    public function fetchAssoc() {
        // Call PDOStatement::fetch to fetch the row.
        return $this->fetch(PDO::FETCH_ASSOC);
    }
}