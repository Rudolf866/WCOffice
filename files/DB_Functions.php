<?php

// error_reporting(E_ALL);
// ini_set('display_errors',1);
// mb_internal_encoding("UTF-8");

class DB_Functions {

	private $dbh;

	function __construct() {
        require_once 'DB_Connect.php';
        /** Подключение к базе данных
				*/
        $db = new DB_Connect();
        $this->dbh = $db->connect_db();
        $this->dbh->query("SET NAMES 'UTF8'");
    }

    // destructor
    function __destruct() {

    }

    /** Функция авторизации пользователя
	 */
    public function auth($login, $password, $company, $notification) {

    	try {
    		$res = $this->dbh->prepare("SELECT `session`, `password`, `salt`, `id` FROM `iwater_driver` WHERE `login` = ? AND `company` = ?");
    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		$res->execute(array($login, $company));


	    	while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
	    		$combo = $r['salt'] . $password;

	    		if (hash('sha512', $combo) == $r['password']) {
	    			//Проверка пароля

	    			if ($notification != '') {
	    				try {
		    				$up = $this->dbh->query("UPDATE `iwater_driver` SET `notification` = '$notification' WHERE `login` = '$login'");
		    				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    			} catch (Exception $e) {
		    				$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
	    					$error->execute(array(time(), $e->getMessage()));
		    			}
	    			}
	    			return array('session' => $r['session'], 'id' => $r['id']);
	    		} else {
	    			return false;
	    		}
	    	}
    	} catch (Exception $e) {
    		$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
    		$error->execute(array(time(), $e->getMessage()));
    		return false;
    	}
    }

    /** Получение путевого листа по сессии водителя и дате листа
    */
    public function getDriverList($session, $id) {
			try {
				$period = $this->dbh->query("SELECT `period`, `timing` FROM `iwater_company` AS c LEFT JOIN `iwater_driver` AS d ON (c.id = d.company) WHERE session = '$session'");
				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (Exception $e) {
				$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
				$error->execute(array(time(), $e->getMessage()));
    			return false;
			}
			$period_decoder = array();
			$p = $period->fetch(PDO::FETCH_ASSOC);
			$period_array = json_decode($p['period'], TRUE);
			$timing_array = json_decode($p['timing'], TRUE);

			foreach ($period_array as $key => $value) {
					$period_decoder[$value['unit']] = $timing_array[$key]['unit'];
			}

			try {
				$res = $this->dbh->prepare("SELECT DISTINCT o.id AS order_id, `water_equip`, o.contact, o.name, o.cash, o.notice, o.date, o.period, o.address, o.status, a.coords AS cor_p, o.coords AS cor_new FROM `iwater_orders` AS o LEFT JOIN `iwater_driver` AS d ON (o.driver = d.id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) WHERE d.session = ? AND o.list = ? ORDER BY map_num, o.id DESC");
				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$res->execute(array($session, $id));
			} catch (Exception $e) {
				$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
				$error->execute(array(time(), $e->getMessage()));
				return false;
			}

    	$outList = array();
    	$i = 0;

    	while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
    		$outList[$i]['id'] = $r['order_id'];

			$order_list = '';
			$string = unserialize($r['water_equip']);

			foreach ($string as $key => $value) {
				try {
					$un = $this->dbh->query("SELECT `name` FROM `iwater_units` WHERE `id` = '$key'");
					$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				} catch (Exception $e) {
					$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
					$error->execute(array(time(), $e->getMessage()));
	    		return false;
	    	}

				$unn = $un->fetch();
				$order_list .= '
' . $unn['name'] . '  x' . $value;
			}

			$contact = preg_replace('/[^0-9 ]/', '', $r['contact']);
			$contact = explode(" ", $contact);
			$contact_phone = "";

			foreach ($contact as $key => $value) {
				if (strlen($value) == 11 && $value[0] == "8") {
					$contact_phone .= "+7" . substr($value, 1) . ";";
				} else if (strlen($value) == 10 && $value[0] == "9") {
					$contact_phone .= '+7' . $value . ";";
				} else if (strlen($value) == 11 && $value[0] == "7") {
					$contact_phone .= "+" . $value . ";";
				} else if (strlen($value) == 12 && $value[0] == "+") {
					$contact_phone .= $value . ";";
				} else if (strlen($value) == 7 || strlen($value) == 6) {
					$contact_phone .= $value . ";";
				}
			}

			$contact_phone = substr($contact_phone, 0, -1);

	    	$outList[$i]['name'] = $r['name'];
	    	$outList[$i]['order'] = $order_list;
	    	$outList[$i]['cash'] = $r['cash'];
	    	$outList[$i]['contact'] = $contact_phone;
	    	$outList[$i]['notice'] = $r['notice'];
    		$outList[$i]['date'] = date('d/m/Y', $r['date']);
    		$outList[$i]['period'] = $period_decoder[$r['period']];
    		$outList[$i]['address'] = $r['address'];

			if ($r['cor_new'] != null) {
				$outList[$i]['coords'] = $r['cor_new'];
			} else {
				$outList[$i]['coords'] = $r['cor_p'];
			}

			if ($r['status'] < 3) {
    			$outList[$i]['status'] = 0;
    		} else {
    			$outList[$i]['status'] = 1;
    		}
    		$i++;
    	}

    	return $outList;
    }

	/** Получение информации о днях на которые были путевые листы
	*/
	public function driverHistory($session) {

		$current_date = mktime(0,0,0);

		try {
			$res = $this->dbh->prepare("SELECT DISTINCT `date`, `list` FROM `iwater_orders` AS o LEFT JOIN `iwater_driver` AS d ON (o.driver = d.id) WHERE d.session = ? AND `date` < '$current_date' ORDER BY `date` DESC LIMIT 10");
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$res->execute(array($session));
		} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
			$error->execute(array(time(), $e->getMessage()));
		}

		$temp = array();

		while ($r = $res->fetch(PDO::FETCH_ASSOC)) { // За то, как это выглядит ответственен Android разработчик, мы сильно спешим с реализацией нескольких путевых листов на одну дату
			// и в итоге он/она хочет, чтобы id путевых на одну дату перечислялись через запятую
			if (array_key_exists($r['date'], $temp)) {
				$temp[$r['date']] .= "," . $r['list'];
			} else {
				$temp[$r['date']] = $r['list'];
			}
		}

		return $temp;
	}

	/** Получение информации о днях на которые были путевые листы
	*/
	public function todayList($session) {

		$current_date = mktime(0,0,0);

		try {
			$res = $this->dbh->prepare("SELECT DISTINCT `list` FROM `iwater_orders` AS o LEFT JOIN `iwater_driver` AS d ON (o.driver = d.id) WHERE d.session = ? AND `date` = '$current_date' ORDER BY `date` DESC");
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$res->execute(array($session));
		} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
			$error->execute(array(time(), $e->getMessage()));
		}

		$temp = "";

		while ($r = $res->fetch(PDO::FETCH_ASSOC)) { // За то, как это выглядит ответственен Android разработчик, мы сильно спешим с реализацией нескольких путевых листов на одну дату
			$temp .= $r['list'] . ",";
		}

		$temp = substr($temp, 0, -1);

		return $temp;
	}

    /** Подтверждение заказа
    */
    public function orderAccept($id, $tank, $comment, $coord, $delinquency) {
		$water_array = array(); // Список воды для расчёта количества сданной тары
		$current_time = time(); // Текущее время для записи в отчёт
		if ($delinquency != '') { $current_time = $delinquency; }

		try {
			 $res = $this->dbh->query("SELECT * FROM `iwater_units` WHERE `shname` IS NOT NULL");
			 $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
		  $error->execute(array(time(), $e->getMessage()));
		  return false;
		}

		while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
			 array_push($water_array, $r['id']);
		}

    	try {
    		$res = $this->dbh->prepare("INSERT INTO `iwater_dcontrol` (`order_id`, `time`, `coord`, `tank`, `notice`) VALUES (?, ?, ?, ?, ?);");
    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		$res->execute(array($id, $current_time, $coord, $tank, $comment));
    	} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
			$error->execute(array(time(), $e->getMessage()));
    		return false;
    	}

		try {
    		$res = $this->dbh->prepare("UPDATE `iwater_orders` SET `status` = '3' WHERE `id` = ?;");
    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		$res->execute(array($id));
    	} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
			$error->execute(array(time(), $e->getMessage()));
    		return false;
    	}

		// Получение количества тары в заказе и у клиента
		try {
    		$res = $this->dbh->query("SELECT `tanks`, `water_equip`, c.id AS client_id FROM `iwater_clients` AS c LEFT JOIN `iwater_orders` AS o ON (o.client_id = c.id) WHERE o.id = " . $id);
    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
			$error->execute(array(time(), $e->getMessage()));
    		return false;
    	}

		$result = $res->fetch(PDO::FETCH_ASSOC);
		$tank_move = $result['tanks']; // Значение перемещения тары, отрицательное если водитель забрал тары больше чем привёз, то оно отрицательное
		$water_equip = unserialize($result['water_equip']);

		foreach ($water_equip as $key => $value) {
			if (in_array($key, $water_array)) {
				 $tank_move += $value;
			}
		}

		$tank_move -= $tank;

		try {
    		$res = $this->dbh->prepare("UPDATE `iwater_clients` SET `tanks` = ? WHERE `id` = ?");
    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$res->execute(array($tank_move, $result['client_id']));
    	} catch (Exception $e) {
			$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
			$error->execute(array(time(), $e->getMessage()));
    		return false;
    	}

    	return true;
    }

		/** ВСЕ МЕТОДЫ НИЖЕ ТОЛЬКО НА СТАДИЮ ТЕСТИРОВАНИЯ И СОЗДАНЫ ЛИШЬ ДЛЯ ТОГО, ЧТОБЫ НЕ ОТВЛЕКАТЬ "ПРОГРАММИСТА" ОТ ОСНОВНОЙ РАБОТЫ */

		/** Функция, чтобы меня не трогали
		*/
		public function blackOut($yea) {
			try {
				$res = $this->dbh->query("UPDATE `iwater_orders` SET `status` = 0");
				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (Exception $e) {
				$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
				$error->execute(array(time(), $e->getMessage()));
    		return false;
			}

			return true;
		}

		/** Обновить даты
		*/
		public function dateUpdater($yea) {
			$date = date('d/m/Y');
		  $date = explode('/', $date);
			$timestamp = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

			try {
				$res = $this->dbh->query("UPDATE `iwater_orders` SET `date` = " . $timestamp . " WHERE `list` = 8222");
				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (Exception $e) {
				$error = $this->dbh->prepare("INSERT INTO `iwater_app_error`(`date`, `text`) VALUES(?, ?)");
				$error->execute(array(time(), $e->getMessage()));
    		return false;
			}

			return true;
		}
}
?>
