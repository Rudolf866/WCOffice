#!/usr/local/bin/php
<?php
/**
 * Class script analytics 
 */
 error_reporting(E_ALL);
   ini_set('display_errors',1);

class analyticsClass
{
    
    private $dbh;

    function __construct() {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/functions.php');

        $this->dbh = connect_db();
        $this->dbh->query("SET NAMES 'UTF8'");
    }

    function updateLastDate(){
       try {
         $res = $this->dbh->query("SELECT o.id , o.client_id AS client, MAX(o.date) AS o_date, c.last_date AS last FROM iwater_orders AS o JOIN iwater_clients AS c ON (o.client_id = c.client_id) GROUP BY o.client_id");
         $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     } catch (Exception $e) {
         echo 'Подключение не удалось: ' . $e->getMessage();
     }
     while ($u_r = $res->fetch(PDO::FETCH_ASSOC)) {
      $time = $u_r['o_date'] - 172800;
      try {
                    $upd_res = $this->dbh->prepare("UPDATE `iwater_clients` SET `last_date` = ? WHERE `client_id` =  ?");
                    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
                $upd_res->execute(array($time, $u_r['client']));
     }
 }
      /**
       * Получение id клиентов у которых > 3 заказов и рассчет средней даты между датами
       * 
      */
     function startAnalysis(){
       $current_date = time() - 86400;
       try {
         $res = $this->dbh->query("SELECT COUNT(o.id) AS count, o.local_id AS client, c.last_date FROM iwater_orders AS o JOIN iwater_clients AS c ON (o.local_id = c.id) WHERE c.last_date = '$current_date' GROUP BY o.local_id ORDER BY count DESC");
         $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     } catch (Exception $e) {
         echo 'Подключение не удалось: ' . $e->getMessage();
     }

     while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if ($r['count'] == 2) {
           exit();
        } else {
         $this->calcAverage($r['client']);
      }
     }
 }

      /**
       * Рассчет средней разницы между датами
       * @param $id номер клиента для расчётов
      */
     function calcAverage($id)  {
         try {
             $res_date = $this->dbh->query("SELECT `date` FROM `iwater_orders`  WHERE `local_id` = '$id'  ORDER BY `date` LIMIT 0, 3");
              $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

         } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
         }
         
         $average = 0;
         $date = array();
         $current_date = time() - 86400;

         while ($r_d = $res_date->fetch(PDO::FETCH_ASSOC)) {
             array_push($date, $r_d['date']);
         }

         for ($i = 1; $i < 3 ; $i++) {
             $average = $average + ($date[$i] - $date[$i-1]);
         }

         if (($current_date - max($date)) > (1/3 * $average)) {
             try {
                  $this->dbh->query("UPDATE `iwater_clients` SET `avg_difference` = '$average'/ 3 WHERE `id` =  '$id'");
                  $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }
         }
     }
 }
?>
