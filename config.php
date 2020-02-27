<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

$NomeServidor = 'PW 4FunBR';

$PontosGanhosPorMatar     = 3;
$PontosPerdidosPorMorrer  = 2;
$RegistrosPorPagina       = 15;

$DBHost       = "localhost";
$DBName       = "pw";
$DBUser       = "root";
$DBPassWord   = '';
$DBDriver     = "mysql";
$DBPort       = 3306;
$DBCONN       = false;

$arrClass = array(
  "0"   =>  "Guerreiro",
  "1"   =>  "Mago",
  "2"   =>  "Espiritualista",
  "3"   =>  "Feiticeira",
  "4"   =>  "B&aacute;rbaro",
  "5"   =>  "Mercen&aacute;rio",
  "6"   =>  "Arqueiro",
  "7"   =>  "Sacerdote",
  "8"   =>  "Arcano",
  "9"   =>  "M&iacute;stico" ,  
  "11"  =>  "Tormentador",
  "10"  =>  "Retalhador",
);

require 'lib/connection.class.php';
require 'lib/statement.class.php';
require 'lib/resultset.class.php';
require 'lib/sqlexception.class.php';
require 'lib/functions.php';
