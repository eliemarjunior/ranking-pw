<?php

function get($var){
    return antiInjection(isset($_GET[$var])?$_GET[$var]:(isset($_POST[$var])?$_POST[$var]:''));
}

function antiInjection($sql, $strip_tags = true){
    while(preg_match("/('|\*|--|\\\\)/i", $sql)){
        $sql = preg_replace("/('|\*|--|\\\\)/i","",$sql);
    }
    $sql = trim($sql);
    if($strip_tags ) $sql = strip_tags($sql);
    return addslashes($sql);
}