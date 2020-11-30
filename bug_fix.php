<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/functions.php');

$dbh = connect_db();

/**
 * Скрипт заполняет дату полследнего заказа в базе клиентов
*/

/**
 * Подготовленный запрос для прописывания данных
*/

try {
   $update_sql = $dbh->prepare("UPDATE `iwater_clients` SET `last_date` = ? WHERE `client_id` = ?");
   $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
   echo 'Some error: ' . $e->getMessage();
}

/**
 * Тянем данные о последних заказах
*/

try {
   $list_order = $dbh->query("SELECT `client_id`, MAX(`date`) AS date FROM `iwater_orders` WHERE `date` < 1543957200 GROUP BY `client_id`");
   $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
   echo 'Some error: ' . $e->getMessage();
}

/**
 * Перебираем полученные данные и пишем в таблицу клиентов
*/

while ($order = $list_order->fetch(PDO::FETCH_ASSOC)) {
   $update_sql->execute(array($order['date'], $order['client_id']));
}

echo 'Success!';

?>
