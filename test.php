<?php
// error_reporting(E_ALL);
// ini_set('display_errors',1);

require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/functions.php');

$dbh = connect_db();

/**
 * Список переменных
 */
 $driver_list = array(); // массив количества нарушений водителей
 $periods = array(); // список периодов доставки

try {
	$res = $dbh->query('SELECT * FROM `iwater_company`');
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
	echo 'Подключение не удалось: ' . $e->getMessage();
}

while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
	$period = json_decode($r['period']);
	$timing = json_decode($r['timing']);

	foreach ($period as $key => $value) {
		$time = explode('-', $timing[$key]->unit);
		$periods[$r['id']][$value->unit] = $time[1];
	}
}

try {
    $res = $dbh->query('SELECT * FROM `iwater_driver`');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
   echo 'Подключение не удалось: ' . $e->getMessage();
}

while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
   $driver_list[$r['id']]['finished'] = 0; //Завершённых заказов
   $driver_list[$r['id']]['good'] = 0; // Завершённых без нарушений
   $driver_list[$r['id']]['violation'] = 0; // Нарушений
   $driver_list[$r['id']]['all'] = 0; // Всего заказов

   /**
    * Заполнение данных о водителе, для дальнейших подсчётов
    */
   try {
      $order = $dbh->query("SELECT o.period, o.status, d.time AS d_time, o.date FROM `iwater_orders` AS o LEFT JOIN `iwater_dcontrol` AS d ON (o.id = d.order_id) WHERE `driver` = " . $r['id']);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (Exception $e) {
      echo 'Подключение не удалось: ' . $e->getMessage();
   }

   while ($o = $order->fetch(PDO::FETCH_ASSOC)) {
      $driver_list[$r['id']]['all']++;
      if ($periods[$r['company_id']][$o['period']] > date('H:i', $o['d_time']) && $o['status'] == 0) {
         $driver_list[$r['id']]['violation']++;
      } else if ($periods[$r['company_id']][$o['period']] < date('H:i', $o['d_time']) && $o['status'] == 2) {
         $driver_list[$r['id']]['finished']++;
			$driver_list[$r['id']]['violation']++;
      } else {
			$driver_list[$r['id']]['finished']++;
         $driver_list[$r['id']]['good']++;
      }
   }
}

/**
 * Запись результатов аналитики в базу
*/

try {
   $write_stat = $dbh->prepare("UPDATE `iwater_driver` SET `stat` = ? WHERE `id` = ?");
   $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
   echo 'Подключение не удалось: ' . $e->getMessage();
}

foreach ($driver_list as $key => $value) {
   $current_stat = $value['all'] / ($value['finished'] - $value['violation']) + ($value['all'] / $value['good']);
   $write_stat->execute(array(round(($current_stat / 3), 2), $key));
}

echo "1";
