<?php
/**
 * Main Database Class. Initilized by a string representing the name of the database to connect with.<br />
 * Handles all Database tasks like conection, query execution, etc, using the mysqli library.
 * @author Ankso
 */
class Database
{
    /**
     * Class constructor, establishes a MySQL connection to the specified DB (or retrieves one active connection from the connections pool)
     * @param $dbToConnect Database where the operations are going to be executed.
     */
    function __construct($dbToConnect)
    {
        if (!isset($dbToConnect))
            die("Fatal error: Missing argument when initializing the Database class.");
        global $SERVER_INFO;
        $this->_mysqli = new mysqli(/*"p:" . */$SERVER_INFO['HOST'], $SERVER_INFO['USERNAME'], $SERVER_INFO['PASSWORD'], $dbToConnect);
        if ($this->_mysqli->connect_errno)
            die("Fatal error ". $this->_mysqli->connect_errno .": ". $this->_mysqli->connect_error);
    }
    
    /**
     * Class destructor. Does nothing.
     */
    function __destructor()
    {
    }
    
    /**
     * Begins a MySQL transaction.
     * @return Returns true on success, or false on failure.
     */
    function BeginTransaction()
    {
        return $this->_mysqli->autocommit(false);
    }
    
    /**
     * Commits a MySQL transaction.
     * @return Returns true on success, or false on failure.
     */
    function CommitTransaction()
    {
        return ($this->_mysqli->commit() && $this->_mysqli->autocommit(true));
    }
    
    /**
     * Rollbacks a MySQL transaction.
     * @return Returns true on success, or false on failure.
     */
    function RollbackTransaction()
    {
        return ($this->_mysqli->rollback() && $this->_mysqli->autocommit(true));
    }

    /**
     * Executes a query on the database.
     * @param string $query The query to be executed.
     * @return $resource Returns mysqli_result on success for SELECT or other operations that return a result value, or true for INSERT type operation.<p>Returns false on failure.</p>
     */
    public function Execute($query)
    {
        return $this->_mysqli->query($query);
    }
    
    /**
     * Prepares and executes a prepared statement. It can execute the stmt once or multiple times.
     * @param string $query The prepared statement to be executed.
     * @param array $params The params that are going to be inserted in the prepared statement. $params is a bidimensional array with the following structure:<br/>$params = array(<br/>0 => array(<br/>0 => 'i',<br/>1 => $myInt,<br/>)<br/>);</p>
     * @return mixed Returns a mysqli_result on success for SELECT or other operations that return a result value, or true for INSERT type operation.<p>Returns an array of mysqli_results if multiple sentences return a result</p><p>Returns false on failure.</p>
     */
    public function ExecuteStmt($query, $params)
    {
        // We'll need both parameters. To execute a query without variables, use Database::query() instead.
        if (!isset($query) || !isset($params))
            return false;
        
        if(!is_array($params))
            return false;
        
        // Prepare the stmt
        if (!($stmt = $this->_mysqli->prepare($query)))
        {
            echo $this->_mysqli->error;
            return false;
        }
        /**
         * The $params structure is:
         * $params = array(
         *     0 => array(
         *         0 => "isi",
         *         1 => $myInt1,
         *         2 => $myString,
         *         3 => $myInt2
         *         )
         *     );
         * so...
         */
        $results = array();                // The array of results
        $eCount = count($params);          // External count: represents the number of executions of the prepared statement
        for ($i = 0; $i < $eCount; ++$i)
        {
            // We need to pass the values by reference, this hack does the trick
            $args = array();
            foreach ($params[$i] as $j => &$arg)
                $args[$j] = &$arg;
            // call_user_func_array makes the magic. But it is about 1/3 solwer than call the function directly. And may be that's not good in the future.
            if (!call_user_func_array(array($stmt, 'bind_param'), $args))
                return false;
            if (!$stmt->execute())
            {
                echo $this->_mysqli->error;
                return false;
            }
            // It appears that for INSERT type queries, get_result() returns false. So, if the query has been executed correctly, we can assume that the real result is true
            $res = $stmt->get_result();
            $results[] = ($res === false ? true : $res);
        }
        if (count($results) == 1)
            return $results[0];
        return $results;
    }
    
    /**
     * Creates a valid array for one statement to use it when calling ExecuteStmt(). Multiple params can be sended.
     * @param string A valid type identifier(s) for stmt::bind_param (i, d, s, b)
     * @param mixed The variable(s) related with the previous type identifier(s).
     * @return bool Returns an array ready to use in Database::ExecuteStmt if success, false in case of failure.
     */
    public function BuildStmtArray()
    {
        if (func_num_args() < 2)
            return false;
        
        $args = func_get_args();
        $StmtArray = array(
            0 => array()
            );
        for ($i = 0; $i < func_num_args(); ++$i)
            $StmtArray[0][] = $args[$i];
        return $StmtArray;
    }
    
    /**
     * Creates a valid array for multiple statements to use when calling ExecuteStmt(). It accepts multiple params.
     * @param integer $queryCount The number of times that the statement is going to be executed.
     * @param string A valid type identifier(s) for stmt::bind_param (i, d, s, b)
     * @param mixed The variable(s) related with the previous type identifier(s)
     * @param [..] 
     * @return bool Returns an array ready to use in Database::ExecuteStmt if success, false in case of failure.
     */
    public function BuildStmtPackage($queryCount)
    {
        $args = func_get_args();
        // Extract $queryCount from the array
        array_shift($args);
        $varTypes = array_shift($args);  // the string representing the var types is only passed once.
        $varCount = count($args);        // This represents the number of variables per stmt.
        if ($varCount < 1 || !is_int($queryCount) || $queryCount === 0)
            return false;
        $StmtArray = array(
            0 => array()
        );
        for ($j = 0; $j < $queryCount; ++$j)
        {
            $StmtArray[$j][0] = $varTypes;
            for ($i = 0; $i < ($varCount / $queryCount); ++$i)
                $StmtArray[$j][$i + 1] = array_shift($args);
        }
        return $StmtArray;
    }
    
    /**
     * Escapes special characters in a string to be used in a SQL sentence
     * @param string $string The string to escape.
     * @return mixed The escaped string or false on error.
     */
    public function RealEscapeString($string)
    {
        return $this->_mysqli->real_escape_string($string);
    }
    
    private $_mysqli;
}
?>