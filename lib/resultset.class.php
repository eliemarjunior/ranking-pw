<?php

class ResultSet { 
     
    /** 
     * PHP result resource 
     * 
     * @var unknown_type 
     */ 
    private $result_id; 
    /** 
     * Number of rows 
     * 
     * @var unknown_type 
     */ 
    private $numRows = null; 
    /** 
     * The current row 
     * 
     * @var array 
     */ 
    private $currentRow = null;     
    /** 
     * Current row number 
     * 
     * @var Integer 
     */ 
    private $currentRowNum = 0; 
     
    /** 
     * Create a resultset 
     * 
     * @param unknown_type $result 
     * @return ResultSet 
     */ 
    function ResultSet($result, $numRows=null)
    { 
        $this->result_id = $result;
        $this->numRows = $numRows;
    } 
     
    /** 
     * returns the number of rows 
     * 
     * @return Integer 
     */ 
    function numRows() 
    { 
        if ($this->numRows == null){
            if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                $this->numRows = count($this->result_id);
                
            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                $this->numRows = mssql_num_rows($this->result_id);

            }else{
                $this->numRows = $this->result_id->num_rows; 
            }

        }
             
        return $this->numRows; 
    } 
     
    /** 
     * Advance to the next row 
     * 
     * @return boolean 
     */ 
    function next() 
    { 
        if ( $this->currentRowNum < $this->numRows() ) 
        { 
            $this->currentRowNum += 1;

            if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
                $this->currentRow = oci_fetch_assoc($this->result_id);

            }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
                $this->currentRow = mssql_fetch_assoc($this->result_id);
                
            }else{
                $this->currentRow = $this->result_id->fetch_array();
            }
            
            return true; 
        }     
         
        return false;     
    } 
     
    /** 
     * Reset the result set 
     * 
     */ 
    function reset() 
    { 
        $this->currentRowNum = 0; 
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle'){
          
        }elseif(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'sqlserver'){
            mssql_data_seek($this->result_id, 0); 

        }else{
            mysql_data_seek($this->result_id, 0); ;
        }
        
    } 
     
    /** 
     * return an int 
     * 
     * @param string $colName 
     * @return Integer 
     */ 
    function getInt($colName) 
    { 
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle')
            $colName = strtoupper($colName);
        return intval($this->currentRow[$colName]); 
        
    } 
     
    /** 
     * return a float 
     * 
     * @param String $colName 
     * @return float 
     */ 
    function getNumber($colName) 
    { 
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle')
            $colName = strtoupper($colName);
        return floatval($this->currentRow[$colName]); 
    } 
     
    /** 
     * return a String 
     * 
     * @param String $colName 
     * @return float 
     */ 
    function getString($colName) 
    { 
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle')
            $colName = strtoupper($colName);
        return stripslashes(is_object($this->currentRow[$colName]) ? str_replace(array('\n','\r'),'',$this->currentRow[$colName]->load()) : $this->currentRow[$colName]);
        
    } 
     
    /** 
     * Return a boolean 
     * 
     * @param String $colName 
     * @return Integer 
     */ 
    function getBoolean($colName) 
    { 
        if(strtolower($GLOBALS['DBCONN']['data']['drive']) == 'oracle')
            $colName = strtoupper($colName);
        if ($this->currentRow[$colName] == 0) 
            return false; 
        else      
            return true; 
    } 
     
    /** 
     * Return the current row as array 
     * 
     * @return Array 
     */ 
    function getAllVars() 
    { 
        return $this->currentRow; 
    } 
     
     
}  