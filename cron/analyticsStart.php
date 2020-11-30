<?php
 error_reporting(E_ALL);
   ini_set('display_errors',1);
   ini_set("memory_limit","32M");

   require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/functions.php');
   require_once('analyticsClass.php');

   $analis = new analyticsClass();
   $analis->updateLastDate();
   $analis->startAnalysis();
?>

