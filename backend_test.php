<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/functions.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/Classes/PHPExcel.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/Classes/PHPExcel/IOFactory.php');
	$dbh=connect_db();
	
	if($_POST) {
        if (isset($_POST['add_user'])) {
            //Добавить пользователя
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
            $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);

            $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
            $max = 29;
            $size = StrLen($chars) - 1;
            $salt = '$2a$08$';
            while ($max--)
                $salt .= $chars[rand(0, $size)];

            if (CRYPT_BLOWFISH == 1) {
                $hash = crypt($password, $salt);
            }
            try {
                $res = $dbh->query("SELECT count(`login`) FROM `iwater_users` WHERE `login`='" . $login . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $ok = $r['count(`login`)'];
            }

            if ($ok == 0)
            {
                try {
                    $sth = $dbh->prepare("INSERT INTO `iwater_users` (`login`, `password`, `salt`, `name`, `phone`, `role`) VALUES (?, ?, ?, ?, ?, ?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sth->execute(array($login, $hash, $salt, $name, $phone, $role));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
            }
            setActionLog("user", "Добавление", "iwater_user", "Добавлен пользователь: " . $name);
            header('Location: /iwaterTest/');
        }
        if (isset($_POST['login_form'])) {
            //Авторизация
            $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS));
            $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS));
            $error = "";
            try {
                $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `login`='$login'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            if($login !="") {
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $hash = $r['password'];
                    $salt = $r['salt'];
                }
            }
            if (CRYPT_BLOWFISH == 1) {
                if ($hash == crypt($password, $salt)) {
                    $hash_s = sha1($login . time());
                    $_SESSION['fggafdfc'] = $hash_s;
                    try {
                        $res = $dbh->query("UPDATE `iwater_users` SET `session`='$hash_s' WHERE `login`='$login'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                } else {
                    $error = "?err=login";
                }
            }
            header('Location: /iwaterTest' . $error);
        }
        if (isset($_POST['add_role'])) {
            //Добавить роль
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            $arr = $_POST['perms'];
            $perms = json_encode($arr);
            try {
                $res = $dbh->prepare("INSERT INTO `iwater_roles`(`name`, `perms`) VALUES (?, ?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($name, $perms));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            setActionLog("role", "Добавление", "iwater_roles", "Добавлена роль" . $name);
            header('Location: /iwaterTest/');
        }
        if (isset($_POST['list_users'])) {
            //Редактирование списка пользователей
            $z = 0;
            $ze = count($_POST['names']);

            while ($z < $ze) {
                $q_s = '';
                $c_p = false;
                if (strip_tags($_POST['passwords'][$z]) != '') {
                    $q_s = ',`password`=?,`salt`=?';
                    $c_p = true;
                }
                try {
                    $res = $dbh->prepare('UPDATE `iwater_users` SET `name`=?,`phone`=?,`role`=?' . $q_s . ' WHERE id = ?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ($c_p == true) {

                        $password = strip_tags($_POST['passwords'][$z]);
                        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
                        $max = 29;
                        $size = StrLen($chars) - 1;
                        $salt = '$2a$08$';
                        while ($max--)
                            $salt .= $chars[rand(0, $size)];

                        if (CRYPT_BLOWFISH == 1) {
                            $hash = crypt($password, $salt);
                        }

                        $res->execute(array(strip_tags($_POST['names'][$z]), strip_tags($_POST['phones'][$z]), strip_tags($_POST['roles'][$z]), $hash, $salt, strip_tags($_POST['ids'][$z])));
                    } else {
                        $res->execute(array(strip_tags($_POST['names'][$z]), strip_tags($_POST['phones'][$z]), strip_tags($_POST['roles'][$z]), strip_tags($_POST['ids'][$z])));
                    }
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                $z++;
            }

            setActionLog("user", "Редактирование", "iwater_user", "");
            header('Location: /iwaterTest/admin/list_users/');
        }
        if (isset($_POST['add_client']))
        {
            //Добавить клиента
            $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST, 'name'));
            $num_c = trim(filter_input(INPUT_POST, 'num_c', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
                $res = $dbh->prepare("SELECT `client_id` FROM `iwater_clients` WHERE `client_id`='?'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($num_c));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $client_id = $r['client_id'];
            }
            if ($num_c == null) {
                header('Location: /iwaterTest/admin/add_client/');
            } else {
                try {
                    $res = $dbh->prepare("INSERT INTO `iwater_clients`(`type`, `name`, `client_id`) VALUES (?, ?, ?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
                $res->execute(array($type, $name, $num_c));


                $z = count($_POST['region']);
                $i = 0;
                $flagDouble = 0;
                while ($i < $z) {
                    try {
                        $res = $dbh->prepare("INSERT INTO `iwater_addresses`(`client_id`, `contact`, `region`, `address`, `coords`,`full_address`) VALUES (?, ?, ?, ?, ?,?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                    }
                    $region = trim(strip_tags($_POST['region'][$i]));
                    $address = trim(strip_tags($_POST['address'][$i]));
                    $coords = trim(strip_tags($_POST['cords'][$i]));
                    $contact = trim(($_POST['contact'][$i]));
                    $res->execute(array($num_c, $contact, $region, $address, $coords, $region . ', ' . $address));

                    $flagDouble = 0;
                    for ($i2 = 0; $i2 < count($_POST['cords']); $i2++) {
                        if ($_POST['cords'][$i] == $_POST['cords'][$i2]) {
                            $flagDouble++;
                        }
                    }
                    if ($flagDouble >= 2) {
                        break;
                    }
                    $flagDouble = 0;
                    for ($i2 = 0; $i2 < count($_POST['cords']); $i2++) {
                        if ($_POST['address'][$i] == $_POST['address'][$i2]) {
                            $flagDouble++;
                        }
                    }
                    if ($flagDouble >= 2) {
                        break;
                    }
                    $flagDouble = 0;

                    $i++;
                }
                setActionLog("client", "Добавление", "iwater_clients", "Добавление клеинта: " . $num_c);
                header('Location: /iwaterTest/admin/add_client/');
            }
        }

        if (isset($_POST['edit_client'])) {
            //Редактировать клиента
            $id = trim(filter_input(INPUT_POST, 'id_db', FILTER_SANITIZE_SPECIAL_CHARS));
            $num_c = trim(filter_input(INPUT_POST, 'num_c', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON(c.client_id=a.client_id)  WHERE c.id='$id';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            };
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {

                setActionLog("client", "Индивидуальное редактирование", "iwater_clients", "Клиент id: " . $r['id'] . " Старые данные: " . $r['client_id'] . " " . $r['region'] . " " . $r['address'] . " " . $r['coords'] . " " . $r['contact']);
            }

            $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS));
            if ($type == "on") $type = 1;
            $name = trim(filter_input(INPUT_POST, 'name'));

            try {
                $res = $dbh->prepare("UPDATE `iwater_clients` SET `type`=?, `name`=?, `client_id`=? WHERE id='$id';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($type, $name, $num_c));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_addresses` WHERE client_id='$num_c'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($num_c));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }


            $z = count($_POST['region']);
            $i = 0;
            $flagDouble = 0;
            while ($i < $z) {
                try {
                    $res = $dbh->prepare("INSERT INTO `iwater_addresses`(`client_id`, `contact`, `region`, `address`, `coords`,`full_address`) VALUES (?, ?, ?, ?, ?,?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
                $region = trim(strip_tags($_POST['region'][$i]));
                $address = trim(strip_tags($_POST['address'][$i]));
                $coords = trim(strip_tags($_POST['cords'][$i]));
                $contact = trim(($_POST['contact'][$i]));
                $res->execute(array($num_c, $contact, $region, $address, $coords, $region . ', ' . $address));

                $flagDouble = 0;
                for ($i2 = 0; $i2 < count($_POST['cords']); $i2++) {
                    if ($_POST['cords'][$i] == $_POST['cords'][$i2]) {
                        $flagDouble++;
                    }
                }
                if ($flagDouble >= 2) {
                    break;
                }
                $flagDouble = 0;
                for ($i2 = 0; $i2 < count($_POST['cords']); $i2++) {
                    if ($_POST['address'][$i] == $_POST['address'][$i2]) {
                        $flagDouble++;
                    }
                }
                if ($flagDouble >= 2) {
                    break;
                }
                $flagDouble = 0;

                $i++;
            }

			setActionLog("client", "Индивидуальное редактирование", "iwater_clients", "Изменение клиента: ".$num_c);
            header('Location: /iwaterTest/admin/list_clients/');

        }

        if (isset($_POST['delete_client']))
        {
            //Удаление клиента
            $id = trim(filter_input(INPUT_POST, 'delete_client', FILTER_SANITIZE_SPECIAL_CHARS));

            $user_id = setActionLog("client", "Перемещение в корзину", "iwater_clients", "Перемещение в корзину: " . $id);
            try {
                $res = $dbh->prepare("UPDATE `iwater_clients` SET `for_delete`=1, `time_change`=?, `user_changing`=?  WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array(mktime(), $user_id));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `status`=1 WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            echo 1;
        }

        if (isset($_POST['restablish_client']))
        {
            // Восстановление клиента из корзины
            $id = trim(filter_input(INPUT_POST, 'restablish_client', FILTER_SANITIZE_SPECIAL_CHARS));
            $user_id = setActionLog("client", "Восстановление из корзины", "iwater_clients", "Восстановление из корзины: " . $id);
            try {
                $res = $dbh->prepare("UPDATE `iwater_clients` SET `for_delete`=0, `time_change`=?, `user_changing`=? WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array(mktime(), $user_id));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            echo 1;
        }

        if (isset($_POST['destroy_client']))
        {
            // Полное удаление клиента
            $id = trim(filter_input(INPUT_POST, 'delete_client', FILTER_SANITIZE_SPECIAL_CHARS));
            setActionLog("client", "Полное удаление", "iwater_clients", "Полное удаление: " . $id);

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_clients` WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_addresses` WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_orders` WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            echo 1;
        }
        if (isset($_POST['list_clients'])) {
            //Список клиентов
            $json = stripcslashes($_POST['list_clients']);
            $data = json_decode($json, true);
            $data[1] = iconv(mb_detect_encoding($data[1]), "CP1251", $data[1]);
            $data[2] = iconv(mb_detect_encoding($data[2]), "CP1251", $data[2]);
            $query = ' WHERE for_delete = 0';
            if ($data[0] != NULL || $data[1] != NULL || $data[2] != NULL) {
                if ($data[0] != NULL) {

                    if ($data[1] != NULL || $data[2] != NULL) {
                        $query .= ' AND c.client_id LIKE "%' . $data[0] . '%" AND';
                    } else {
                        $query .= ' AND c.client_id LIKE "%' . $data[0] . '%"';
                    }
                }
                if ($data[1] != NULL) {
                    if ($data[0] == NULL) {
                        $query .= ' AND ';
                    }
                    if ($data[2] == NULL) {
                        $query .= ' c.name LIKE "%' . $data[1] . '%"';
                    } else {
                        $query .= ' c.name LIKE "%' . $data[1] . '%" AND';
                    }
                }
                if ($data[2] != NULL) {
                    if ($data[0] == NULL && $data[1] == NULL) {
                        $query .= ' AND ';
                    }
                    $query .= ' a.contact LIKE "%' . $data[2] . '%"';
                }
            }

            $current_page = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $offset = $lists_in_page * ($current_page - 1);

            try {
                $res = $dbh->prepare("SELECT *, c.id AS c_id FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id " . $query . " LIMIT " . $lists_in_page . " OFFSET " . $offset . ";");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $last_id = "";

            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row id='" . $r['c_id'] . "'>";
                $s .= "<cell>" . $r['c_id'] . "</cell>";
                $s .= "<cell></cell>";
                $s .= "<cell>" . $r[type] . "</cell>";
                $s .= "<cell><![CDATA[" . trim(filter_var($r[name], FILTER_SANITIZE_SPECIAL_CHARS)) . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r[client_id] . "]]></cell>";
                try {
                    $res_addr = $dbh->prepare("SELECT * FROM `iwater_addresses` WHERE `client_id` =" . $r['client_id'] . ";");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res_addr->execute();
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r_a = $res_addr->fetch(PDO::FETCH_ASSOC)) {
                    $s .= '<cell class="addr"><cell><![CDATA[' . $r_a[region] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a[address] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . stripcslashes(trim(filter_var($r_a[contact], FILTER_SANITIZE_SPECIAL_CHARS))) . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a[coords] . "]]></cell></cell>";
                }

                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['delete_list_clients'])) {
            try {
                $res = $dbh->prepare("SELECT *, c.id AS c_id, u.login AS u_name, c.name AS c_name FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id LEFT JOIN `iwater_users` AS u ON c.user_changing = u.id  WHERE c.for_delete = 1;");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row id='" . $r['c_id'] . "'>";
                $s .= "<cell>" . $r['c_id'] . "</cell>";
                $s .= "<cell></cell>";
                $s .= "<cell>" . $r[type] . "</cell>";
                $s .= "<cell><![CDATA[" . trim(filter_var($r['c_name'], FILTER_SANITIZE_SPECIAL_CHARS)) . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r[client_id] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['u_name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . date("d/m/Y H:i:s", $r['time_change']) . "]]></cell>";
                try {
                    $res_addr = $dbh->prepare("SELECT * FROM `iwater_addresses` WHERE `client_id` =" . $r['client_id'] . ";");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res_addr->execute();
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r_a = $res_addr->fetch(PDO::FETCH_ASSOC)) {
                    $s .= '<cell class="addr"><cell><![CDATA[' . $r_a[region] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a[address] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . stripcslashes(trim(filter_var($r_a[contact], FILTER_SANITIZE_SPECIAL_CHARS))) . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a[coords] . "]]></cell></cell>";
                }

                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['list_lists'])) {
            //Список клиентов
            $json = stripcslashes($_POST['list_lists']);
            $data = json_decode($json, true);
            $query = "";
            $date = $data;
            if ($date[0] != NULL) {
                $date[0] = explode('.', $date[0]);
                $date[0] = mktime(0, 0, 0, $date[0][1], $date[0][0], $date[0][2]);
                $count = count($data);
                $half_sec_in_day = 86400 / 2;
                if ($date[1] == "") {
                    $query = ' WHERE `date` > ' . strval($date[0] - $half_sec_in_day) . ' AND `date` < ' . strval($date[0] + $half_sec_in_day);
                } else {
                    $date[1] = explode('.', $date[1]);
                    $date[1] = mktime(0, 0, 0, $date[1][1], $date[1][0], $date[1][2]);
                    $query = ' WHERE `date` > ' . strval($date[0] - $half_sec_in_day) . ' AND `date` < ' . strval($date[1] + $half_sec_in_day);
                }

            }
            $current_page = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $offset = $lists_in_page * ($current_page - 1);
            try {
                $res = $dbh->prepare("SELECT DISTINCT l.file, MAX(l.map_num) AS map,l.date AS list_date, l.create_date, u.login, u.id, u2.id AS driver_id, u2.name AS driver_name FROM `iwater_lists` AS l LEFT JOIN `iwater_users` AS u ON l.user_id = u.id LEFT JOIN `iwater_users` AS u2 ON l.driver_id = u2.id " . $query . " GROUP BY l.file ORDER BY `date` DESC LIMIT " . $lists_in_page . " OFFSET " . $offset . ";");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $file = iconv("utf-8", "cp1251", $r['file']);
                $s .= "<row>";
                $s .= '<cell>' . $file . "</cell>";
                $s .= '<cell><a class="xlsx" href="/iwaterTest/files/' . $r['file'] . '">' . "Скачать" . "</a></cell>";
                $s .= "<cell><![CDATA[" . $r['login'] . "]]></cell>";
                $s .= '<cell>' . date("d/m/Y H:i:s", $r['create_date']) . "</cell>";
                $s .= "<cell><![CDATA[" . $r['map'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['driver_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['driver_name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['list_date'] . "]]></cell>";
                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['delete_list'])) {
            $name = (trim(filter_input(INPUT_POST, 'delete_list', FILTER_SANITIZE_SPECIAL_CHARS)));
            unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $name);
            try {
                $res = $dbh->prepare('DELETE FROM `iwater_lists` WHERE `file` LIKE "' . $name . '";');
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
        }
        if (isset($_POST['page_lists'])) {
            //Список клиентов
            $json = stripcslashes($_POST['page_lists']);
            $data = json_decode($json, true);
            $query = "";
            $date = $data;
            if ($date[0] != NULL) {
                $date[0] = explode('.', $date[0]);
                $date[0] = mktime(0, 0, 0, $date[0][1], $date[0][0], $date[0][2]);
                $count = count($data);
                $half_sec_in_day = 86400 / 2;
                if ($date[1] == "") {
                    $query = ' WHERE `date` > ' . strval($date[0] - $half_sec_in_day) . ' AND `date` < ' . strval($date[0] + $half_sec_in_day);
                } else {
                    $date[1] = explode('.', $date[1]);
                    $date[1] = mktime(0, 0, 0, $date[1][1], $date[1][0], $date[1][2]);
                    $query = ' WHERE `date` > ' . strval($date[0] - $half_sec_in_day) . ' AND `date` < ' . strval($date[1] + $half_sec_in_day);
                }

            }
            try {
                $res = $dbh->prepare("SELECT COUNT(DISTINCT `file`) AS count_l FROM `iwater_lists`" . $query . " ORDER BY `date` DESC");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $count_lists = $r['count_l'];
            }

//			$count_pages = 5;
            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $count_pages = ceil($count_lists / $lists_in_page);
            $count_show_pages = 5;
            $active = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            if (!is_int($active)) {
                $active = 1;
            }
            $url = "/iwaterTest/admin/list_lists/";
            $url_page = "/iwaterTest/admin/list_lists/?page=";
            if ($count_pages > 1) { // Всё это только если количество страниц больше 1
                /* Дальше идёт вычисление первой выводимой страницы и последней (чтобы текущая страница была где-то посредине, если это возможно, и чтобы общая сумма выводимых страниц была равна count_show_pages, либо меньше, если количество страниц недостаточно) */
                $left = $active - 1;
                $right = $count_pages - $active;
                if ($left < floor($count_show_pages / 2)) $start = 1;
                else $start = $active - floor($count_show_pages / 2);
                $end = $start + $count_show_pages - 1;
                if ($end > $count_pages) {
                    $start -= ($end - $count_pages);
                    $end = $count_pages;
                    if ($start < 1) $start = 1;
                }
                ?>

                <div id="pagination">
                    <span>Страницы: </span>';
                    <?php if ($active != 1) { ?>
                        <a href="<?= $url ?>" title="Первая страница">&lt;&lt;&lt;</a>
                        <a href="<?php if ($active == 2) { ?><?= $url ?><?php } else { ?><?= $url_page . ($active - 1) ?><?php } ?>"
                           title="Предыдущая страница">&lt;</a>
                    <?php } ?>
                    <?php for ($i = $start; $i <= $end; $i++) { ?>
                        <?php if ($i == $active) { ?><span><?= $i ?></span><?php } else { ?><a
                            href="<?php if ($i == 1) { ?><?= $url ?><?php } else { ?><?= $url_page . $i ?><?php } ?>"><?= $i ?></a><?php } ?>
                    <?php } ?>
                    <?php if ($active != $count_pages) { ?>
                        <a href="<?= $url_page . ($active + 1) ?>" title="Следующая страница">&gt;</a>
                        <a href="<?= $url_page . $count_pages ?>" title="Последняя страница">&gt;&gt;&gt;</a>
                    <?php } ?>
                </div>
                <?php
            }

        }

        if (isset($_POST['page_clients'])) {
            //Список клиентов
            $json = stripcslashes($_POST['page_clients']);
            $data = json_decode($json, true);
            $data[1] = iconv(mb_detect_encoding($data[1]), "CP1251", $data[1]);
            $data[2] = iconv(mb_detect_encoding($data[2]), "CP1251", $data[2]);
            $get = "&";
            if ($data[0] != null || $data[0] != "") $get .= "num=" . $data[0] . "&";
            if ($data[1] != null || $data[1] != "") $get .= "name=" . $data[1] . "&";
            if ($data[2] != null || $data[2] != "") $get .= "cont=" . $data[2] . "&";

            $get = iconv("cp1251", "utf-8", $get);

            $query = ' WHERE for_delete = 0';
            if ($data[0] != NULL || $data[1] != NULL || $data[2] != NULL) {
                if ($data[0] != NULL) {

                    if ($data[1] != NULL || $data[2] != NULL) {
                        $query .= ' AND c.client_id LIKE "%' . $data[0] . '%" AND';
                    } else {
                        $query .= ' AND c.client_id LIKE "%' . $data[0] . '%"';
                    }
                }
                if ($data[1] != NULL) {
                    if ($data[0] == NULL) {
                        $query .= ' AND ';
                    }
                    if ($data[2] == NULL) {
                        $query .= ' c.name LIKE "%' . $data[1] . '%"';
                    } else {
                        $query .= ' c.name LIKE "%' . $data[1] . '%" AND';
                    }
                }
                if ($data[2] != NULL) {
                    if ($data[0] == NULL && $data[1] == NULL) {
                        $query .= ' AND ';
                    }
                    $query .= ' a.contact LIKE "%' . $data[2] . '%"';
                }
            }

            try {
                $res = $dbh->prepare("SELECT COUNT(DISTINCT c.id) AS count_l FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id " . $query . ";");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $count_lists = $r['count_l'];
            }

            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $count_pages = ceil($count_lists / $lists_in_page);
            $count_show_pages = 5;
            $active = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            if (!is_int($active)) {
                $active = 1;
            }
            $url = "/iwaterTest/admin/list_clients/?";
            $url_page = "/iwaterTest/admin/list_clients/?page=";
            if ($count_pages > 1) { // Всё это только если количество страниц больше 1
                /* Дальше идёт вычисление первой выводимой страницы и последней (чтобы текущая страница была где-то посредине, если это возможно, и чтобы общая сумма выводимых страниц была равна count_show_pages, либо меньше, если количество страниц недостаточно) */
                $left = $active - 1;
                $right = $count_pages - $active;
                if ($left < floor($count_show_pages / 2)) $start = 1;
                else $start = $active - floor($count_show_pages / 2);
                $end = $start + $count_show_pages - 1;
                if ($end > $count_pages) {
                    $start -= ($end - $count_pages);
                    $end = $count_pages;
                    if ($start < 1) $start = 1;
                }
                ?>
                <!-- Дальше идёт вывод Pagination -->
                <div id="pagination">
                    <span>Страницы: </span>';
                    <?php if ($active != 1) { ?>
                        <a href="<?= $url . $get ?>" title="Первая страница">&lt;&lt;&lt;</a>
                        <a href="<?php if ($active == 2) { ?><?= $url . $get ?><?php } else { ?><?= $url_page . ($active - 1) . $get ?><?php } ?>"
                           title="Предыдущая страница">&lt;</a>
                    <?php } ?>
                    <?php for ($i = $start; $i <= $end; $i++) { ?>
                        <?php if ($i == $active) { ?><span><?= $i ?></span><?php } else { ?><a
                            href="<?php if ($i == 1) { ?><?= $url . $get ?><?php } else { ?><?= $url_page . $i . $get ?><?php } ?>"><?= $i ?></a><?php } ?>
                    <?php } ?>
                    <?php if ($active != $count_pages) { ?>
                        <a href="<?= $url_page . ($active + 1) . $get ?>" title="Следующая страница">&gt;</a>
                        <a href="<?= $url_page . $count_pages . $get ?>" title="Последняя страница">&gt;&gt;&gt;</a>
                    <?php } ?>
                </div>
                <?php
            }

        }

        if (isset($_POST['list_clients_upd'])) {
            //Обновить данные клиента
            $z = 0;
            $ze = count($_POST['type']);

            try {
                $res = $dbh->prepare('UPDATE `iwater_clients` SET `type`=?,`name`=? WHERE client_id = ?');
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            while ($z < $ze) {
                $address = explode(', ', strip_tags($_POST['address'][$z]));
                $region = $address[0];
                unset($address[0]);
                sort($address);
                $res->execute(array(strip_tags($_POST['type'][$z]), strip_tags($_POST['names'][$z]), strip_tags($_POST['client_id'][$z])));
                $z++;
            }
            setActionLog("client", "Редактирование", "iwater_clients", "");
            header('Location: /iwaterTest/admin/list_clients/');
        }
        if (isset($_POST['add_order'])) {

            //Добавить заказ
            $client_id = trim(filter_input(INPUT_POST, 'client_num', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
                $res = $dbh->query("SELECT count(`id`) FROM `iwater_clients` WHERE `client_id`='" . $client_id . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $ok = $r['count(`id`)'];
            }
            if ($ok || $client_id == "--") {
                $name = trim(filter_input(INPUT_POST, 'name'));
                $region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS));
                if ($region == "default") {
                    try {
                        $res = $dbh->query("SELECT a.region FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id = '$client_id' AND a.address  LIKE '%$address%' LIMIT 1");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $region = $r['region'];
                    }
                }
                $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
                $contact = trim(filter_input(INPUT_POST, 'contact'));
                if ($contact == "" && $client_id != "--") {
                    $contact = get_contact_by_client_id($client_id, $address);
                }
                $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
                $no_date = trim(filter_input(INPUT_POST, 'no_date', FILTER_SANITIZE_SPECIAL_CHARS));
                $time = trim(filter_input(INPUT_POST, 'time'));
                $time_d = trim(filter_input(INPUT_POST, 'time_d', FILTER_SANITIZE_SPECIAL_CHARS));
                $notice = trim(filter_input(INPUT_POST, 'notice'));
                $water_ag = trim(filter_input(INPUT_POST, 'water_ag', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_dp = trim(filter_input(INPUT_POST, 'water_dp', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_e = trim(filter_input(INPUT_POST, 'water_e', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_pl = trim(filter_input(INPUT_POST, 'water_pl', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_other = trim(filter_input(INPUT_POST, 'water_other', FILTER_SANITIZE_SPECIAL_CHARS));
                $equip = trim(filter_input(INPUT_POST, 'equip'));
                $dep = trim(filter_input(INPUT_POST, 'dep', FILTER_SANITIZE_SPECIAL_CHARS));
                $cash_formula = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
                $cash_b_formula = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
                $on_floor = trim(filter_input(INPUT_POST, 'on_floor', FILTER_SANITIZE_SPECIAL_CHARS));
                $tank_b = trim(filter_input(INPUT_POST, 'tank_b', FILTER_SANITIZE_SPECIAL_CHARS));
                $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
                $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
                $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
                $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
                $cords = trim(filter_input(INPUT_POST, 'cords', FILTER_SANITIZE_SPECIAL_CHARS));

                $Cal = new Field_calculate();

                $cash = $Cal->calculate($cash_formula);
                $cash_b = $Cal->calculate($cash_b_formula);

                if ($cords == "") {
                    $cords = null;
                }
                $water_total = $water_ag + $water_dp + $water_e + $water_pl + $water_other;
                $date = explode('/', $date);
                $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
                if ($no_date) {
                    $date = '';
                    $no_date = 1;
                }

                try {
                    $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `name`, `address`, `contact`, `date`, `no_date`, `time`, `period`, `notice`, `water_ag`, `water_dp`, `water_e`, `water_pl`, `water_other`, `water_total`, `equip`, `dep`, `cash`, `cash_b`,`cash_formula`, `cash_b_formula`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `coords`) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ? , ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?,?,?,?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($client_id, $name, $address, $contact, $date, $no_date, $time, $time_d, $notice, $water_ag, $water_dp, $water_e, $water_pl, $water_other, $water_total, $equip, $dep, $cash, $cash_b,$cash_formula, $cash_b_formula, $on_floor, $tank_b, $tank_empty_now, $driver, $status, $reason, $region, $cords));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
                setActionLog("order", "Добавление", "iwater_orders", "Клиент: " . $name . " Дата: " . trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS)) . " Водитель: " . $driver);
                header('Location: /iwaterTest/admin/list_orders/');
            } else {
                header('Location: /iwaterTest/admin/add_order/');
            }
        }
        if (isset($_POST['edit_order'])) {
            $id = trim(filter_input(INPUT_POST, 'db_id', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query("SELECT *,u.name AS d_name, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON(o.driver=u.id)  WHERE o.id='$id';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            };
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {

                setActionLog("order", "Индивидуальное редактирование", "iwater_orders", "Клиент: " . $r['client_id'] . " Водитель: " . $r['name'] . " Старые данные: " . $r['id'] . " " . $r['client_id'] . " " . $r['address'] . " " . $r['contact'] . " " . $r['date'] . " " . $r['no_date'] . " " . $r['time'] . " " . $r['time_d'] . " " . $r['notice'] . " " . $r['water_ag'] . " " . $r['water_dp'] . " " . $r['water_e'] . " " . $r['water_pl'] . " " . $r['water_other'] . " " . $r['water_total'] . " " . $r['equip'] . " " . $r['dep'] . " " . $r['cash'] . " " . $r['cash_b'] . " " . $r['on_floor'] . " " . $r['tank_b'] . " " . $r['tank_empty_now'] . " " . $r['driver'] . " " . $r['status'] . " " . $r['reason'] . " " . $r['region']);
            }


            $id = trim(filter_input(INPUT_POST, 'db_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $client_id = trim(filter_input(INPUT_POST, 'client_num', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST, 'name'));
            $region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS));


            if ($region == "default") {
                try {
                    $res = $dbh->query("SELECT `region` FROM `iwater_clients` WHERE `client_id`='" . $client_id . "'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $region = $r['region'];
                }
            }
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $cords = trim(filter_input(INPUT_POST, 'cords', FILTER_SANITIZE_SPECIAL_CHARS));
            if ($cords == "") {
                $cords = null;
            }

            $contact = trim(filter_input(INPUT_POST, 'contact'));
            if ($contact == "") {
                $contact = get_contact_by_client_id($client_id);
            }
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $no_date = trim(filter_input(INPUT_POST, 'no_date', FILTER_SANITIZE_SPECIAL_CHARS));
            $time = trim(filter_input(INPUT_POST, 'time'));
            $time_d = trim(filter_input(INPUT_POST, 'time_d', FILTER_SANITIZE_SPECIAL_CHARS));
            $notice = trim(filter_input(INPUT_POST, 'notice'));
            $water_ag = trim(filter_input(INPUT_POST, 'water_ag', FILTER_SANITIZE_SPECIAL_CHARS));
            $water_dp = trim(filter_input(INPUT_POST, 'water_dp', FILTER_SANITIZE_SPECIAL_CHARS));
            $water_e = trim(filter_input(INPUT_POST, 'water_e', FILTER_SANITIZE_SPECIAL_CHARS));
            $water_pl = trim(filter_input(INPUT_POST, 'water_pl', FILTER_SANITIZE_SPECIAL_CHARS));
            $water_other = trim(filter_input(INPUT_POST, 'water_other', FILTER_SANITIZE_SPECIAL_CHARS));
            $equip = trim(filter_input(INPUT_POST, 'equip'));
            $dep = trim(filter_input(INPUT_POST, 'dep', FILTER_SANITIZE_SPECIAL_CHARS));
            $cash_formula = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
            $cash_b_formula = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
            $on_floor = trim(filter_input(INPUT_POST, 'on_floor', FILTER_SANITIZE_SPECIAL_CHARS));
            $tank_b = trim(filter_input(INPUT_POST, 'tank_b', FILTER_SANITIZE_SPECIAL_CHARS));
            $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
            $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
            $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
            $water_total = $water_ag + $water_dp + $water_e + $water_pl + $water_other;
            $date = explode('/', $date);
            $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);


            $Cal = new Field_calculate();

            $cash = $Cal->calculate($cash_formula);
            $cash_b = $Cal->calculate($cash_b_formula);

            if ($no_date) {
                $date = '';
                $no_date = 1;
            }

            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `client_id`=?, `name`=?, `address`=?, `coords`=?, `contact`=?, `date`=?, `no_date`=?, `time`=?, `period`=?, `notice`=?, `water_ag`=?, `water_dp`=?, `water_e`=?, `water_pl`=?, `water_other`=?, `water_total`=?, `equip`=?, `dep`=?, `cash`=?, `cash_b`=?,`cash_formula`=?, `cash_b_formula`=?, `on_floor`=?, `tank_b`=?, `tank_empty_now`=?, `driver`=?, `status`=?, `reason`=?, `region`=? WHERE `id`='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($client_id, $name, $address, $cords, $contact, $date, $no_date, $time, $time_d, $notice, $water_ag, $water_dp, $water_e, $water_pl, $water_other, $water_total, $equip, $dep, $cash, $cash_b, $cash_formula, $cash_b_formula, $on_floor, $tank_b, $tank_empty_now, $driver, $status, $reason, $region));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            header('Location: /iwaterTest/admin/list_orders/');
        }
        if (isset($_POST['page'])) {
            if ($_GET{'order'}) {
                //Список заказов
                $extraSQL = "";
                if ($_GET['client_order']) {
                    $client_id = trim(filter_input(INPUT_GET, 'client_order', FILTER_SANITIZE_SPECIAL_CHARS));
                    $extraSQL = "WHERE o.client_id = '$client_id'";
                }
                if ($_GET['no_date_order']) {
                    $extraSQL = 'WHERE o.date = "" ';
                }
                if (isset($_GET['list_order_upd'])) {
                    $half_sec_in_day = 86400 / 2;
                    if ($_GET['from'] != "" || $_GET['to'] != "") {
                        $extraSQL = 'WHERE ';
                    }
                    if ($_GET['from'] != "") {
                        $from = trim(filter_input(INPUT_GET, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
                        $from = explode('/', $from);
                        $from = mktime(0, 0, 0, $from[1], $from[0], $from[2]);
                        $extraSQL .= ' o.date > ' . strval($from - $half_sec_in_day);
                    }
                    if ($_GET['from'] != "" && $_GET['to'] != "") {
                        $extraSQL .= ' AND ';
                    }
                    if ($_GET['to'] != "") {
                        $to = trim(filter_input(INPUT_GET, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
                        $to = explode('/', $to);
                        $to = mktime(0, 0, 0, $to[1], $to[0], $to[2]);
                        $extraSQL .= ' o.date < ' . strval($to + $half_sec_in_day);
                    }
                }
                try {
                    $res = $dbh->query("SELECT *,u.name AS d_name, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) " . $extraSQL . " ORDER BY o.id DESC");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $out = "<?xml version='1.0' encoding='cp1251'?>";
                $out .= '<rows>';
                $out .= '<page></page>';
                $out .= '<total></total>';
                $out .= '<records></records>';
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $newdate = "";
                    if ($r['date'] != "") {
                        $newdate = iconv("cp1251", "utf-8", (date("d/m/Y", $r['date'])));
                    }

                    $out .= "<row id='" . $r['o_id'] . "'>";
                    $out .= '<cell></cell>';
                    $out .= '<cell><![CDATA[' . $r['client_id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['o_name'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['time'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['d_name'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['water_ag'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['water_dp'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['water_e'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['water_pl'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['water_other'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['tank_empty_now'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['equip'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['history'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['notice'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['reason'] . ']]></cell>';
                    $out .= '</row>';
                }
                $out .= '</rows>';
                header("Content-type: text/xml;charset=cp1251");
                echo $out;
            }

            if ($_GET['logs']) {
                if ($_GET['logs'] == "order") {
                    $extraSQL = "WHERE `operation` LIKE '%order%'";
                }
                try {
                    $res = $dbh->query("SELECT *,u.name AS admin FROM `iwater_logs` AS l JOIN `iwater_users` AS u ON(l.user_id=u.id)" . $extraSQL . " ORDER BY l.time DESC");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $out = "<?xml version='1.0' encoding='cp1251'?>";
                $out .= '<rows>';
                $out .= '<page></page>';
                $out .= '<total></total>';
                $out .= '<records></records>';
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $newdate = "";
                    if ($r['time'] != "") {
                        $newdate = iconv("cp1251", "utf-8", (date("d/m/Y H:i:s", $r['time'])));
                    }
                    $out .= "<row id='" . $r['id'] . "'>";
                    $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['login'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['operation'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['action'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['table'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['data'] . ']]></cell>';
                    $out .= '</row>';
                }
                $out .= '</rows>';
                header("Content-type: text/xml;charset=cp1251");
                echo $out;
            }
        }
        if (isset($_POST['oper'])) {
            if ($_POST['oper'] == 'edit') {
                //Обновить данные заказа
                $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                try {
                    $res = $dbh->query("SELECT `client_id`,`name`, `address`,`time`,`notice`,`water_ag`,`water_dp`,`water_e`,`water_pl`,`water_other`,`status`,`water_total`,`equip` FROM `iwater_orders` WHERE `id`='$id' ");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    setActionLog("order", "Редактирование", "iwater_orders", "Клиент:" . $r['client_id'] . " " . $r['name'] . " Старые данные:" . $r['address'] . " " . $r['time'] . " " . $r['water_ag'] . " " . $r['water_dp'] . " " . $r['water_e'] . " " . $r['water_pl'] . " " . $r['water_other'] . " " . $r['status'] . " " . $r['water_total'] . " " . $r['equip']);
                }

                $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
                $time = trim(filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS));
                $notice = trim(filter_input(INPUT_POST, 'notice', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_ag = trim(filter_input(INPUT_POST, 'water_ag', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_dp = trim(filter_input(INPUT_POST, 'water_dp', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_e = trim(filter_input(INPUT_POST, 'water_e', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_pl = trim(filter_input(INPUT_POST, 'water_pl', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_other = trim(filter_input(INPUT_POST, 'water_other', FILTER_SANITIZE_SPECIAL_CHARS));
                $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
                $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
                $equip = trim(filter_input(INPUT_POST, 'equip', FILTER_SANITIZE_SPECIAL_CHARS));
                $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_total = $water_ag + $water_dp + $water_e + $water_pl + $water_other;
                $name = iconv(mb_detect_encoding($name), "CP1251", $name);
                $address = iconv(mb_detect_encoding($address), "CP1251", $address);
                $equip = iconv(mb_detect_encoding($equip), "CP1251", $equip);
                $notice = iconv(mb_detect_encoding($notice), "CP1251", $notice);
                $time = iconv(mb_detect_encoding($time), "CP1251", $time);

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `name`=?,`address`=?,`time`=?,`notice`=?,`water_ag`=?,`water_dp`=?,`water_e`=?,`water_pl`=?,`water_other`=?, `tank_empty_now`=?,`status`=?,`water_total`=?,`equip`=? WHERE `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($name, $address, $time, $notice, $water_ag, $water_dp, $water_e, $water_pl, $water_other, $tank_empty_now, $status, $water_total, $equip, $id));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
            }
            if ($_POST['oper'] == 'del') {
                $order = array();
                try {
                    $res = $dbh->prepare('SELECT * FROM `iwater_orders` WHERE `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $order = $r;
                }

                try {
                    $res = $dbh->prepare('DELETE FROM `iwater_orders` WHERE `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                setActionLog("order", "Удаление", "iwater_clients", "№заказа: " . $order['id'] . " Клиент: " . $order['client_id'] . " Дата: " . $order['date'] . " Время: " . $order['time']);
            }
        }
        if (isset($_POST['add_list'])) {
            //Добавить путевой лист
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = explode('/', $date);
            $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

            try {
                $res = $dbh->prepare("SELECT count(`id`) as count, date, file FROM `iwater_lists` WHERE `date`='$date'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $exist = $r['count'];
            }
//			if(1){
            if ($exist == 0 || isset($_POST['extra_list_exist'])) {
                try {
                    $res = $dbh->prepare("SELECT max(`id`) FROM `iwater_lists`");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute();
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $list_id = $r['max(`id`)'] + 1;
                }
                if ($list_id == 1) {
                    try {
                        $res = $dbh->prepare("SELECT max(`list`) FROM `iwater_orders`");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute();
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $list_id = $r['max(`list`)'] + 1;
                    }
                }
                if ($_POST['driver'] != "All") {
                    try {
                        $res = $dbh->prepare("SELECT `id` FROM `iwater_lists` WHERE `date`='$date'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute();
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $list_id = $r['id'];
                    }
                }

                $regions = array('Санкт-Петербург');
                $regions = implode("','", $regions);
                try {
                    $res = $dbh->prepare("SELECT o.id, u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver=u.id) WHERE `date`=? AND `region` IN ('" . $regions . "')");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                    $drivers[] = $r['driver_n'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $map_num = 2;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
//					$map_num++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }

                $regions = array('Красное Село', 'Ломоносов', 'Горелово', 'Стрельна', 'Петергоф', 'Кронштадт');
                $regions = implode("','", $regions);
                try {
                    $res = $dbh->prepare("SELECT `id` FROM `iwater_orders` WHERE `date`=? AND `region` IN ('" . $regions . "')");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $ids_order = null;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $map_num = 1;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
//					$map_num++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }

                $regions = array('Пушкин', 'Колпино', 'Коммунар', 'Металлострой', 'Павловск', 'Шушары', 'Ленинградская область');
                $regions = implode("','", $regions);
                try {
                    $res = $dbh->prepare("SELECT `id` FROM `iwater_orders` WHERE `date`=? AND `region` IN ('" . $regions . "')");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $ids_order = null;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $map_num = 0;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
//					$map_num++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT DISTINCT u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver=u.id) WHERE list=? ");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($list_id));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                $i = 0;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
//					if($_POST['driver'] == $r['driver_id']) {
                    $driversInList[$i][] = $r['driver_id'];
                    $driversInList[$i][] = $r['driver_n'];
                    $i++;
//					}
                }
                $file = date('j.m.Y', $date) . '.xlsx';
                $dbh = connect_db();
                if (isset($_SESSION['fggafdfc'])) {
                    $session = array();
                    try {
                        $sth = $dbh->prepare("SELECT `id`, `login`,`name`  FROM  `iwater_users` WHERE `session`=?");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sth->execute(array(($_SESSION['fggafdfc'])));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                    while ($r = $sth->fetch(PDO::FETCH_ASSOC)) {
                        $session = $r;
                    }
                }
                if ((@fopen($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file, "r"))) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file);
                }
                try {
                    $sth = $dbh->prepare("INSERT INTO `iwater_lists`(`date`, `file`,`user_id`,`create_date`, `map_num`) VALUES (?, ?, ?, ?,?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sth->execute(array($date, $file, $session['id'], mktime(), $list_id));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
                for ($k = 0; $k < count($driversInList); $k++) {
                    $file_d = date('j.m.Y', $date) . '(driver)' . iconv("CP1251", "UTF-8", $driversInList[$k][1]) . '.xlsx';
                    if ((@fopen($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file_d, "r"))) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file_d);
                    }

                    try {
                        $sth = $dbh->prepare("INSERT INTO `iwater_lists`(`date`, `file`,`user_id`,`create_date`, `map_num`, `driver_id`) VALUES (?, ?, ?, ?,?,?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sth->execute(array($date, $file_d, $session['id'], mktime(), $list_id . "?driver_id=" . $driversInList[$k][0], $driversInList[$k][0]));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                    }
                }

//				$file = createExcelFile($date);
//				$driversFiles = array();
//				for($k = 0; $k<count($driversInList);$k++){
//					$driversFiles[$k] = createExcelFile($date, $driversInList[$k][0], $driversInList[$k][1] );
//				}
                $file = date('j.m.Y', $date) . '.xlsx';
                ?>
                <form method="post" id="lists" action="/iwaterTest/lists">
                    <input name="date" type="hidden" value=<?php echo $date ?>>
                    <input name="list_id" type="hidden" value=<?php echo $list_id ?>>
                    <input name="count" type="hidden" value=<?php echo count($driversInList) ?>>
                    <?php for ($k = 0; $k < count($driversInList); $k++) { ?>
                        <input name="<?php echo "driver_id_" . $k ?>" type="hidden"
                               value="<?php echo $driversInList[$k][0] ?>">
                        <input name="<?php echo "driver_name_" . $k ?>" type="hidden"
                               value="<?php echo $driversInList[$k][1] ?>">
                    <?php } ?>
                </form>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
                <script>
                    $('#lists').submit();
                </script>
                <?php
//				$out.='<div style="display: inline-block">Общий путевой лист: '.' </div>'.'<div style="display: inline-block"><a href="/iwaterTest/files/'.$file.'">XLSX</a><a href="/iwaterTest/map/'.$list_id.'/"> Карта</a></div>';
//				for($k = 0; $k<count($driversInList);$k++){
//					$file = date('j.m.Y',$date).$driversInList[$k][1].'.xlsx';
//					$out.='<br><div style="display: inline-block">Путевой лист водителя: : '.$driversInList[$k][1]." ".' </div>'.'<div style="display: inline-block"><a href="/iwaterTest/files/'.$file.'"> XLSX</a><a href="/iwaterTest/map/'.$list_id.'/"> Карта</a></div>';
//				}
//
//				echo $out;

            } else {
                ?>
                <form method="post" id="extraList" action="/iwaterTest/backend.php">
                    <input name="date" type="hidden" placeholder="Дата путевого" value=<?php echo $_POST['date'] ?>>
                    <input name="driver" type="hidden" value=<?php echo $_POST['driver'] ?>>
                    <input name="add_list" type="hidden">
                    <input name="extra_list_exist" type="hidden">
                </form>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
                <script>
                    var result = confirm("Путевой лист на дату ранее был сформирован, заменить?");
                    if (result) {
                        $('#extraList').submit();
                    } else {
                        document.location.href = '/iwaterTest/admin/add_list/';
                    }
                </script>
                <?php
//				header('Location: /iwaterTest/admin/add_list/');
            }

        }
        if (isset($_POST['client_num_l'])) {
            //Поиск id клиента по имени
            $client_num = trim(filter_input(INPUT_POST, 'client_num_l', FILTER_SANITIZE_SPECIAL_CHARS));
            $array_digits = $client_num;
            if (strlen($client_num) > 1) {
                $array_digits = "%";
                for ($i = 0; $i < strlen($client_num); $i++) {
                    $array_digits .= $client_num[$i] . "%";
                }
            }
            try {
                $dbh->query('SET CHARACTER SET utf8');
                $res = $dbh->query("SELECT DISTINCT c.client_id,c.name,a.address FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.client_id LIKE ('$array_digits')");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $json = array();
            $i = 0;
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $json[] = array(
                    'value' => $r['client_id'],
                    'label' => $r['client_id'],
                    'desc' => $r['name'] . " | " . $r['address']
                );
            }

            $response = json_encode($json);
            echo $response;
//			var_dump($response);
            die();
        }
        if (isset($_POST['client_num_s'])) {
            //Поиск адреса и названия клиента по id
            $client_num = trim(filter_input(INPUT_POST, 'client_num_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id = '$client_num' AND a.address  LIKE '%$address%' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row>";
                $s .= "<cell><![CDATA[" . $r['client_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['address'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['region'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . stripcslashes($r['contact']) . "]]></cell>";
                $s .= "</row>";
            }

            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['name_l'])) {
            //Поиск имени клиента
            $name = trim(filter_input(INPUT_POST, 'name_l', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = iconv(mb_detect_encoding($name), "CP1251", $name);
            try {
//				$dbh->query('SET CHARACTER SET utf8');
                $res = $dbh->query("SELECT DISTINCT c.name,c.client_id,a.address FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.name LIKE ('%$name%')");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $json = array();
            $i = 0;
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $json[] = array(
                    'value' => iconv("cp1251", "utf-8", $r['name']),
                    'label' => iconv("cp1251", "utf-8", $r['name']),
                    'desc' => iconv("cp1251", "utf-8", $r['client_id'] . " |  " . $r['address'])
                );
            }
            $response = json_encode($json);
            echo $response;
            die();
        }
        if (isset($_POST['name_s'])) {
            //Поиск id и адреса по имени клиента
            $name = trim(filter_input(INPUT_POST, 'name_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = iconv(mb_detect_encoding($name), "CP1251", $name);
            $name = html_entity_decode($name);
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.name = '$name' AND a.address LIKE '%$address%' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row>";
                $s .= "<cell><![CDATA[" . $r['client_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['address'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['region'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['contact'] . "]]></cell>";
                $s .= "</row>";
            }

            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['address_l'])) {
            //Поиск адреса
            $address = trim(filter_input(INPUT_POST, 'address_l', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT DISTINCT a.address,c.client_id,c.name FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE a.address LIKE ('%$address%')");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $json = array();
            $i = 0;
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $json[] = array(
                    'value' => iconv("cp1251", "utf-8", $r['address']),
                    'label' => iconv("cp1251", "utf-8", $r['address']),
                    'desc' => iconv("cp1251", "utf-8", $r['client_id'] . " | " . $r['name'])
                );
            }
            $response = json_encode($json);
            echo $response;
            die();
        }
        if (isset($_POST['address_s'])) {
            //Поиск имени и id клиента по адресу
            $address = trim(filter_input(INPUT_POST, 'address_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE a.address = '$address' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row>";
                $s .= "<cell><![CDATA[" . $r['client_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['address'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['region'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['contact'] . "]]></cell>";
                $s .= "</row>";
            }

            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['list_p'])) {
            //Координаты заказов
            $list_id = trim(filter_input(INPUT_POST, 'list_p', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $extraSQL = "";
            $list_id = explode('?', $list_id);
            $list_id = $list_id[0];
            $extraSQL .= $list_id;
            if ($driver_id != "") {
                $extraSQL .= " AND `driver` =" . $driver_id;
            }
            if(isset($_POST['exception_driver'])){
                $exc = $_POST['exception_driver'];
                for($i=0;$i<count($exc);$i++){
                    $extraSQL.=" AND `driver` !=" . $exc[$i];
                }
            }
            try {
                $res = $dbh->query("SELECT *,a.coords AS cor_p, o.coords AS cor_new, o.id AS order_id, u.name as driver_name  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND `list` =" . $extraSQL . " AND o.status=0 ORDER BY map_num, o.id DESC");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row>";
                if ($r['cor_new'] != null) {
                    $s .= "<cell id='cord' class='temp'><![CDATA[" . $r['cor_new'] . "]]></cell>";
                } else {
                    $s .= "<cell id='cord'><![CDATA[" . $r['cor_p'] . "]]></cell>";
                }
                $s .= "<cell id='time'><![CDATA[" . $r['time'] . "]]></cell>";
                $s .= "<cell id='tank_b'><![CDATA[" . $r['water_total'] . "]]></cell>";
                $s .= "<cell id='client_id'><![CDATA[" . $r['client_id'] . "]]></cell>";
                $s .= "<cell id='period'><![CDATA[" . $r['period'] . "]]></cell>";
                $s .= "<cell id='id'><![CDATA[" . $r['order_id'] . "]]></cell>";
                $s .= "<cell id='driver_name'><![CDATA[" . $r['driver_name'] . "]]></cell>";
                $s .= "<cell id='changed_driver'><![CDATA[" . $r['changed_driver'] . "]]></cell>";
                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['createExcell'])) {
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_n = trim(filter_input(INPUT_POST, 'driver_n', FILTER_SANITIZE_SPECIAL_CHARS));
            createExcelFile($date, $driver_id, $driver_n);
            setActionLog("list", "Формирование", "iwater_lists", "На дату: " . date('j.m.Y', $date));
        }
        if (isset($_POST['unique'])) {
            $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS));
            $value = trim(filter_input(INPUT_POST, 'value', FILTER_SANITIZE_SPECIAL_CHARS));
            if (isset ($_POST['current_id'])) {
                if ($_POST['current_id'] == $_POST['value']) {
                    return true;
                }
            }
            $response = 0;
            switch ($_POST['unique']) {
                case "client_id":
                    try {
                        $res = $dbh->query("SELECT COUNT(`id`) AS count FROM `iwater_clients` WHERE `client_id`='$value' ");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $response = $r['count'];
                    }
                    $response = ($response != "0") ? true : false;
                    break;
            }
            echo $response;
        }
        if (isset($_POST['change_coords_in_list'])) {
            $order_id = trim(filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $coords = $_POST['change_coords_in_list'];
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `coords`='$coords[0],$coords[1]' WHERE `id`=?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($order_id));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            setActionLog("order", "Изменение координат", "iwater_orders", "Заказ: " . $order_id);
        }
        if (isset($_POST['change_date'])) {
            $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $time = trim(filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS));
            $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = explode('/', $date);
            $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_orders` WHERE `id`='$id' ");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $order = array();
            $notice = "";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                setActionLog("order", "Изменение даты", "iwater_order", "Клиент: " . $r['client_id'] . " Причина: " . $reason . " Старая дата: " . date('j.m.Y', $r['date']) . "Новая Дата: " . date('j.m.Y', $date));
                $notice = $r['history'];
                $old_date = $r['date'];
                $order = $r;
            }

            try {
                $res = $dbh->query("SELECT `id` FROM `iwater_orders` ORDER BY `id` DESC LIMIT 1 ");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $last_number = $r['id'];
            }

            $old_notice = $notice . " Перенесён с " . date('j.m.Y', $old_date) . " на " . date('j.m.Y', $date) . ". Номер нового заказа: " . ($last_number + 1);
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `reason`='$reason',`history`='$old_notice', `status`=3 WHERE `id`=?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($id));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $new_notice = " Перенесён с " . date('j.m.Y', $old_date) . " на " . date('j.m.Y', $date) . ". Номер cтарого заказа: " . $id;

            try {
                $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `name`, `address`, `contact`, `date`, `no_date`, `time`, `period`, `notice`, `water_ag`, `water_dp`, `water_e`, `water_pl`,`water_other`, `water_total`, `equip`, `dep`, `cash`, `cash_b`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `history`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?,?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?,?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($order['client_id'], $order['name'], $order['address'], $order['contact'], $date, 0, $order['time'], $order['period'], $order['notice'], $order['water_ag'], $order['water_dp'], $order['water_e'], $order['water_pl'], $order['water_other'], $order['water_total'], $order['equip'], $order['dep'], $order['cash'], $order['cash_b'], $order['on_floor'], $order['tank_b'], $order['tank_empty_now'], "0", $order['status'], $order['reason'], $order['region'], $notice . $new_notice));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            header('Location: /iwaterTest/admin/list_orders/');
        }
        if (isset($_POST['check_order'])) {
            $client_id = trim(filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $response = "null";
            try {
                $res = $dbh->query(" SELECT o.id AS o_id FROM `iwater_orders` AS o 
                                          LEFT JOIN `iwater_users` AS u ON o.driver = u.id 
                                          WHERE o.client_id='$client_id'  
                                          AND o.address = '$address'
                                          ORDER BY o.id DESC LIMIT 1;");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $response = $r['o_id'];
            }
            if($response == "null"){
                try {
                    $res = $dbh->query(" SELECT o.id AS o_id FROM `iwater_orders` AS o 
                                          LEFT JOIN `iwater_users` AS u ON o.driver = u.id 
                                          WHERE o.client_id='$client_id'  
                                          ORDER BY o.id DESC LIMIT 1;");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $response = $r['o_id'];
                }
            }


            echo $response;
        }
        if (isset($_POST['fill_order'])) {
            $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query(" SELECT *, u.id AS u_id FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON o.driver = u.id  WHERE o.id='$id' ORDER BY o.id DESC LIMIT 1;");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $regions = array('В работе', 'Отмена', 'Доставлен', 'Перенос');
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row>";
                $s .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['time'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['period'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . htmlspecialchars_decode($r['notice']) . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['water_ag'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['water_dp'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['water_e'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['water_pl'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['water_other'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . htmlspecialchars_decode($r['equip']) . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['dep'] . ']]></cell>';
                if($r['cash_formula'] == ""){
                    $s .= '<cell><![CDATA[' . $r['cash'] . ']]></cell>';
                }else{
                    $s .= '<cell><![CDATA[' . $r['cash_formula'] . ']]></cell>';
                }
                if($r['cash_b_formula'] == ""){
                    $s .= '<cell><![CDATA[' . $r['cash_b'] . ']]></cell>';
                }else{
                    $s .= '<cell><![CDATA[' . $r['cash_b_formula'] . ']]></cell>';
                }
                $s .= '<cell><![CDATA[' . $r['on_floor'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['tank_b'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['tank_empty_now'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['u_id'] . ']]></cell>';
                $s .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                $s .= "</row>";
            }

            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;

        }
        if (isset($_POST['statuses_order_list'])) {
            $extraSQL = " WHERE ";
            for ($i = 0; $i < count($_POST['statuses_order_list']); $i++) {
                if ($i > 0) $extraSQL .= " OR ";
                $extraSQL .= " `id`=" . $_POST['statuses_order_list'][$i];
            }
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `status`=?" . $extraSQL);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($_POST['status']));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
        }
        if (isset($_POST['order_excel_file'])) {
            $from = trim(filter_input(INPUT_POST, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
            $to = trim(filter_input(INPUT_POST, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
            $order_excel_file = trim(filter_input(INPUT_POST, 'order_excel_file', FILTER_SANITIZE_SPECIAL_CHARS));
            createOrderExcelFile($from, $to, $order_excel_file);
            setActionLog("order", "Формирование Excel файла", "iwater_order", "На дату: " . $from . " " . $to);
        }
        if (isset($_POST['change_driver_in_map'])) {
            $driver = trim(filter_input(INPUT_POST, 'change_driver_in_map', FILTER_SANITIZE_SPECIAL_CHARS));
            $orders = $_POST['orders'];
            $sql = ' WHERE';
            $orders_id = "";
            for ($i = 0; $i < count($orders); $i++) {
                $orders_id .= ' ' . $orders[$i] . ' ';
                $sql .= ' `id` = ' . $orders[$i];
                if (count($orders) == $i + 1) {
                    break;
                }
                $sql .= ' OR ';
            }


            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `changed_driver`=1, `driver`=?" . $sql);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($driver));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }

            setActionLog("map", "Изменение водителя", "iwater_orders", "заказы: " . $orders_id);

            echo 1;
        }
        if (isset($_POST['driver_map_info'])) {
            $list_id = trim(filter_input(INPUT_POST, 'list', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
            $extraSQL = "";
            $list_id = explode('?', $list_id);
            $list_id = $list_id[0];
            $extraSQL .= $list_id;
            if ($driver_id != "" && $driver_id != "all") {
                $extraSQL .= " AND `driver` =" . $driver_id;
            }

            try {
//                $res=$dbh->query("SELECT *,a.coords AS cor_p, o.coords AS cor_new, o.id AS order_id, u.name as driver_name  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND `list` =".$extraSQL." AND o.status=0 ORDER BY map_num, o.id DESC");
                $res = $dbh->query("SELECT u.id as driver_id, u.name as driver_name, SUM(o.water_total) AS total,COUNT(*) AS count1  
                                    FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) 
                                    LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) 
                                    LEFT JOIN `iwater_users` as u ON (o.driver = u.id) 
                                    WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0)
                                    AND `list` =" . $extraSQL . " AND o.status=0 
                                    GROUP BY driver_name ORDER BY map_num, o.id DESC");
//                print_r($res);
//                die();

                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $array = array();
            $i = 0;
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $array[$i] = array();
                array_push($array[$i],iconv("cp1251", "utf-8",$r['driver_name']));
                array_push($array[$i],$r['total']);
                array_push($array[$i],$r['count1']);
                array_push($array[$i],$r['driver_id']);
                $i++;
            }
            print_r(json_encode($array));
        }
        if (isset($_POST['add_route_to_DB'])) {
            $data = $_POST['data'];
            try{
                $res=$dbh->prepare("UPDATE `iwater_orders` SET `number_visit`=? WHERE `id`=?");
                $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                for($i=1,$j=1;$i<count($data);$i++) {
                    if(isset($data[$i]) || $data[$i]!="") {
                        if(isset($data[$i][6])) {
                            $res->execute(array($j, $data[$i][6]));
                            $j++;
                        }
                    }
                }
            }
            catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $map = trim(filter_input(INPUT_POST, 'map', FILTER_SANITIZE_SPECIAL_CHARS));
            $map = explode("/",$map);
            $map = $map[5];

            try {
                $res = $dbh->query(" SELECT `file` FROM `iwater_lists` AS l WHERE l.map_num='$map';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $file = $r['file'];
                unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file);
            }

            die();

        }
         if (isset($_POST['update_total'])) {
            if(isset($_POST['cash'])){
                $formula = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
            }else {
                $formula = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
            }
             $Cal = new Field_calculate();

             $total = $Cal->calculate($formula);
             echo $total;
             die();

         }
        if (isset($_POST['settings'])) {
            $e_mail = trim(filter_input(INPUT_POST, 'e_mail', FILTER_SANITIZE_SPECIAL_CHARS));
            $arr_mail = explode(",",$e_mail);
            $arr_mail = json_encode($arr_mail);

            try {
                $res = $dbh->prepare("UPDATE `iwater_settings` SET `data`='".$arr_mail."' WHERE `name`='email_to_smtp'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            setActionLog("settings", "Изменение e-mail", "iwater_settings", "Изменение настроек е-майла".$arr_mail );
            header('Location: /iwaterTest/admin/settings/');
        }

    }
	if($_GET){
		if(isset($_GET['ban'])){
				//Бан пользователя
				$id=trim(filter_input(INPUT_GET, 'ban', FILTER_SANITIZE_SPECIAL_CHARS));
				try{
					$res=$dbh->prepare("UPDATE `iwater_users` SET `ban`='1' WHERE `id`=?");
					$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
					$res->execute(array($id));
				}
				catch (Exception $e) {
					echo 'Подключение не удалось: ' . $e->getMessage();		
				}
				setActionLog("user", "Блокировка пользователя", "iwater_users", "Клиент c id: ".$id." забанен ");


			header('Location: /iwaterTest/admin/list_users/');
		}
		if(isset($_GET['logout'])){
			session_destroy();
			header('Location: /iwaterTest');
		}
	}
	//header('Location: /iwaterTest/');

function setActionLog($operation, $action, $table, $data){
	$dbh=connect_db();
	if(isset($_SESSION['fggafdfc'])) {
		$session=array();
		try {
			$sth = $dbh->prepare("SELECT `id`, `login`,`name`  FROM  `iwater_users` WHERE `session`=?");
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sth->execute(array(($_SESSION['fggafdfc'])));
		}
		catch (Exception $e) {
			echo 'Подключение не удалось: ' . $e->getMessage();
		}
		while($r = $sth->fetch(PDO::FETCH_ASSOC)) {
			$session = $r;
		}
	}
	try{
		$sth = $dbh->prepare("INSERT INTO `iwater_logs`(`user_id`, `operation`, `action`, `table`, `data`, `time`) VALUES (?, ?, ?, ?, ?, ?)");
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$sth->execute(array($session['id'], $operation, $action, $table, $data, mktime()));

	}
	catch (Exception $e) {
		echo 'Подключение не удалось: ' . $e->getMessage().'</br>';
	}
	return $session['id'];
}

function createExcelFile($date, $driver = "", $driver_name = ""){
	$extraName = "";
	$extraSQL = "";
	if($driver != ""){
		$extraName = '(driver)'.$driver_name;
		$extraSQL = " AND u.id =".$driver;
	}
	$file = iconv ("UTF-8","CP1251",date('j.m.Y',$date).$extraName.'.xlsx');

	$filename = '/iwaterTest/files/'.$file;
	if ((@fopen($_SERVER['DOCUMENT_ROOT'].$filename, "r"))) {
		echo $file;
		return 1;
	}
	
	$objPHPExcel = PHPExcel_IOFactory::load("files/blank.xlsx");
	$objPHPExcel->getActiveSheet()->setCellValue('A3',date('j.m.Y',$date));
	$dbh=connect_db();


	try{
		$res=$dbh->prepare("SELECT *,o.address AS o_address, o.contact AS o_contact, o.name as client_name ,u.name AS driver_n, c.type AS type, (SELECT COUNT(o2.client_id) FROM `iwater_orders` AS o2 WHERE  o.client_id = o2.client_id GROUP BY o2.client_id) AS count_orders FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON(driver=u.id) LEFT JOIN `iwater_clients` AS c ON o.client_id = c.client_id  WHERE `date`=?".$extraSQL." AND o.status=0 ORDER BY map_num, o.id DESC");
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$res->execute(array($date));
	}
	catch (Exception $e) {
		echo 'Подключение не удалось: ' . $e->getMessage();
	}
	if($driver_name != ""){
		$objPHPExcel->getActiveSheet()->setCellValue('E2',  $driver_name);
	}
	$x=6;
	$newMapNum = 0;

	$styleArray_forNew = array(
		'fill' => array(
			'color' => array('rgb' => '000000')
		),
		'font'  => array(
			'color' => array('rgb' => 'FFFFFF')
		));

    $styleArray_forTransfer = array(
        'fill' => array(
            'color' => array('rgb' => 'DFC204')
        )
        );
	while($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if($r['history'] != "") {
            $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $x . ':U' . $x)
                ->applyFromArray($styleArray_forTransfer);
        }
		$newMapNum++;
//					$objPHPExcel->getActiveSheet()->insertNewRowBefore($x, 1);
//		$objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $r['map_num']);

		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $newMapNum);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$x, iconv('windows-1251', 'utf-8',($r['client_name'])));
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$x, $r['client_id']);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')
			->setAutoSize(true);

		if($r['count_orders'] == 1){
				$objPHPExcel->getActiveSheet()
					->getStyle('C' . $x)
					->applyFromArray($styleArray_forNew);
		}
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$x, iconv('windows-1251', 'utf-8', $r['o_address']));
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$x, iconv('windows-1251', 'utf-8',($r['o_contact'])));
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$x, iconv('windows-1251', 'utf-8',($r['time'])));
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$x, iconv('windows-1251', 'utf-8', $r['driver_n']));
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$x, "");
		if($r['water_ag'] == 0) $r['water_ag'] = "";
		if($r['water_dp'] == 0) $r['water_dp'] = "";
		if($r['water_e'] == 0) $r['water_e'] = "";
		if($r['water_pl'] == 0) $r['water_pl'] = "";
		if($r['water_other'] == 0) $r['water_other'] = "";
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$x, $r['water_ag']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$x, $r['water_dp']);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$x, $r['water_e']);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$x, $r['water_pl']);
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$x, $r['water_other']);
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$x, $r['water_total']);
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$x, iconv('windows-1251', 'utf-8', $r['dep']));
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')
			->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$x, $r['cash']);
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$x, $r['cash_b']);
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$x, $r['on_floor']);
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$x, iconv('windows-1251', 'utf-8', $r['equip']));
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$x, iconv('windows-1251', 'utf-8', $r['notice']));
        $objPHPExcel->getActiveSheet()->setCellValue('U'.$x, $r['number_visit']);
		$objPHPExcel->getActiveSheet()->getRowDimension($x)->setRowHeight(-1);

		$styleArray = array(
			'font'  => array(
				'color' => array('rgb' => 'FF0000')
			));
		if($r['type']==1){
			$objPHPExcel->getActiveSheet()
				->getStyle('A' . $x . ':U' . $x)
				->applyFromArray($styleArray);
		}

		if($r['history'] != "") {
            $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $x . ':U' . $x)
                ->applyFromArray($styleArray_forTransfer);
        }
        if($r['history'] != "") {
            $objPHPExcel->getActiveSheet()
                ->getStyle('D' . $x . ':G' . $x)
                ->applyFromArray($styleArray_forTransfer);
        }

		$x++;
	}


	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('files/'.iconv ("CP1251","UTF-8",$file));
	
	echo $file;
}

function createOrderExcelFile($from, $to, $all){
	$objPHPExcel = PHPExcel_IOFactory::load("files/order_blank.xlsx");
	$dbh=connect_db();

	$file = 'order_from_';

	$from=explode('/',$from);
	$file.=strval($from[0]). strval($from[1]). strval($from[2]);
	$from=mktime(0, 0, 0, $from[1], $from[0], $from[2]);

	$to=explode('/',$to);
	$file.='_to_'.strval($to[0]). strval($to[1]). strval($to[2]).'.xlsx';
	$to=mktime(23, 59, 59, $to[1], $to[0], $to[2]);
	$extraSQL ="";
	if($all != "all"){
		$extraSQL = "WHERE  o.date >= ? AND o.date <= ?";

	}

	try{
		$res=$dbh->prepare("SELECT *,o.name as client_name ,u.name AS driver_n FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON(driver=u.id) ".$extraSQL." ORDER BY o.date, o.id");
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$res->execute(array($from, $to));
	}
	catch (Exception $e) {
		echo 'Подключение не удалось: ' . $e->getMessage();
	}

	$x=4;
	$newMapNum = 0;

	while($r = $res->fetch(PDO::FETCH_ASSOC)) {
//					$objPHPExcel->getActiveSheet()->insertNewRowBefore($x, 1);
//		$objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $r['map_num']);
		$date = date('j.m.Y',$r['date']);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $date);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$x, iconv('windows-1251', 'utf-8',($r['client_name'])));
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$x, $r['client_id']);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$x, iconv('windows-1251', 'utf-8', $r['address']));
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$x, iconv('windows-1251', 'utf-8',($r['contact'])));
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$x, iconv('windows-1251', 'utf-8',($r['time'])));
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$x, iconv('windows-1251', 'utf-8', $r['driver_n']));
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$x, $r['tank_empty_now']);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$x, $r['water_ag']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$x, $r['water_dp']);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$x, $r['water_e']);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$x, $r['water_pl']);
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$x, $r['water_other']);
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$x, $r['water_total']);
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$x, iconv('windows-1251', 'utf-8', $r['dep']));
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$x, $r['cash']);
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$x, $r['cash_b']);
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$x, $r['on_floor']);
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$x, iconv('windows-1251', 'utf-8', $r['equip']));
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$x, iconv('windows-1251', 'utf-8', $r['notice']));
		$x++;
	}


	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	if($all=="all"){
		$objWriter->save('files/all_order.xlsx');
	}else {
		$objWriter->save('files/' . $file);
	}

	return $file;
}

function get_contact_by_client_id($client_id, $address){
	$dbh=connect_db();
	$contact = "";
	try{
		$res=$dbh->prepare("SELECT c.name, a.contact FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id =? AND a.address  LIKE '%$address%' LIMIT 1");
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$res->execute(array($client_id));
	}
	catch (Exception $e) {
		echo 'Подключение не удалось: ' . $e->getMessage();
	}
	while($r = $res->fetch(PDO::FETCH_ASSOC)) {
		$contact = $r['contact'];
	}
	return $contact;
}
?>