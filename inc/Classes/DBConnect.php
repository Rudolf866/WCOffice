<?php

//   ini_set('display_errors', 1);
//   error_reporting(E_ALL);

// function connect_db() {

// 	$dsn = 'mysql:dbname=db_endicomp_27;charset=cp1251;host=localhost';
// 	$user = 'dbu_endicomp_1';
// 	$password = 'oHb0esMnA4z';

// 	return new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES cp1251"));

// }
 // ========================доступ к production базе===================================

   //$dsn = 'mysql:dbname=db_endicomp_27;charset=UTF8;host=endicomp.mysql';
    //$user = 'dbu_endicomp_1';
   // $password = 'oHb0esMnA4z';

//=========================================================================================

function connect_db() {

        $dsn = 'mysql:dbname=db_endicomp_6;charset=UTF8;host=endicomp.mysql';
        $user = 'dbu_endicomp_6';
        $password = 'ncvhbuQ8mGy';

    return new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));

}
