<?php

class Connection { 
     
    /** 
     * PHP Connection resource 
     * 
     * @var unknown_type 
     */ 
    public $conn_id; 
    public $dataConnection = array(
        'user' => '',
        'pass' => '',
        'name' => '',
        'host' => '',
        'drive' => '',
        'port' => '',
    );
    /** 
     * whether we are in a transaction 
     * 
     * @var boolean 
     */ 
    private $inTransaction = false; 
    /** 
     * are we connected? 
     * 
     * @var unknown_type 
     */ 
    private $isConnected = false; 
    /** 
     * Debugger 
     * 
     * @var Debugger 
     */ 
    private $_debugger = null;
     
    /** 
     * Create a connection 
     * 
     * @param unknown_type $host 
     * @param unknown_type $user 
     * @param unknown_type $pass 
     * @param unknown_type $db 
     * @return Connection 
     */ 
    function __construct($dataConnection=array()) 
    {
        if(count($dataConnection)>0){
            $this->dataConnection = $dataConnection;
            $GLOBALS['DBCONN'] = false;
        }else{
            $this->dataConnection = array(
               'user' => $GLOBALS['DBUser'],
               'pass' => $GLOBALS['DBPassWord'],
               'name' => $GLOBALS['DBName'],
               'host' => $GLOBALS['DBHost'],
               'port' => $GLOBALS['DBPort'],
               'drive' => $GLOBALS['DBDriver'],
           );
        }
        
        if(!isset($GLOBALS['DBCONN']) || !$GLOBALS['DBCONN']){
            if(strtolower($this->dataConnection['drive']) == 'oracle'){
                $this->conn_id = oci_connect($this->dataConnection['user'], $this->dataConnection['pass'], $this->dataConnection['host'].":".$this->dataConnection['port']."/".$this->dataConnection['name'], "WE8ISO8859P1");
            }elseif(strtolower($this->dataConnection['drive']) == 'sqlserver'){
                $this->conn_id = mssql_connect($this->dataConnection['host'].":".$this->dataConnection['port'], $this->dataConnection['user'], $this->dataConnection['pass']);
            }elseif(strtolower($this->dataConnection['drive']) == 'postgre'){
                $this->conn_id=pg_connect("host={$this->dataConnection['host']} port={$this->dataConnection['port']} dbname={$this->dataConnection['name']} user={$this->dataConnection['user']} password={$this->dataConnection['pass']}");
            }else{
                $this->conn_id = new mysqli($this->dataConnection['host'], $this->dataConnection['user'], $this->dataConnection['pass'], $this->dataConnection['name'],$this->dataConnection['port']);
                if (mysqli_connect_errno()) trigger_error(mysqli_connect_error());
                $this->conn_id->set_charset('latin1');
            }
            
            if (!$this->conn_id)
                throw new SQLException("Não foi possível conectar ao Banco de Dados.", 1);
            
            $GLOBALS['DBCONN']['conn']= $this->conn_id;
            $GLOBALS['DBCONN']['data']= $this->dataConnection;
            
        }else{
            $this->conn_id = $GLOBALS['DBCONN']['conn'];
        }
        
        $this->isConnected = true;
        //$this->setDebugger(new Debugger(true)); 
    } 
     
    /** 
     * Are we in a transaction? 
     * 
     * @return boolean 
     */ 
    function inTransaction() 
    { 
        return $this->inTransaction; 
    }
    
    /** 
     * start a transaction 
     * 
     */ 
    function beginTransaction() 
    { 
        if ($this->inTransaction) 
            throw new SQLException("Transação já iniciada", 3); 

        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
            $st = oci_parse($this->conn_id, "begin");
            oci_execute($st);
        }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
            mssql_query("begin", $this->conn_id);
        }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'postgre'){
            pg_query($this->conn_id,'BEGIN');
        }else{
            $this->conn_id->query("begin");
        }
        
        $this->inTransaction = true; 
    } 
     
    /** 
     * commit transaction 
     * 
     */ 
    function commitTransaction() 
    {
        try{
            if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                $this->inTransaction = false;
                return oci_commit($this->conn_id);
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                mssql_query("commit", $this->conn_id);
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'postgre'){
                pg_query($this->conn_id,'COMMIT');
            }else{
                $this->conn_id->query("commit");
            }
            
            $this->inTransaction = false;
            return true;
        }  catch (Exception $e){
            return false;
        }
    } 
     
    /** 
     * Rollover the transaction 
     * 
     */ 
    function rolloverTransaction() 
    { 
        if ($this->inTransaction){
            if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                $this->inTransaction = false;
                return oci_rollback($this->conn_id);
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                mssql_query("rollover", $this->conn_id);
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'postgre'){
                pg_query($this->conn_id,'ROLLOVER');
            }else{
                $this->conn_id->query("rollover"); 
            }

        }
        $this->inTransaction = false;
    } 
    
    /** 
     * Close the connection
     * 
     */ 
    function closeConnection() 
    { 
        if ($this->conn_id){
            if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                oci_close($this->conn_id);
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                mssql_close($this->conn_id);
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'postgre'){
                pg_close($this->conn_id);
            }else{
                $this->conn_id->close();
            }
            $GLOBALS['DBCONN']=false;
        }
             
        $this->isConnected = false; 
    } 
    
    /** 
     * Create a statement 
     * 
     * @param String $sql 
     * @return Statement 
     */ 
    function prepareStatement($sql) 
    { 
        $stmt = new Statement($this); 
        $stmt->setQuery($sql); 
         
        return $stmt; 
    } 
     
    /** 
     * Set a debugger 
     * 
     * @param unknown_type $debugger 
     */ 
    function setDebugger($debugger) 
    { 
        $this->_debugger = $debugger; 
    } 
     
    /** 
     * Get the debugger 
     * 
     * @return Debugger 
     */ 
    function getDebugger() 
    { 
        return $this->_debugger; 
    } 
    
    function isConnected(){
        return (isset($GLOBALS['DBCONN'])&& $GLOBALS['DBCONN']!=false);
    }
}