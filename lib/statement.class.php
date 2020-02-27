<?php
class Statement { 
     
    /** 
     * The SQL query 
     * 
     * @var String 
     */ 
    private $query;

    /** 
     * variables 
     * 
     * @var array 
     */ 
    private $vars = array();
    private $returnVar;
    private $varsCLOB = array();
    /** 
     * the connection object 
     * 
     * @var Connection 
     */ 
    private $connection; 
     
    /** 
     * Create a new statement based on the connection 
     * 
     * @param Connection $conn 
     * @return Statement 
     */ 
    function Statement($conn) 
    { 
        $this->connection = $conn;         
    } 

    /** 
     * Set a String 
     * 
     * @param String $name 
     * @param String $val 
     */ 
    function setString($name, $val) 
    { 
        $this->vars[$name] ="'".  addslashes(stripslashes($val))."'"; 
    } 
    
    
    function setReturnVar($name) 
    { 
        $this->returnVar = strtoupper($name); 
    } 
    
    /** 
     * Set a SQL 
     * 
     * @param String $name 
     * @param String $val 
     */ 
    function setSql($name, $val) 
    { 
        $this->vars[$name] = $val; 
    } 

    /** 
     * Set an integer 
     * 
     * @param String $name 
     * @param Integer $val 
     */ 
    function setInt($name, $val) 
    { 
        if (empty($val) && (string)$val != "0") 
        { 
            $this->setNull($name); 
            return; 
        } 
        if (!is_numeric($val)) 
            throw new SQLException("$name=$val não é inteiro.", 4); 
             
        if (strpos($val, ".") !== false) 
            throw new SQLException("$name=$val não é inteiro, é float.", 4); 
             
        $this->vars[$name] = $val; 
    } 
    /** 
     * Set a CLOB Data 
     * 
     * @param String $name 
     * @param String $val 
     */
    function setCLOB($name, $val)
    {
        $this->varsCLOB[$name] = stripslashes($val);
    }

    /** 
     * Set a float 
     * 
     * @param String $name 
     * @param Float $val 
     */ 
    function setNumber($name, $val) 
    { 
        if (empty($val) && (string)$val != "0") 
        { 
            $this->setNull($name); 
            return; 
        } 
        if (!is_numeric($val)) 
            throw new SQLException("$name=$val não é float", 5); 
             
        $this->vars[$name] = $val; 
    } 
     
    /** 
     * set a null value 
     * 
     * @param String $name 
     */ 
    function setNull($name) 
    { 
        $this->vars[$name] = 'null'; 
    } 
     
    /** 
     * Set a boolean 
     * 
     * @param String $name 
     * @param Boolean $value 
     */ 
    function setBoolean($name, $value) 
    { 
        if ($value == true) 
            $this->vars[$name] = "'1'"; 
        else 
            $this->vars[$name] = "'0'"; 
    } 
     
    /** 
     * Set sql query 
     * 
     * @param String $query 
     */ 
    function setQuery($query) 
    { 
        $this->query = $query; 
    } 
    
    /** 
     * Get sql query 
     * 
     * @return String $query 
     */ 
	function getQuery() 
    { 
        return $this->query; 
    }
    
    /** 
     * Set an Integer Array 
     * 
     * @param String $name 
     * @param Array $value 
     */ 
    function setIntArray($name, $value) 
    { 
        foreach ($value as $key=>$val) 
        { 
            if (!is_numeric($val)) 
                throw new SQLException("$val não é inteiro", 4); 
             
            if (strpos($val, ".") !== false) 
                throw new SQLException("$val não é inteiro, é float.", 4); 
        } 
        $this->vars[$name] = "(".implode(", ", $value).")"; 
    } 

    /** 
     * Parse the query and insert variables 
     * 
     * @return String 
     */ 
    function parse() 
    { 
        $parsed = $this->query; 
        foreach ($this->vars as $key=>$val) 
        { 
            $parsed = str_replace(":$key:", $val, $parsed); 
        }

        foreach ($this->varsCLOB as $key=>$val)
        {
            $parsed = str_replace(":$key:", ":L_$key", $parsed);
        }
         
        if(!$this->connection->isConnected()){
            $this->connection = new Connection();
        }
        
        return $parsed; 
    } 
     
    /** 
     * execute a query that does not require a resultset 
     * to be returned. Mainly used for insert, update, delete, 
     * and other non-select queries 
     * 
     * @return Integer 
     */ 
    function executeQuery($returnId=true)
    {
        $parsed = $this->parse();
        
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                $id = 0;
                
                $index = strpos(strtolower($this->query), "insert");
                if ($index !== false && $index == 0 && $returnId && $this->returnVar != ''){
                    $parsed .= " RETURNING {$this->returnVar} INTO :ID";
                }
                
                $st = oci_parse($this->connection->conn_id, $parsed);
                
                if ($index !== false && $index == 0 && $returnId && $this->returnVar != ''){
                    oci_bind_by_name($st, ":ID", $id);
                }
                
                if(count($this->varsCLOB)>0){
                    foreach ($this->varsCLOB as $key=>$value){
                        oci_bind_by_name($st, ":L_{$key}", $value);
                    }
                }
                
                oci_execute($st, OCI_DEFAULT);
                        
                if (!$this->connection->commitTransaction())
                {
                    $this->connection->rolloverTransaction();
                    throw new SQLException(oci_error($st), 6, $parsed);
                }
                
                if ($index !== false && $index == 0 && $returnId){
                    return $id;
                }else{
                    return oci_num_rows($st);
                }
                
        }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                if ($returnId && $index !== false && $index == 0){
                    $this->query = $parsed."; SELECT SCOPE_IDENTITY()";
                    $id = $this->executeScalar();
                    return $id;
                }else{
                    if (!mssql_query($parsed, $this->connection->conn_id))
                    {
                        $this->connection->rolloverTransaction();
                        throw new SQLException(mssql_get_last_message(), 6, $parsed);
                    }
                    return mssql_rows_affected($this->connection->conn_id);
                }


        }else{
                
                if (!$this->connection->conn_id->query($parsed))
                {
                    $this->connection->rolloverTransaction();
                    throw new SQLException(mysqli_error($this->connection->conn_id), 6, $parsed);
                }

                // find out if this is an insert query...
                $index = strpos(strtolower($this->query), "insert");
                if ($index !== false && $index == 0)
                    return $this->connection->conn_id->insert_id;
                else
                    return $this->connection->conn_id->affected_rows;
        }
        
    } 
     
    /** 
     * execute a query and return a result set. 
     * 
     * @return ResultSet 
     */ 
    function executeReader() 
    { 
        $parsed = $this->parse(); 
        //echo $parsed;
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                $st = oci_parse($this->connection->conn_id, $parsed);

                if (!oci_execute($st))
                {
                    $this->connection->rolloverTransaction();
                    throw new SQLException(oci_error($st), 7, $parsed);
                }

                $rset = new ResultSet($st,$this->numRows());

        }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                if (!($result = mssql_query($parsed, $this->connection->conn_id)))
                {
                    $this->connection->rolloverTransaction();
                    throw new SQLException(mssql_get_last_message(), 7, $parsed);
                }

                $rset = new ResultSet($result);

                
        }else{
                if (!($result = $this->connection->conn_id->query($parsed)))
                {
                    $this->connection->rolloverTransaction();
                    throw new SQLException(mysqli_error($this->connection->conn_id), 7, $parsed);
                }

                $rset = new ResultSet($result);
        }

        
         
        return $rset; 
    } 
    
    function executeScalar() 
    { 
        $parsed = $this->parse(); 
        if($parsed != ''){
            if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                    $st = oci_parse($this->connection->conn_id, $parsed);

                    if (!oci_execute($st))
                    {
                        $this->connection->rolloverTransaction();
                        throw new SQLException(oci_error($st), 8, $parsed);
                    }

                    $record = oci_fetch_assoc($st);

            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){

                    if (!($result = mssql_query($parsed, $this->connection->conn_id)))
                    { 
                        $this->connection->rolloverTransaction();
                        throw new SQLException(mssql_get_last_message(), 8, $parsed);
                    }

                    $record = mssql_fetch_assoc($result);


            }else{
                
                    if (!($query = $this->connection->conn_id->query($parsed)))
                    {
                        $this->connection->rolloverTransaction();
                        throw new SQLException(mysqli_error($this->connection->conn_id), 8, $parsed);
                    }
                    
                    $record = $query->fetch_array();
            }

             return array_pop($record); 
         }else{
            return '';
         }
    } 
    
    function executeSP($nm, $params=array()) 
    { 
        $stmt = mssql_init($nm, $this->connection->conn_id);
        foreach ($params as $key => $value) {
            mssql_bind($stmt, '@'.$key, $value['value'], $value['type'])
            or die("Unable to bind $key<br>".mssql_get_last_message());
        }
        $result = mssql_execute($stmt);
        unset($stmt);
        return new ResultSet($result);
    } 
     
    private function numRows(){
        $parsed = $this->parse();
        $st = oci_parse($this->connection->conn_id, "SELECT COUNT(*) FROM ({$parsed})");

        if (!oci_execute($st))
        {
            $this->connection->rolloverTransaction();
            throw new SQLException(oci_error($st), 8, $parsed);
        }

        $record = oci_fetch_assoc($st);
        return array_pop($record);
    }
    
}