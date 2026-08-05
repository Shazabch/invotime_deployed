<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * NTICompass' CodeIgniter Subquery Library
 * (Requires Active Record and PHP5)
 *
 * Version 2.6
 *
 * By: Eric Siegel
 * http://labs.nticompassinc.com
 */
class Subquery
{
    public $CI, $db, $prefix, $func, $dbStack, $statement, $join_type, $join_on, $unions;
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->db = $this->CI->db; // Default database connection
        // https://github.com/EllisLab/CodeIgniter/pull/307
        $canRun = false;
        $functions = array('_compile_select', 'get_compiled_select');
        foreach ($functions as $func) {
            if ($canRun = is_callable(array($this->db, $func))) {
                $this->func = $func;
                break;
            }
        }
        if (!$canRun) {
            show_error("Subquery library cannot run.  Missing get_compiled_select.  Please see the library's documentation.");
        }
        $this->prefix = trim($this->db->dbprefix(' '));
        $this->dbStack = array();
        $this->statement = array();
        $this->join_type = array();
        $this->join_on = array();
        $this->unions = 0;
    }
    /**
     * defaultDB - Sets the default database object to use
     *
     * @param $database - Database object to use by default
     *
     */
    public function defaultDB($database)
    {
        $this->db = $database;
        $this->prefix = trim($this->db->dbprefix(' '));
    }
    /**
     * start_subquery - Creates a new database object to be used for the subquery
     *
     * @param $statement - SQL statement to put subquery into (select, from, join, etc.)
     * @param $join_type - JOIN type (only for join statements)
     * @param $join_on - JOIN ON clause (only for join statements)
     *
     * @return A new database object to use for subqueries
     */
    public function start_subquery($statement, $join_type = '', $join_on = 1)
    {
        $db = $this->CI->load->database('', true);
        if (is_callable(array($db, 'set_dbprefix'))) {
            $db->set_dbprefix($this->prefix);
        }
        $this->dbStack[] = $db;
        $this->statement[] = $statement;
        switch (strtolower($statement)) {
            case 'join':
            case 'join_on':
                $this->join_type[] = $join_type;
                $this->join_on[] = $join_on;
                break;
        }
        return $db;
    }
    /**
     * start_union - Creates a new database object to be used for unions
     *
     * @return A new database object to use for a union query
     */
    public function start_union()
    {
        $this->unions++;
        return $this->start_subquery('');
    }
    /**
     * end_subquery - Closes the database object and writes the subquery
     *
     * @param $alias - Alias to use in query, or field to use for WHERE
     * @param $operator - Operator to use for WHERE (=, !=, <, etc.) / WHERE IN (TRUE for WHERE IN, FALSE for WHERE NOT IN)
     *        $if_null - If it's a SELECT, this will turn it into COALESCE((SELECT ...), $if_null) AS $alias
     * @param $database - Database object to use when dbStack is empty (optional)
     *
     * @return none
     */
    public function end_subquery($alias = '', $operator = true, $database = false)
    {
        $db = array_pop($this->dbStack);
        $sql = '(' . $db->{$this->func}() . ')';
        $db->close();
        $as_alias = $alias != '' ? "AS $alias" : $alias;
        $statement = array_pop($this->statement);
        if ($database === false) {
            $database = (count($this->dbStack) == 0)
            ? $this->db : $this->dbStack[count($this->dbStack) - 1];
        }
        $alias = $database->protect_identifiers($alias);
        switch (strtolower($statement)) {
            case 'join':
                $join_type = array_pop($this->join_type);
                $join_on = array_pop($this->join_on);
                $database->$statement("$sql $as_alias", $join_on, $join_type);
                break;
            case 'join_on':
                $join_type = array_pop($this->join_type);
                $join_on = array_pop($this->join_on);
                $operator = $operator === true ? '=' : $operator;
                // Hack to get around the regex in JOIN
                // /([\w\.]+)([\W\s]+)(.+)/
                $sql = preg_replace('/(\n|\r\n)/', ' ', $sql);
                $database->join($join_on, "$alias $operator $sql", $join_type);
                break;
            case 'select':
                if ($operator !== true) {
                    $operator = $database->escape($operator);
                    $database->$statement("COALESCE($sql, $operator) $as_alias", false);
                } else {
                    $database->$statement("$sql $as_alias", false);
                }
                break;
            case 'where':
                $operator = $operator === true ? '=' : $operator;
                $database->where("$alias $operator $sql", null, false);
                break;
            case 'or_where':
                $operator = $operator === true ? '=' : $operator;
                $database->or_where("$alias $operator $sql", null, false);
                break;
            case 'where_in':
                $operator = $operator === true ? 'IN' : 'NOT IN';
                $database->where("$alias $operator $sql", null, false);
                break;
            case 'where_exists':
                $operator = $operator === true ? 'EXISTS' : 'NOT EXISTS';
                $database->where("$operator $sql", null, false);
                break;
            default:
                $database->$statement("$sql $as_alias");
                break;
        }
    }
    /**
     * end_union - Combines all opened db objects into a UNION ALL query
     *
     * @param $database - Database object to use when dbStack is empty (optional)
     *
     * @return none
     */
    public function end_union($database = false)
    {
        $queries = array();
        for ($this->unions; $this->unions > 0; $this->unions--) {
            $db = array_pop($this->dbStack);
            $queries[] = $db->{$this->func}();
            $db->close();
            array_pop($this->statement);
        }
        $queries = array_reverse($queries);
        if (substr($queries[0], 0, 6) == 'SELECT') {
            $queries[0] = substr($queries[0], 7);
        }
        $sql = implode(' UNION ALL ', $queries);
        if ($database === false) {
            $database = (count($this->dbStack) == 0)
            ? $this->db : $this->dbStack[count($this->dbStack) - 1];
        }
        $database->select($sql, false);
    }
    /**
     * join_range - Helper function to CROSS JOIN a list of numbers
     * From: http://stackoverflow.com/questions/4155873/mysql-find-in-set-vs-in/4156063#4156063
     *
     * @param $start - Range start
     * @param $end - Range end
     * @param $alias - Alias for number list
     * @param $table_name - JOINed tables need an alias (optional)
     * @param $database - Database object to use when dbStack is empty (optional)
     */
    public function join_range($start, $end, $alias, $table_name = 'q', $database = false)
    {
        $range = array();
        foreach (range($start, $end) as $r) {
            $range[] = "SELECT $r AS $alias";
        }
        $range[0] = substr($range[0], 7);
        $range = implode(' UNION ALL ', $range);
        $sub = $this->start_subquery('join', 'inner');
        $sub->select($range, false);
        $this->end_subquery($table_name, true, $database);
    }
    /**
     * dbWrapper - Call a function using "$this->db" in a sandbox, so you don't interfere with other queries
     *
     * @param $callback - Function to call, only tested with array($obj, 'func') syntax
     * @param $params... - Parameters to pass to callback
     *
     * @return Whatever the callback returns
     */
    public function dbWrapper($callback)
    {
        $newdb = $this->CI->load->database('', true);
        if (is_callable(array($newdb, 'set_dbprefix'))) {
            $newdb->set_dbprefix($this->prefix);
        }
        $cidb = $this->CI->db;
        $this->CI->db = $newdb;
        $params = func_get_args();
        array_shift($params);
        $ret = call_user_func_array($callback, $params);
        $this->CI->db = $cidb;
        return $ret;
    }
}
