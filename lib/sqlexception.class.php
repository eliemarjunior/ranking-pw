<?php

class SQLException extends Exception { 
     
    /** 
     * The SQL associated with this exception 
     * 
     * @var String 
     */ 
    private $sql; 
     
    function SQLException ($message, $code = "", $query = null) 
    { 
        parent::__construct($message, $code); 
        if ($query != null) 
            $this->sql = $query; 
    } 
     
    /** 
     * Return the query associated with this exception 
     * 
     * @return String 
     */ 
    function getQuery() 
    { 
        return $this->sql; 
    } 
     
    /** 
     * Convert the Exception to String 
     * 
     * @return String 
     */ 
    function __toString() 
    { 
        return "Code: ".$this->getCode()." Message: ".$this->getMessage()." Query: ".$this->getQuery();
    } 
}