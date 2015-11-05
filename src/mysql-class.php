<?php

/*
 * Copyright (c) 2015 José Porto
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace JosePorto\MySQL;

use mysqli;

class SQL
{
	// Base variables

    var $lastError;         // Holds the last error
	var $result;            // Holds the SQL query result
	var $records;           // Holds the total number of records returned
	var $affected;          // Holds the total number of records affected
	var $insertId;          // Holds the last insert id
	var $rawResults;        // Holds raw 'arrayed' results
	var $arrayedResult;     // Holds an array of the result
	var $arrayedFields;     // Holds the fields
	var $debugData;         // Holds the debug data
	var $database;          // Database
	var $dbLink;    	    // Database Connection Link
	var $stmt;			    // Holds the statement
	var $meta;				// Holds the statement result meta data
	var $lastQueryTime;		// Holds last execution time

    /**
     * SQL constructor.
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $dbName
     */
    public function __construct( $host = DB_HOST, $user = DB_USER, $password = DB_PASSWORD, $dbName = DB_NAME )
    {
		$this->database = $dbName;
		$this->Open($host, $user, $password, $dbName);
	}

    /**
     *
     */
    public function __destruct()
    {
		self::Close();
	}

	// Private Functions

    /**
     * @param $host
     * @param $user
     * @param $password
     * @param $dbName
     * @return bool
     */
    private function Open($host, $user, $password, $dbName )
    {
		if( $this->dbLink ){ $this->Close(); }

		$this->dbLink = new mysqli($host, $user, $password, $dbName);

		if ( mysqli_connect_errno() ) {
			error_log( $this->dbLink->error );
			$this->lastError = htmlspecialchars( $this->dbLink->error );
			return false;
		}else{
			$this->dbLink->query("SET names 'UTF8'", true);
			return true;
		}
	}

    /**
     * @param $arr
     * @return array
     */
    static private function refValues($arr )
    {
		// Reference is required for PHP 5.3+
		if ( strnatcmp( phpversion(), '5.3' ) >= 0 && $arr !=null ) {
			$refs = array();
			foreach( $arr as $key => $value )
				$refs[ $key ] = &$arr[ $key ];
			return $refs;
		}
		return $arr;
	}

	// Public Functions

    /**
     * @param $sql
     * @param $params
     * @return array|null
     */
    public function SELECT($sql, $params )
    {
		return self::execSTMT( $sql, $params, false );
	}

    /**
     * @param $sql
     * @param $params
     * @return array|null
     */
    public function INSERT($sql, $params )
    {
		return self::execSTMT( $sql, $params, true );
	}

    /**
     * @param $sql
     * @param $params
     * @return array|null
     */
    public function DELETE($sql, $params )
    {
		return self::execSTMT( $sql, $params, true );
	}

    /**
     * @param $sql
     * @param $params
     * @param $close
     * @return array|null
     */
    public function execSTMT($sql, $params, $close )
    {
		$taskStartTime = microtime(true); 		    // starts timer
		$this->debugData = debug_backtrace(); 		// instances the backtrace

		$this->stmt = $this->dbLink->prepare( $sql );

		// Test if the statement preparation worked
		if( !$this->stmt )
        {
			$this->lastError = htmlspecialchars( $this->dbLink->error );
		}

		// Bind the parameters to the statement, replacing the ?
		if($params != null)
        {
			call_user_func_array( array( $this->stmt, 'bind_param' ), self::refValues( $params ) );
		}

		// Execute the query
		$this->stmt->execute();

		if( $close )
        {
			$this->affected = $this->stmt->affected_rows;
			$this->insertId = htmlspecialchars( $this->dbLink->insert_id );
			$result = $this->stmt->affected_rows;
		} else {
			$this->stmt->store_result();
			$this->meta = $this->stmt->result_metadata();
			$this->records = $this->stmt->num_rows;

            $parameters = array();

			while ( $field = $this->meta->fetch_field() )
            {
				$parameters[] = &$row[ $field->name ];
			}

			$results = null;

			call_user_func_array( array( $this->stmt, 'bind_result' ), self::refValues( $parameters ) );

			while ( $this->stmt->fetch() )
            {
				$x = array();
				foreach( $row as $key => $val )
                {
					$x[ $key ] = $val;
				}
				$results[] = $x;
			}

			$this->arrayedResult = $results;
			$result = $results;
		}

		$this->stmt->close();

		$taskEndTime = microtime(true);
		$this->lastQueryTime = $taskEndTime - $taskStartTime;

		return $result;
	}

    /**
     * @return mixed
     */
    public function Close()
    {
		return $this->dbLink->close();
	}
}
