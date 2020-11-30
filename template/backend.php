<?php

   // error_reporting(E_ALL);
   // ini_set('display_errors',1);

    // ключ от firebase
    define("API_ACCESS_KEY", "AAAAGB4Wiws:APA91bFULbzx6kdNXtwGy8k1fuA6-t_HcSffLexDg7PZGz99CuUtXLUpylQ-CEXdndpk2qDmBkaR2sjHWRSx-QfjIYVdbg_88lbcPLcCK9M2QKK8X7AxN2LQtOEw2V4YkbMgd0VzHHbc");

    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/functions.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/Classes/PHPExcel.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/Classes/PHPExcel/IOFactory.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/Classes/PHP_XLSXWritter/xlsxwriter.class.php');

    $dbh=connect_db();

    if ($_POST) {
        if (isset($_POST['login_form'])) {
            //Авторизация

            $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS));
            $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS));
            $error = "";

            $sess = '';
            try {
                $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `login`='$login'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            if ($login !="") {
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $hash = $r['password'];
                    $salt = $r['salt'];
                    $sess = $r['session'];
                }
            }
           if ($hash == crypt($password, $salt)) {
              $hash_s = sha1($login . time());
              $_SESSION['fggafdfc'] = $hash_s;
              try {
                  $res = $dbh->query("UPDATE `iwater_users` SET `session`='$hash_s' WHERE `login`='$login'");
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
              }
            }

            header('Location: /iwaterTest' . $error);
        }
        if (isset($_POST['select_role'])) {
           // Получить список полей с их привелегиями
           $role = trim(filter_input(INPUT_POST, 'select_role', FILTER_SANITIZE_SPECIAL_CHARS));

           try {
             $res = $dbh->query("SELECT `perms` FROM `iwater_roles` WHERE `id` = " . $role);
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $r = $res->fetch(PDO::FETCH_ASSOC);
           $perms = json_decode($r['perms']);

           try {
             $res = $dbh->query("SELECT * FROM `iwater_perms`;");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          $output_array = array();

          $i = 0;
          while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
             array_push($output_array, array($r['name'], $r['title'], $perms[$i]));
             $i++;
          }

          echo json_encode($output_array);
        }
        if (isset($_POST['update_role'])) {
           // Обновляем данные о роли
           $role = trim(filter_input(INPUT_POST, 'update_role', FILTER_SANITIZE_SPECIAL_CHARS));
           $name = trim(filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_SPECIAL_CHARS));
           $perms = trim(filter_input(INPUT_POST, 'perms_list'));

           // $perms = stripcslashes($perms); // Удаляем экранирование

           try {
              $res = $dbh->prepare("UPDATE `iwater_roles` SET `perms` = ?, `name` = ? WHERE `id` = ?");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $res->execute(array($perms, $name, $role));
           echo $role;
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
                    $res = $dbh->prepare('UPDATE `iwater_users` SET `name`=?,`phone`=?,`role`=?' . $q_s . ' WHERE id = ? ');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ($c_p == true) {
                        $password = strip_tags($_POST['passwords'][$z]);
                        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
                        $max = 29;
                        $size =($chars) - 1;
                        $salt = '$5$rounds=5000$';
                        while ($max--) {
                            $salt .= $chars[rand(0, $size)];
                        }
                        $hash = crypt($password, $salt);

                        $res->execute(array(strip_tags($_POST['names'][$z]), strip_tags($_POST['phones'][$z]), strip_tags($_POST['roles'][$z]), $hash, $salt, strip_tags($_POST['ids'][$z])));
                    } else {
                        $res->execute(array(strip_tags($_POST['names'][$z]), strip_tags($_POST['phones'][$z]), strip_tags($_POST['roles'][$z]), strip_tags($_POST['ids'][$z])));
                    }
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                // обновление данных водителя в таблицы iwater_user and iwater_driver
                try {
                    // $res = $dbh->prepare('UPDATE `iwater_driver` SET `login`=?'. $q_s . ' WHERE id = ? ');
                    $res = $dbh->prepare('UPDATE `iwater_driver` SET `password`=?,`salt`=?  WHERE id = ? ');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ($c_p == true) {
                        $password = strip_tags($_POST['passwords'][$z]);
                        //$password = filter_input(INPUT_POST, $password, FILTER_SANITIZE_SPECIAL_CHARS);
                        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
                        $max = 29;
                        // $size = StrLen($chars) - 1;
                        $size = ($chars) - 1;
                        $salt = '$5$rounds=5000$';
                        while ($max--) {
                            $salt .= $chars[rand(0, $size)];
                        }
                        //$hash = crypt($password, $salt);
                        $password = hash('sha512', $salt . $password);// хешируем пароль для водителя

                        //$res->execute(array(strip_tags($_POST['names'][$z]), $hash, $salt, strip_tags($_POST['ids'][$z])));
                        //$res->execute(array($hash, $salt, strip_tags($_POST['ids'][$z])));
                        $res->execute(array($password, $salt, strip_tags($_POST['ids'][$z])));
                    } else {
                        // $res->execute(array(strip_tags($_POST['names'][$z]), $hash, $salt, strip_tags($_POST['ids'][$z])));
                        $res->execute(array(strip_tags($_POST['ids'][$z])));
                    }
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }


                $z++;
            }

            setActionLog("user", "Редактирование", "iwater_user", "");
            header('Location: /iwaterTest/admin/list_users/');
        }
        if (isset($_POST['edit_provider'])) {
           $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
           $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
           $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS));

            $sess = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $sess->fetch();
            $company = $usersess['company_id'];

           try{
             $res = $dbh->prepare("UPDATE `iwater_providers` SET `name` = ?, `contact` = ? WHERE id = ?");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }
           $res->execute(array($name, $contact, $id));

          setActionLog("provider", "Редактирование", "iwater_providers", "Редактирование контрагента: " . $name);
          header('Location: /iwaterTest/');
        }
        if (isset($_POST['select_period'])) {
            //Вызвать список периодов
            try {
                $res = $dbh->query("SELECT `period`, `timing`, `color` FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (c.id = u.company_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $r = $res->fetch();

            echo '{"period":"' . addslashes($r['period']) . '","timing":"' . addslashes($r['timing']) . '","color":"' . addslashes($r['color']) . '"}';
        }
        if (isset($_POST['change_period'])) {
            //Обновить периоды
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $periods = stripcslashes(trim(filter_input(INPUT_POST, 'change_period')));
            $colors = stripcslashes(trim(filter_input(INPUT_POST, 'change_color')));
            try {
                $res = $dbh->prepare("UPDATE `iwater_company` SET `period` = ?, color = ? WHERE `id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $res->execute(array($periods, $colors));

            setActionLog("company", "Изменение", "iwater_company", "Изменены периоды доставки");
            // header('Location: /iwaterTest/');
        }
        if (isset($_GET['category_list'])) {
           $page = $_POST['page'];
           $limit = $_POST['rows'];
           $sidx = $_POST['sidx'];
           $sord = $_POST['sord'];

           $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
           $usersess = $res->fetch();
           $company = $usersess['company_id'];

           $today_unixtime = strtotime((string)date('d.m.Y'));

           try {
             $storage = $dbh->query("SELECT s.id, s.name FROM `iwater_storage` AS s_agr JOIN `iwater_storage_agr` AS s ON (s_agr.id = s.id) WHERE s.company_id = '$company' AND s.date_finish > " . $today_unixtime);
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          $count = $storage->rowCount();
          $total_pages = 1;

          $out = "<?xml version='1.0' encoding='utf8'?>";
          $out .= '<rows>';
          $out .= '<page>' . $page . '</page>';
          $out .= '<total>' . $total_pages . '</total>';
          $out .= '<records>' . $count . '</records>';
          while ($s = $storage->fetch(PDO::FETCH_ASSOC)) {
              $out .= '<row id="' . $s['id'] . '">';
              $out .= '<cell><![CDATA[' . $s['name'] . ']]></cell>';
              $out .= '</row>';
          }
          $out .= '</rows>';
          header("Content-type: text/xml;charset=utf8");
          echo $out;
        }


/**
  *****
  ********
  *************
  *******************
  * КОМАНДЫ НА УДАЛЕНИЕ ДАННЫХ
  *******************
  ****************
  ************
  **********
  ********
  ******
  ****
  ***
  **
  *
*/

        if (isset($_POST['add_role'])) { // Добавить роль
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
        if (isset($_POST['add_provider'])) { // Добавить поставщика
           $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
           $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS));

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

           try {
             $dbh->query("INSERT INTO `iwater_providers` (`company_id`,`name`, `contact`) VALUES('$company', '$name', '$contact')");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          setActionLog("provider", "Добавление", "iwater_providers", "Добавление контрагента: " . $name);
          header('Location: /iwaterTest/');
        }
        if (isset($_POST['add_company'])) { // Добавить компанию
            $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            $city = trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS));
            $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS));
            $schedule = trim(filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_SPECIAL_CHARS));
            $regions = trim(filter_input(INPUT_POST, 'regions', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
                $res = $dbh->prepare("INSERT INTO `iwater_company` (`id`, `name`, `city`, `region`, `contact`, `period`, `schedule`, `regions`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $res->execute(array($id, $name, $city, $city, $contact, '[{"unit":"Утром"},{"unit":"Днём"},{"unit":"До полудня"},{"unit":"Вечером"},{"unit":"До полуночи"}]', $schedule, $regions));

            setActionLog("company", "Добавление", "iwater_company", "Добавление компании: " . $id);
            header('Location: /iwaterTest/admin/add_company/');
        }
        if (isset($_POST['storage_arrival'])) { // Управление приходами на склад
           $storage = trim(filter_input(INPUT_POST, 'storage', FILTER_SANITIZE_SPECIAL_CHARS));
           $unit = trim(filter_input(INPUT_POST, 'unit', FILTER_SANITIZE_SPECIAL_CHARS));
           $count = trim(filter_input(INPUT_POST, 'count', FILTER_SANITIZE_SPECIAL_CHARS));
           $source = trim(filter_input(INPUT_POST, 'source', FILTER_SANITIZE_SPECIAL_CHARS));
           $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS));

           $source_status = 0; // Проверка, есть ли у источника достаточно ресурсов

           /**
            * Определяем интерфейсы к базе
           */
           try {
              $res = $dbh->prepare("SELECT * FROM `iwater_storage_count` WHERE `unit_id` = ? AND `storage` = ?");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           try {
             $up = $dbh->prepare("UPDATE `iwater_storage_count` SET `count` = ?, `unit_id` = ? WHERE `storage` = ?");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           try {
              $history = $dbh->prepare("INSERT INTO `iwater_storage_history`(`unit_id`, `storage`, `operation`, `count`, `comment`, `date`) VALUES (?, ?, ?, ?, ?, " . time() . ")");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           try {
             $up_in = $dbh->prepare("INSERT INTO `iwater_storage_count`(`unit_id`, `storage`, `shelf_life`, `count`, `last_update`) VALUES (?, ?, ?, ?, ?)");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           /**
            * Проверка, хватит и в случае переноса товара на источнике
           */
           if ($source > 0) {
              $res->execute(array($unit, $source));

              if ($res->rowCount() > 0) {
                 echo '<script>alert("Недостаточно товаров на складе"); window.location = "/iwaterTest/"; </script>';
              } else {
                 $r = $res->fetch(PDO::FETCH_ASSOC);
                 if ($r['count'] > $count) {
                    $source_status = $r['count'];
                 } else {
                    echo '<script>alert("Недостаточно товаров на складе"); window.location = "/iwaterTest/"; </script>';
                 }
              }
           }

           $res->execute(array($unit, $storage));
           $r = $res->fetch(PDO::FETCH_ASSOC);

           if ($res->rowCount() > 0) {
              $current_count = $r['count'];
              $up->execute(array($current_count + $count, $unit, $storage));

              if ($count > 0) {
                 $history->execute(array($unit, $storage, 'Приход', $count, $comment));
              } else {
                 $history->execute(array($unit, $storage, 'Расход', $count, $comment));
              }

              if ($source > 0) {
                 $up->execute(array($current_count - $count, $unit, $source));
                 $history->execute(array($unit, $storage, 'Перенос', $count, $comment));
              }
           } else {
              if ($source == 0) {
                 $up_in->execute(array($unit, $storage, 9999999999999, $count, time()));
                 if ($count > 0) {
                    $history->execute(array($unit, $storage, 'Приход', $count, $comment));
                 } else {
                    $history->execute(array($unit, $storage, 'Расход', $count, $comment));
                 }
              } else {
                 $up->execute(array($current_count - $count, $unit, $source));
                 $history->execute(array($unit, $storage, 'Перенос', $count, $comment));
              }
          }

          setActionLog("storage", "Приход на склад", "iwater_storage_count", "Изменено количество товара на складе");
          header('Location: /iwaterTest/admin/storage_arrival/');
        }
        if (isset($_POST['add_client'])) { // Добавить клиента
            $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST, 'name'));
            $num_c = trim(filter_input(INPUT_POST, 'num_c', FILTER_SANITIZE_SPECIAL_CHARS));

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $res = $dbh->prepare("SELECT `client_id` FROM `iwater_clients` WHERE `client_id`='?' AND `company_id` = '$company'");
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
                    $res = $dbh->prepare("INSERT INTO `iwater_clients`(`type`, `name`, `client_id`, `company_id`) VALUES (?, ?, ?, ?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
                }
                $res->execute(array($type, $name, $num_c, $company));


                $z = count($_POST['region']);
                $i = 0;
                $flagDouble = 0;
                while ($i < $z) {
                    try {
                        $res = $dbh->prepare("INSERT INTO `iwater_addresses`(`client_id`, `contact`, `region`, `address`, `coords`,`full_address`) VALUES (?, ?, ?, ?, ?, ?)");
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
                setActionLog("client", "Добавление", "iwater_clients", "Добавление клиента: " . $num_c);
                header('Location: /iwaterTest/admin/add_client/');
            }
        }
        if (isset($_POST['add_order'])) { // Добавить заказ
           $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
           $usersess = $res->fetch();
           $company = $usersess['company_id'];

           $mobile = trim(filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_SPECIAL_CHARS));
           $client_id = trim(filter_input(INPUT_POST, 'client_num', FILTER_SANITIZE_SPECIAL_CHARS));
           $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
           $formula = trim(filter_input(INPUT_POST, 'cash_formula', FILTER_SANITIZE_SPECIAL_CHARS));

           try {
               $res = $dbh->query("SELECT count(`id`) FROM `iwater_clients` WHERE `client_id`='" . $client_id . "'");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $r = $res->fetch(PDO::FETCH_ASSOC);
           $ok = $r['count(`id`)'];

           if ($ok || $client_id == "--") {
               $name = trim(filter_input(INPUT_POST, 'name'));
               $region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS));
               $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
               $contact = trim(filter_input(INPUT_POST, 'contact'));
               $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
               $no_date = trim(filter_input(INPUT_POST, 'no_date', FILTER_SANITIZE_SPECIAL_CHARS));
               $time = trim(filter_input(INPUT_POST, 'time'));
               $time_d = trim(filter_input(INPUT_POST, 'time_d', FILTER_SANITIZE_SPECIAL_CHARS));
               $notice = trim(filter_input(INPUT_POST, 'notice'));
               $water_equip = trim(filter_input(INPUT_POST, 'water_equip'));
               $equip = trim(filter_input(INPUT_POST, 'equip'));
               $dep = trim(filter_input(INPUT_POST, 'dep', FILTER_SANITIZE_SPECIAL_CHARS));
               $cash = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
               $cash_b = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
               $on_floor = trim(filter_input(INPUT_POST, 'on_floor', FILTER_SANITIZE_SPECIAL_CHARS));
               $storage = trim(filter_input(INPUT_POST, 'storage', FILTER_SANITIZE_SPECIAL_CHARS));
               $tank_b = trim(filter_input(INPUT_POST, 'tank_b', FILTER_SANITIZE_SPECIAL_CHARS));
               $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
               $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
               $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
               $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
               $cords = trim(filter_input(INPUT_POST, 'cords', FILTER_SANITIZE_SPECIAL_CHARS));

               $cords = $cords != "" ? $cords : null;

               $contact = ($contact == "" && $client_id != "--") ? get_contact_by_client_id($client_id, $address) : $contact;


               if ($region == "default") {
                   try {
                       $res = $dbh->query("SELECT a.region as rg FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id = '$client_id' AND a.address  LIKE '%$address%' LIMIT 1");
                       $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   } catch (Exception $e) {
                       echo 'Подключение не удалось: ' . $e->getMessage();
                   }
                   while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                       $region = $r['rg'];
                   }
               }

               $date = explode('/', $date);
               $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

               if ($no_date) {
                   $date = '';
                   $no_date = 1;
               }

               $last_update = time();
               $id_count_db = "";
               $old_unit_id = "";
               $old_storage = "";
               $operation = "Расход";
               $comment = "Новый звказ";
               $old_count = "";
               $old_date = "";
               $id_exist = "";
               $count_product = "";

               try {
                  $res_up_history = $dbh->prepare("INSERT INTO `iwater_storage_history` (`unit_id`, `storage`, `operation`, `count`, `comment`, `date`) VALUES(?, ?, ?, ?, ?, ?)");
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               } catch (Exception $e) {
                  echo "Подключение не удалось: " . $e->getMessage();
               }

               try {
                  $res_up_count = $dbh->prepare('UPDATE `iwater_storage_count` SET `count` = ?, `last_update`=? WHERE `id`=?');
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
               }

               try {
                  $res_count = $dbh->query("SELECT * FROM `iwater_storage_count` WHERE storage = " . $storage . "");
                  $dbh->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE);
               } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
               }

               $equip_array = unserialize($water_equip);

               while ($r = $res_count->fetch(PDO::FETCH_ASSOC)) {
                  if (array_key_exists($r['unit_id'], $equip_array)) {
                    try {
                        $res = $dbh->query("SELECT * FROM `iwater_storage_count` WHERE `id` = " . $r['id']);
                        $dbh->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                    if ($r['count'] < $equip_array[$r['unit_id']]) {
                       $eq_count = 0;
                    } else {
                       $eq_count = $r['count'] - $equip_array[$r['unit_id']];
                    }
                    $res_up_history->execute(array($r['unit_id'], $r['storage'] , $operation, $r['count'], $comment, $r['last_update']));
                    $res_up_count->execute(array($eq_count, $last_update, $r['id'] ));
                    unset($equip_array[$r['unit_id']]);
                  }
               }
               $storage_info = "";
               foreach ($equip_array as $key => $value) {
                  $storage_info .= ' нету такого ' . $key . ' с = ' . $value;
               }

               try {
                   $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `company_id`, `name`, `address`, `contact`, `date`, `no_date`, `time`, `period`, `notice`, `water_equip`, `equip`, `dep`, `cash`, `cash_b`,`cash_formula`, `cash_b_formula`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `coords`, `mobile`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                   $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   $res->execute(array($client_id, $company, $name, $address, $contact, $date, $no_date, $time, $time_d, $notice, $water_equip, $equip, $dep, $cash, $cash_b, $formula, $formula, $on_floor, $tank_b, $tank_empty_now, $driver, $status, $reason, $region, $cords, $mobile));
               } catch (Exception $e) {
                   echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
               }

               setActionLog("order", "Добавление", "iwater_orders", "Клиент: " . $name . " Дата: " . trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS)) . " Водитель: " . $driver);
               if(!empty($storage_info)) {
                  echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <script charset="utf-8"> alert("Недостаточно товаров на складе"); location.href = "/iwaterTest/admin/list_orders/"; </script>';
               } else {
                  header('Location: /iwaterTest/admin/list_orders/');
               }
           } else {
               header('Location: /iwaterTest/admin/add_order/');
           }
      }
      if (isset($_POST['add_storage'])) {
         $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
         $usersess = $res->fetch();
         $company = $usersess['company_id'];

         $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
         $priority = trim(filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_SPECIAL_CHARS));
         $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
         $storeman = trim(filter_input(INPUT_POST, 'storeman', FILTER_SANITIZE_SPECIAL_CHARS));
         $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS));

         try {
          $dbh->query("INSERT INTO `iwater_storage` () VALUES ();");
          $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         } catch (Exception $e) {
          echo 'Подключение не удалось: ' . $e->getMessage();
         }

         $today_unixtime = strtotime((string)date('d.m.Y'));
         $id = $dbh->lastInsertId();

         try {
           $res = $dbh->prepare("INSERT INTO `iwater_storage_agr`(`id`, `company_id`, `name`, `priority`, `address`, `coords`, `storeman_name`, `storeman_phone`, `date_start`, `date_finish`) VALUES ('$id', '$company', ?, ?, ?, '', ?, ?, '$today_unixtime', 2147483647)");
           $res->execute(array($name, $priority, $address, $storeman, $contact));
           $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         } catch (Exception $e) {
           echo 'Подключение не удалось: ' . $e->getMessage();
         }
         setActionLog("storage", "Добавлен склад", "iwater_storage_agr", "Добавлен склад: №" . $id);
         return;
     }
     if (isset($_POST['add_user'])) { // Добавить пользователя
         $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
         $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
         $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
         $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
         $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);

         $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
         $max = 29;
         $size = ($chars) - 1;
         $salt = '$5$rounds=5000$';
         while ($max--) {
             $salt .= $chars[rand(0, $size)];
         }

         $hash = crypt($password, $salt);

         try {
             $res = $dbh->query("SELECT count(`login`) FROM `iwater_users` WHERE `login`='" . $login . "'");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
         }
         while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
             $ok = $r['count(`login`)'];
         }

         $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
         $usersess = $res->fetch();
         $company = $usersess['company_id'];

         $zero = 0;

         if ($ok == 0) {
             try {
                 $sth = $dbh->prepare("INSERT INTO `iwater_users` (`login`, `password`, `salt`, `name`, `phone`, `role`, `company_id`, `ban`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                 $sth->execute(array($login, $hash, $salt, $name, $phone, $role, $company, $zero));
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
             }
         }
         setActionLog("user", "Добавление", "iwater_user", "Добавлен пользователь: " . $name);
         header('Location: /iwaterTest/');


         if (isset($_POST['app_login'])) {
             $app_login = filter_input(INPUT_POST, 'app_login', FILTER_SANITIZE_SPECIAL_CHARS);
             $app_pass = filter_input(INPUT_POST, 'app_pass', FILTER_SANITIZE_SPECIAL_CHARS);

             $app_pass = hash('sha512', $salt . $app_pass);
             $session = '';
             $sess_leng = 40;

             while ($sess_leng--) {
                 $session .= $chars[rand(0, $size)];
             }

             try {
                 $app = $dbh->prepare("INSERT INTO `iwater_driver`(`id`, `login`, `password`, `salt`, `session`, `company`) VALUES (?, ?, ?, ?, ?, ?);");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                 $app->execute(array($dbh->lastInsertId(), $app_login, $app_pass, $salt, $session, $company));
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }
         }
     }

/**
   *****
   ********
   *************
   *******************
   * КОМАНДЫ НА УДАЛЕНИЕ ДАННЫХ
   *******************
   ****************
   ************
   **********
   ********
   ******
   ****
   ***
   **
   *
*/

        if (isset($_POST['delete_list'])) { // Удаление путевого листа
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $name = (trim(filter_input(INPUT_POST, 'delete_list', FILTER_SANITIZE_SPECIAL_CHARS)));
            unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $name);
            try {
                $res = $dbh->prepare('DELETE FROM `iwater_lists` WHERE `id` = "' . $name . '"; UPDATE `iwater_orders` SET `list` = NULL WHERE `list` = ' . $name);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            setActionLog("list", "Удаление", "iwater_lists", "Удалена путевого листа №" . $name);
        }
        if (isset($_POST['delete_role'])) { // Удаление роли пользователя
           $role = trim(filter_input(INPUT_POST, 'delete_role', FILTER_SANITIZE_SPECIAL_CHARS));
           try {
              $res = $dbh->query("DELETE FROM `iwater_roles` WHERE `id` = " . $role);
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }
          setActionLog("role", "Удаление", "iwater_roles", "Удалена роль №" . $role);
          echo $role;
        }
        if (isset($_POST['delete_client_address'])) {
           $address = trim(filter_input(INPUT_POST, 'delete_client_address', FILTER_SANITIZE_SPECIAL_CHARS));
           $client = trim(filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_SPECIAL_CHARS));

           try {
              $res = $dbh->prepare('DELETE FROM `iwater_addresses` WHERE `client_id` = ? AND `address` = ?');
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $res->execute(array($client, $address));
        }
        if (isset($_POST['delete_unit'])) { // Удаление товара и списка номенклатур
           $unit_id = trim(filter_input(INPUT_POST, 'delete_unit', FILTER_SANITIZE_SPECIAL_CHARS));
           $yesterday_unixtime = strtotime(date('Y-m-d')) - 86400;

           // Сверяем с номером компании, чтобы избежать "хулиганства"
           try {
               $res = $dbh->query("SELECT n.id FROM `iwater_users` AS u LEFT JOIN `iwater_category` AS c ON (c.company_id = u.company_id) LEFT JOIN `iwater_units_agr` AS n ON (n.category = c.category_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "' AND n.id = '$unit_id' LIMIT 1");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $data = $res->fetch(PDO::FETCH_ASSOC);
           $unit_id = $data['id'];

           try {
             $dbh->query("UPDATE `iwater_units_agr` SET `date_finish` = " . $yesterday_unixtime . " WHERE `id` = " . $unit_id . " AND `date_finish` > " . $yesterday_unixtime . "; DELETE FROM `iwater_units` WHERE `id` = " . $unit_id);
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           setActionLog("units", "Удаление", "iwater_units", "Удалён товара: №" . $unit_id);
           header('Location: /iwaterTest/admin/list_unit/');
        }
        if (isset($_POST['delete_storage'])) { // Удаление склада
           $storage_id = trim(filter_input(INPUT_POST, 'delete_storage', FILTER_SANITIZE_SPECIAL_CHARS));
           $yesterday_unixtime = strtotime(date('Y-m-d')) - 86400;

           // Сверяем с номером компании, чтобы избежать "хулиганства"
           try {
               $res = $dbh->query("SELECT s.id FROM `iwater_users` AS u LEFT JOIN `iwater_storage_agr` AS s ON (u.company_id = s.company_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "' AND s.id = '$storage_id' LIMIT 1");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $data = $res->fetch(PDO::FETCH_ASSOC);
           $storage_id = $data['id'];

           // Закрытие даты для склада и удаление из таблицы актуальных складов
           try {
             $res = $dbh->prepare("UPDATE `iwater_storage_agr` SET `date_finish` = ? WHERE `id` = ? AND `date_finish` > ?; DELETE FROM `iwater_storage` WHERE `id` = ?");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             $res->execute(array($yesterday_unixtime, $storage_id, $yesterday_unixtime, $storage_id));
          } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          setActionLog("storage", "Удаление", "iwater_storage", "Удалён склад: №" . $storage_id);
          header('Location: /iwaterTest/admin/storage_control/');
        }
        if (isset($_POST['delete_provider'])) { // Удаление поставщика
           $provider_id = trim(filter_input(INPUT_POST, 'delete_provider', FILTER_SANITIZE_SPECIAL_CHARS));

           // Сверяем с номером компании, чтобы избежать "хулиганства"
           try {
               $res = $dbh->query("SELECT p.id FROM `iwater_users` AS u LEFT JOIN `iwater_providers` AS p ON (u.company_id = p.company_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "' AND p.id = '$provider_id' LIMIT 1");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $data = $res->fetch(PDO::FETCH_ASSOC);
           $provider_id = $data['id'];

           try {
             $res = $dbh->prepare("DELETE FROM `iwater_providers` WHERE `id` = ?");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             $res->execute(array($provider_id));
          } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          setActionLog("provider", "Удаление", "iwater_provider", "Удалён поставщик: №" . $provider_id);
          header('Location: /iwaterTest/admin/list_provider/');
          echo '1';
        }
        if (isset($_POST['delete_user'])) { // Удаление пользователя
            $id = trim(filter_input(INPUT_POST, 'delete_user', FILTER_SANITIZE_SPECIAL_CHARS));

            // Сверяем с номером компании, чтобы избежать "хулиганства"
            try {
                $res = $dbh->query("SELECT * FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (u.company_id = c.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $data = $res->fetch(PDO::FETCH_ASSOC);
            $company = $data['company_id'];

            try {
               $role = $dbh->query("SELECT `role` FROM `iwater_user` WHERE `id` = " . $id);
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            // Удаление аккаунта водителя "iWater Logistic"
            $role_id = $role->fetch();
            if ($role_id['role'] == 3) {
               try {
                  $delete_driver_acc = $dbh->query("DELETE FROM `iwater_driver` WHERE `id` = '$id'");
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
               }

            }

            try {
                $res=$dbh->prepare("DELETE FROM `iwater_users` WHERE `id` = ? AND `company_id` = ?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($id, $company));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            setActionLog("user", "Удаление пользователя", "iwater_users", "Клиент c id: ".$id." удалён ");

            header('Location: /iwaterTest/admin/list_users/');
        }
        if (isset($_POST['destroy_client'])) { // Полное удаление клиента
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

/**
  ***
   *****
    *****
    ********
    *************
    *******************
    * КОМАНДЫ С ВЫГРУЗКОЙ XML ДАННЫХ
    *******************
    ****************
    ************
    **********
    ********
    ******
    ****
    ***
    **
    *
*/

         if (isset($_GET['storage_info'])) {
          $page = $_POST['page'];
          $limit = $_POST['rows'];
          $sidx = $_POST['sidx'];
          $sord = $_POST['sord'];

          $today_unixtime = strtotime((string)date('d.m.Y'));
          try {
               $res = $dbh->query("SELECT DISTINCT s.id, s.name, s.priority, s.address, s.coords, s.storeman_name, s.storeman_phone FROM `iwater_storage_agr` AS s LEFT JOIN `iwater_users` AS u ON (s.company_id = u.company_id) JOIN `iwater_storage` AS agr ON (s.id = agr.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "' AND date_finish > " . $today_unixtime);
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
          }

          $count = $res->rowCount();
          $total_pages = 1;

          $out = "<?xml version='1.0' encoding='utf8'?>";
          $out .= '<rows>';
          $out .= '<page>' . $page . '</page>';
          $out .= '<total>' . $total_pages . '</total>';
          $out .= '<records>' . $count . '</records>';
          while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
               $out .= '<row>';
               $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
               $out .= '<cell><![CDATA[' . $r['name'] . ']]></cell>';
               $out .= '<cell><![CDATA[' . $r['priority'] . ']]></cell>';
               $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
               $out .= '<cell><![CDATA[' . $r['storeman_name'] . ']]></cell>';
               $out .= '<cell><![CDATA[' . $r['storeman_phone'] . ']]></cell>';
               $out .= '<cell><![CDATA[<a href="/iwaterTest/admin/edit_storage?id=' . $r['id'] . '"><img src="/iwaterTest/css/image/edit.png"></a>]]></cell>';
               $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
               $out .= '</row>';
          }
          $out .= '</rows>';
          header("Content-type: text/xml;charset=utf8");
          echo $out;
         }

         if (isset($_GET['driver_control'])) {
             //Отслеживание деятельности водителей
             $page = $_POST['page'];
             $limit = $_POST['rows'];
             $sidx = $_POST['sidx'];
             $sord = $_POST['sord'];

             $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
             $usersess = $res->fetch();
             $company = $usersess['company_id'];

             try {
                 $start = ($page - 1) * $limit;
                 $finish = $page * $limit;
                 $res = $dbh->query("SELECT * FROM `iwater_driver` WHERE `company_id` = '$company' ORDER BY $sidx $sord LIMIT $start , $finish");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $count = $res->fetchColumn();
             $total_pages = ceil($count / $limit);

             $out = "<?xml version='1.0' encoding='utf8'?>";
             $out .= '<rows>';
             $out .= '<page>' . $page . '</page>';
             $out .= '<total>' . $total_pages . '</total>';
             $out .= '<records>' . $count . '</records>';
             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                 $dir = (file_exists('../iwater_api/nusoap/images/product/hdpi/' . $r['id'] . '.jpg') ? "+" : "-");
                 $out .= "<row id='" . $r['id'] . "'>";
                 $out .= '<cell></cell>';
                 $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['order_id'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['driver_id'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['date'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['overdue'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['order_cord'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['driver_cord'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['violation'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['notice'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['tanks'] . ']]></cell>';
                 $out .= '</row>';
             }
             $out .= '</rows>';
             header("Content-type: text/xml;charset=utf8");
             echo $out;
         }

         if (isset($_GET['list_unit'])) {
             $page = $_POST['page'];
             $limit = $_POST['rows'];
             $sidx = $_POST['sidx'];
             $sord = $_POST['sord'];

             $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
             $usersess = $res->fetch();
             $company = $usersess['company_id'];

             $today_unixtime = strtotime((string)date('d.m.Y'));
             $yesterday_unixtime = $today_unixtime - 86400;

             //Подсчёт количества записей
             try {
                 $cou = $dbh->query("SELECT * FROM `iwater_units_agr` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) JOIN `iwater_units` AS u_a ON (u_a.id = u.id) WHERE c.company_id = $company");
                 $count = $cou->rowCount();
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             //Вызов списка товаров согласно работе пагинатора
             try {
                 $start = ($page - 1) * $limit;
                 $finish = $page * $limit;
                 $res = $dbh->query("SELECT u.id, u_a.gl_id, u_a.name, u_a.shname, u_a.about, u_a.price, u_a.discount, u_a.gallery, u_a.category, c.category AS cat_name, category_id FROM `iwater_units_agr` AS u_a JOIN `iwater_units` AS u ON (u_a.id = u.id) LEFT JOIN `iwater_category` AS c ON (u_a.category = c.category_id) WHERE c.company_id = '$company' AND `date_finish` > '$yesterday_unixtime' ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $total_pages = ceil($count/$limit);

             $out = "<?xml version='1.0' encoding='utf8'?>";
             $out .= '<rows>';
             $out .= '<page>' . $page . '</page>';
             $out .= '<total>' . $total_pages . '</total>';
             $out .= '<records>' . $count . '</records>';
             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $dir = (file_exists('../wsdl/images/product/hdpi/' . $r['id'] . '.jpg') ? "+" : "-");
                    $about = (strlen($r['about']) < 55 ? $r['about'] : substr($r['about'], 0, 155) . '...');
                    $links = (strlen($r['gallery']) < 30 ? $r['gallery'] : substr($r['gallery'], 0, 30) . '...');

                    $adder = '';
                    $prices = explode(';', $r['price']);
                    if (count($prices) > 1) { $adder = ',..'; }
                    $prices = explode(':', $prices[0]);

                    $price = (strlen($r['price']) < 15 ? $r['price'] : substr($r['price'], 0, 12) . '...');
                    $out .= "<row id='" . $r['id'] . "'>";
                    $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['gl_id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['name'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $about . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $prices[1] . $adder . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['discount'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $links . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['category'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $dir . ']]></cell>';
                    $out .= '</row>';
             }
             $out .= '</rows>';
             header("Content-type: text/xml;charset=utf8");
             echo $out;
         }

         if (isset($_GET['transaction_info_upd'])) {
             $page = $_POST['page'];
             $limit = $_POST['rows'];
             $sidx = $_POST['sidx'];
             $sord = $_POST['sord'];
             //$today_time = time();
             $half_sec_in_day = 86400 / 2;
             //$week_unix =  $today_time - 604800;

             if($_GET['unit'] != ""){
                $extraSQL .= " AND h.unit_id = " . $_GET['unit'];
             }
             if($_GET['storage'] != ""){
                $extraSQL .= " AND h.storage = " . $_GET['storage'];
             }
             if ($_GET['from'] != "" || $_GET['to'] != "") {
                $extraSQL .= ' AND ';
             }//else{
               //$extraSQL .= ' AND h.date > ' . strval($week_unix);
             //}
             if ($_GET['from'] != "") {
                $from = trim(filter_input(INPUT_GET, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
                $from = explode('/', $from);
                $from = mktime(0, 0, 0, $from[1], $from[0], $from[2]);
                $extraSQL .= ' h.date > ' . strval($from - $half_sec_in_day);
             }
             if ($_GET['from'] != "" && $_GET['to'] != "") {
                $extraSQL .= ' AND ';
             }
             if ($_GET['to'] != "") {
                $to = trim(filter_input(INPUT_GET, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
                $to = explode('/', $to);
                $to = mktime(0, 0, 0, $to[1], $to[0], $to[2]);
                $extraSQL .= ' h.date < ' . strval($to + $half_sec_in_day);
             }

             $result = $dbh->query("SELECT * FROM `iwater_storage_history`");
             $count = $result->rowCount();

             $total_pages = ceil($count/$limit);
             $today_unixtime = strtotime((string)date('d.m.Y'));

             try {
                $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT u.name AS u_name, s.name AS s_name, h.operation, h.count, h.date, h.comment FROM `iwater_storage_history` AS h LEFT JOIN `iwater_storage_agr` AS s ON (s.id = h.storage) LEFT JOIN `iwater_units_agr` AS u ON (u.id = h.unit_id) WHERE s.date_finish > h.date " . $extraSQL . " AND s.date_start < h.date AND u.date_finish > h.date AND u.date_start < h.date ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $i = 0;
             $out = "<?xml version='1.0' encoding='utf8'?>";
             $out .= '<rows>';
             $out .= '<page>' . $page . '</page>';
             $out .= '<total>' . $total_pages . '</total>';
             $out .= '<records>' . $count . '</records>';
             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $out .= "<row id='" . $i . "'>";
                $out .= '<cell><![CDATA[' . $r['u_name'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['s_name'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['operation'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['count'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['comment'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . date('d M G:i', $r['date']) . ']]></cell>';
                $out .= '</row>';
                $i++;
             }
             $out .= '</rows>';
             header("Content-type: text/xml;charset=utf8");
             echo $out;
          }
          if (isset($_GET['company'])) {
             $page = $_POST['page'];
             $limit = $_POST['rows'];
             $sidx = $_POST['sidx'];
             $sord = $_POST['sord'];

             $extraSQL = "";

             if ($_POST['_search'] == 'true') {
                 if (isset($_POST['filters'])) {
                     $json = stripslashes($_POST['filters']);

                     $filters = json_decode($json);

                     $where = generateSearchStringFromObj($filters);
                     $extraSQL .= " WHERE " . $where . " ";
                 }
             }


             $result = $dbh->query("SELECT * FROM `iwater_company` " . $extraSQL);
             $count = $result->rowCount();

             $total_pages = ceil($count/$limit);

             try {
                 $start = ($page - 1) * $limit;
                 $finish = $page * $limit;
                 $res = $dbh->query("SELECT * FROM `iwater_company` ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $i = 0;
             $out = "<?xml version='1.0' encoding='utf8'?>";
             $out .= '<rows>';
             $out .= '<page>' . $page . '</page>';
             $out .= '<total>' . $total_pages . '</total>';
             $out .= '<records>' . $count . '</records>';
             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                 $out .= "<row id='" . $i . "'>";
                 $out .= '<cell><![CDATA[<a href="/iwaterTest/admin/edit_company?id=' . $r['id'] . '"><img src="/iwaterTest/css/image/edit.png"></a>]]></cell>';
                 $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['name'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['city'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['contact'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['schedule'] . ']]></cell>';
                 $out .= '<cell><![CDATA[' . $r['regions'] . ']]></cell>';
                 $out .= '</row>';
                 $i++;
             }
             $out .= '</rows>';
             header("Content-type: text/xml;charset=utf8");
             echo $out;
          }
          if (isset($_GET['order'])) {
              $page = $_POST['page'];
              $limit = $_POST['rows'];
              $sidx = $_POST['sidx'];
              $sord = $_POST['sord'];


              $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
              $usersess = $res->fetch();
              $company = $usersess['company_id'];

              //Список заказов
              $extraSQL = "WHERE o.company_id = '$company'";
              if (isset($_GET['client_order'])) {
                  $client_id = trim(filter_input(INPUT_GET, 'client_order', FILTER_SANITIZE_SPECIAL_CHARS));
                  $extraSQL .= " AND o.client_id = '$client_id' ";
              }
              if (isset($_GET['name'])) {
                  $name = trim(filter_input(INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                  $extraSQL .= " AND o.name = '$name' ";
              }
               if (isset($_GET['driver'])) {
                  $driver = trim(filter_input(INPUT_GET, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
                  $extraSQL .= " AND o.driver = '$driver' ";
              }
               if (isset($_GET['status'])) {
                  $status = trim(filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
                  $extraSQL .= " AND o.status = '$status' ";
              }
               if (isset($_GET['equip'])) {
                  $equip = trim(filter_input(INPUT_GET, 'equip', FILTER_SANITIZE_SPECIAL_CHARS));
                  $extraSQL .= " AND o.equip = '$equip' ";
              }
               if (isset($_GET['mobile'])) {
                  $mobile = trim(filter_input(INPUT_GET, 'mobile', FILTER_SANITIZE_SPECIAL_CHARS));
                 // $extraSQL .= " AND o.mobile = '$mobile' ";
				  $extraSQL .= ' AND ADT.contact_search like "%'.$mobile.'%" ';// обращение к столбцу в базе с мобильным номером
              }
              if (isset($_GET['no_date_order'])) {
                   $extraSQL = 'AND o.date = "" ';
              }

              if (isset($_GET['list_order_upd'])) {
                  $half_sec_in_day = 86400 / 2;
                  if ($_GET['from'] != "" || $_GET['to'] != "") {
                      $extraSQL = 'AND ';
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


              if ($_POST['_search'] == 'true') {
                  $sidx = "o.date";
                  $sord = "desc";
                  if (isset($_POST['filters'])) {
                      $json = stripslashes($_POST['filters']);

                      $filters = json_decode($json);

                      $where = generateSearchStringFromObj($filters);
                      $extraSQL = " WHERE " . $where . " ";
                  }
              }
			   //$result = $dbh->query("SELECT *,u.name AS d_name, u.session AS u_sess, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id)" . $extraSQL);
			 //$str_sqli ="SELECT *,u.name AS d_name, u.session AS u_sess, o.id AS o_id, o.name AS o_name, ADT.contact_search FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) inner join (select  client_id,Replace(replace(contact,'-',''),'+','') AS contact_search from `iwater_orders`) as ADT on o.client_id = ADT.client_id " . $extraSQL .";"; 
			 $str_sqli ="SELECT *,u.name AS d_name, u.session AS u_sess, o.id AS o_id, o.name AS o_name, ADT.contact_search FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) inner join (select id, client_id,Replace(replace(contact,'-',''),'+','') AS contact_search from `iwater_orders`) as ADT on o.id = ADT.id " . $extraSQL .";"; 
			 $result = $dbh->prepare($str_sqli);
			  
              $count = $result->rowCount();
              $dbh->query(null);

              $total_pages = ceil($count/$limit);
			  

              try {
                  $start = ($page - 1) * $limit;
                  $finish = $page * $limit;
				  //$res = $dbh->query("SELECT *, o.address, u.name AS d_name, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
				 // $res = $dbh->query("select *, o.address,u.name as d_name, o.id as o_id ,o.name as o_name , ADT.contact_search FROM `iwater_orders` as o left join `iwater_users` as u on (o.driver = u.id) inner join (select client_id,Replace(replace(contact,'-',''),'+','') AS contact_search from `iwater_orders`) as ADT on o.client_id = ADT.client_id " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
				 $res = $dbh->query("select *, o.address,u.name as d_name, o.id as o_id ,o.name as o_name , ADT.contact_search FROM `iwater_orders` as o left join `iwater_users` as u on (o.driver = u.id) inner join (select id,client_id,Replace(replace(contact,'-',''),'+','') AS contact_search from `iwater_orders`) as ADT on o.id = ADT.id " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
				  //echo $extraSQL;
				  //die();
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
              }

              $out = "<?xml version='1.0' encoding='utf8'?>";
              $out .= '<rows>';
              $out .= '<page>' . $page . '</page>';
              $out .= '<total>' . $total_pages . '</total>';
              $out .= '<records>' . $count . '</records>';
              while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                  $newdate = "";
                  if ($r['date'] != "") {
                      $newdate = date("d/m/Y", $r['date']);
                  }

                  $equip = '';
                  $id = unserialize($r['water_equip']);
                  if (is_array($id)) {
                      foreach (array_keys(unserialize($r['water_equip'])) as $index => $value) {
                          if (array_key_exists($index + 1, $id)) {
                              $equip .= $value . ' - ' . $id[$index + 1];
                          }
                          if ($index + 1 < count($id)) {
                              $equip .= ', ';
                          }
                      }
                  } else {
                      $equip = 'null';
                  }

                  $waterFilt1 = array('{"', '}', '":', ',"');
                  $waterFilt2 = array('id ', '', ' - ', ',id ');
                  $string_equip = '';

                  $client = '';
                  if ($r['mobile'] == 0) {
                      $client = '-';
                  } else {
                      $client = $r['mobile'];
                  }

                  $out .= "<row id='" . $r['o_id'] . "'>";
                  $out .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['client_id'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['o_name'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
				  $out .= '<cell><![CDATA[' . $r['contact'] . ']]></cell>';// информация о контактных данных (выборка заказчиков list_orders)
                  $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['time'] . ']]></cell>';

                  //Формаирования листа с отделением воды от остальных товаров

                  $water_order = unserialize($r['water_equip']);

                  try {
                      $unit_w = $dbh->query("SELECT * FROM `iwater_units_agr` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) LEFT JOIN `iwater_units` AS a ON (a.id = u.id) WHERE company_id = '$company'");
                      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                  } catch (Exception $e) {
                      echo 'Подключение не удалось: ' . $e->getMessage();
                  }

                  while ($w = $unit_w->fetch(PDO::FETCH_ASSOC)) {
                      if (strlen($w['shname']) > 0) {
                          if (array_key_exists($w['id'], $water_order)) {
                              $out .= '<cell><![CDATA[' . $water_order[$w['id']] . ']]></cell>';
                          } else {
                              $out .= '<cell><![CDATA[0]]></cell>';
                          }
                      } else {
                          if (array_key_exists($w['id'], $water_order)) {
                              $string_equip .= $water_order[$w['id']] . ' - ' . $w['name'] . '
';
                          }
                      }
                  }

                  $out .= '<cell><![CDATA[' . $r['tank_empty_now'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $string_equip . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['history'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['notice'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['reason'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['d_name'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                  $out .= '</row>';
              }
              $out .= '</rows>';
              header("Content-type: text/xml;charset=utf8");
              echo $out;
          }

          if (isset($_GET['order_app'])) {
              $page = $_POST['page'];
              $limit = $_POST['rows'];
              $sidx = $_POST['sidx'];
              $sord = $_POST['sord'];

              $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
              $usersess = $res->fetch();
              $company = $usersess['company_id'];

              //Список заказов с приложений под управлением Android и iOS
              $extraSQL = " WHERE 1 "; // "WHERE company_id = " . $company;
              // Тут затычка. чтобы мы наконец опеределились с делением на компании
              #FIXME
              if ($_GET['client_order']) {
                  $client_id = trim(filter_input(INPUT_GET, 'client_order', FILTER_SANITIZE_SPECIAL_CHARS));
                  $extraSQL .= " AND client_id = '$client_id'";
              }
              if ($_GET['no_date_order']) {
                  $extraSQL .= ' AND date = "" ';
              }

              if (isset($_GET['list_order_upd'])) {
                  $half_sec_in_day = 86400 / 2;
                  if ($_GET['from'] != "" || $_GET['to'] != "") {
                      $extraSQL .= ' AND ';
                  }
                  if ($_GET['from'] != "") {
                      $from = trim(filter_input(INPUT_GET, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
                      $from = explode('/', $from);
                      $from = mktime(0, 0, 0, $from[1], $from[0], $from[2]);
                      $extraSQL .= ' date > ' . strval($from - $half_sec_in_day);
                  }
                  if ($_GET['from'] != "" && $_GET['to'] != "") {
                      $extraSQL .= ' AND ';
                  }
                  if ($_GET['to'] != "") {
                      $to = trim(filter_input(INPUT_GET, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
                      $to = explode('/', $to);
                      $to = mktime(0, 0, 0, $to[1], $to[0], $to[2]);
                      $extraSQL .= ' date < ' . strval($to + $half_sec_in_day);
                  }
              }


              if ($_POST['_search'] == 'true') {
                  if (isset($_POST['filters'])) {
                      $json = stripslashes($_POST['filters']);

                      $filters = json_decode($json);

                      $where = generateSearchStringFromObj($filters);
                      $extraSQL = " WHERE " . $where . " ";
                  }
              }


              $result = $dbh->query("SELECT * FROM `iwater_orders_app` " . $extraSQL);
              $count = $result->rowCount();
              $dbh->query(null);

              $total_pages = ceil($count/$limit);


              try {
                  $start = ($page - 1) * $limit;
                  $finish = $page * $limit;
                  $res = $dbh->query("SELECT * FROM `iwater_orders_app` " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
              }

              $units_list = array();
              $today_unixtime = strtotime((string)date('d.m.Y'));

              try {
                 $units = $dbh->query("SELECT `id`, `name` FROM `iwater_units_agr` ORDER BY `date_finish`");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
              }

              while ($un = $units->fetch(PDO::FETCH_ASSOC)) {
                 $units_list[$un['id']] = $un['name'];
              }

              $out = "<?xml version='1.0' encoding='utf8'?>";
              $out .= '<rows>';
              $out .= '<page>' . $page . '</page>';
              $out .= '<total>' . $total_pages . '</total>';
              $out .= '<records>' . $count . '</records>';
              while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                  $newdate = "";
                  if ($r['date'] != "") {
                      $newdate = date("d/m/Y H:i", $r['date']);
                  }

                  $water_equip = '';

                  $water_list = str_replace(array('[', ']', '{"id":"', '"count":"', '"', '}'), '', $r['water_equip']);
                  $water_list = explode(',', $water_list);

                  foreach ($water_list as $key => $value) {
                     if ($key % 2 === 0) {
                        $water_equip .= $water_list[$key + 1] . " - " . $units_list[$value] . "
";
                     }
                  }

                  $out .= "<row id='" . $r['id'] . "'>";
                  $out .= '<cell></cell>';
                  $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['client_id'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['period'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['notice'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $water_equip . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['system'] . ']]></cell>';
                  $out .= '<cell><![CDATA[' . $r['checked'] . ']]></cell>';
                  $out .= '</row>';
              }
              $out .= '</rows>';
              header("Content-type: text/xml;charset=utf8");
              echo $out;
          }

          if (isset($_GET['logs'])) {
              $page = $_POST['page'];
              $rows = 7;

              $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
              $usersess = $res->fetch();
              $company = $usersess['company_id'];

              $count = 24502;

              try {
                  $start = ($page - 1) * $rows;
                  $res = $dbh->query("SELECT *,u.name AS admin FROM `iwater_logs` AS l JOIN `iwater_users` AS u ON(l.user_id=u.id) WHERE u.company_id = " . $company . " ORDER BY l.time DESC" . " LIMIT " . $start . ", " . $rows);
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
              }
              $page_count = ceil($count / $rows);

              $out = "<?xml version='1.0' encoding='utf8'?>";
              $out .= '<rows>';
              $out .= '<page>' . $page . '</page>';
              $out .= '<total>' . $page_count . '</total>';
              $out .= '<records>' . $count . '</records>';
              while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                  $newdate = "";

                  if ($r['time'] != "") {
                      $newdate = date("d/m/Y H:i:s", $r['time']);
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
              header("Content-type: text/xml;charset=utf8");
              echo $out;
          }

          if (isset($_GET['list_units'])) {
             //Список клиентов

             $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
             $usersess = $res->fetch();
             $company = $usersess['company_id'];

             $sidx = $_POST['sidx'];
             $sord = $_POST['sord'];

             try {
                 $res = $dbh->query("SELECT u.id, u.name FROM `iwater_category` AS c LEFT JOIN `iwater_units` AS u ON (c.category_id = u.category) WHERE c.company_id = '$company' ORDER BY u.id");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }
             $s = "<?xml version='1.0' encoding='utf8'?>";
             $s .= "<rows>";

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                if ($r['id'] != "") {
                    $s .= "<row>";

                    $s .= "<cell id='id'><![CDATA[" . $r['id'] . "]]></cell>";
                    $s .= "<cell id='name'><![CDATA[" . $r['name'] . "]]></cell>";
                    $s .= "</row>";
                 }
             }
             $s .= "</rows>";
             header("Content-type: text/xml;charset=utf8");
             echo $s;
         }

         if (isset($_GET['list_rest_order'])) {
            //Список клиентов

            $page = $_POST['page'];
            $limit = $_POST['rows'];
            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $extraSQL = " AND c.company_id = '" . $company . "' ";

            if ($_POST['_search'] == 'true') {
                if (isset($_POST['filters'])) {
                    $json = stripslashes($_POST['filters']);

                    $filters = json_decode($json);

                    $where = generateSearchStringFromObj($filters);
                    $extraSQL .= " AND " . $where . " ";
                }
            }


            $result = $dbh->query("SELECT * FROM `iwater_clients` WHERE `company_id` = '$company' AND `tanks` > 0");
            $count = $result->rowCount();

            $total_pages = ceil($count/$limit);

            try {
                $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT c.client_id, c.name, c.tanks, MAX(o.date) AS `last_order` FROM `iwater_clients` AS c LEFT JOIN `iwater_orders` AS o ON (c.client_id = o.client_id) WHERE `tanks` > 0 " . $extraSQL ." GROUP BY c.client_id ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            $s .= '<records>' . $count . '</records>';

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
              if ($r['client_id'] != "") {
                   $s .= "<row>";
                   $s .= "<cell id='id'><![CDATA[" . $r['client_id'] . "]]></cell>";
                   $s .= "<cell id='name'><![CDATA[" . $r['name'] . "]]></cell>";
                   $s .= "<cell id='tanks'><![CDATA[" . $r['tanks'] . "]]></cell>";
                   $s .= "<cell id='last_order'><![CDATA[" . date('j F Y', $r['last_order']) . "]]></cell>";
                   $s .= "</row>";
                }
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
         }
         if (isset($_GET['list_success_analytics'])) {
            //Список клиентов
            $page = $_POST['page'];
            $limit = $_POST['rows'];
            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $extraSQL = "WHERE d.company = " . $company ;

            if ($_POST['_search'] == 'true') {
                if (isset($_POST['filters'])) {
                    $json = stripslashes($_POST['filters']);

                    $filters = json_decode($json);

                    $where = generateSearchStringFromObj($filters);
                    $extraSQL = " AND " . $where . " ";
                }
            }
            $result = $dbh->query("SELECT * FROM `iwater_driver` AS d " . $extraSQL);
            $count = $result->rowCount();
            $dbh->query(null);

            $total_pages = ceil($count/$limit);


            try {
                $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT DISTINCT d.id, u.name, d.stat FROM `iwater_driver` AS d LEFT JOIN `iwater_users` AS u ON (d.id = u.id) " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            $s .= '<records>' . $count . '</records>';

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
              if ($r['id'] != "") {
                   $s .= "<row>";
                   $s .= "<cell id='id'><![CDATA[" . $r['id'] . "]]></cell>";
                   $s .= "<cell id='name'><![CDATA[" . $r['name'] . "]]></cell>";
                   $s .= "<cell id='name'><![CDATA[" . $r['stat'] . "]]></cell>";
                   $s .= "</row>";
                }
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
         }
         if (isset($_GET['list_last_order'])) {
             //Список клиентов

            $page = $_POST['page'];
            $limit = $_POST['rows'];
            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $extraSQL = "AND c.company_id = " . $company ;

            $current_time = time();

            $now = date('y/m/d', time());
            $date_now = explode('/', $now);

            if ($_POST['_search'] == 'true') {
                if (isset($_POST['filters'])) {
                    $json = stripslashes($_POST['filters']);

                    $filters = json_decode($json);

                    $where = generateSearchStringFromObj($filters);
                    $extraSQL = " AND " . $where . " ";
                }
            }

            try {
               $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT c.client_id, c.name, MAX(o.date) AS last_order , c.avg_difference FROM `iwater_clients` AS c LEFT JOIN `iwater_orders` AS o ON (c.client_id = o.client_id) WHERE c.avg_difference IS NOT NULL " . $extraSQL . " GROUP BY c.client_id ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $count = 0;
            $s_add_after_calc = "";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
              if ((($r['avg_difference'] * 3) > ($current_time  - $r['last_order'])) && (($r['last_order'] +  $r['avg_difference']) < $current_time )) {
                    $count++;
                   $last = date('y/m/d', $r['last_order']);
                 $date_last = explode('/', $last);

                 $date_time_str = ($date_now[2] - $date_last[2] < 0) ? ($date_now[1] - $date_last[1] - 1) . "м. " . ($date_now[2] - $date_last[2] + 30) . "д." : ($date_now[1] - $date_last[1]) . "м. " . ($date_now[2] - $date_last[2]) . "д.";

                   $s_add_after_calc .= "<row>";
                   $s_add_after_calc .= "<cell id='id'><![CDATA[" . $r['client_id'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='name'><![CDATA[" . $r['name'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='last_order'><![CDATA[" .  $date_time_str . "]]></cell>";
                   $s_add_after_calc .= "<cell id='next_order'><![CDATA[" . date('j F Y',( $r['last_order'] +  $r['avg_difference'])) . "]]></cell>";
                   $s_add_after_calc .= "</row>";
               }
            }

            $total_pages = ceil($count/$limit);

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            $s .= '<records>' . $count . '</records>';
            $s .= $s_add_after_calc;
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
         }
         if (isset($_GET['list_lost_client'])) {
             //Список клиентов

            $page = $_POST['page'];
            $limit = $_POST['rows'];
            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];
            $current_time = time();

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $extraSQL = " AND c.company_id = '" . $company . "'" ;


            if ($_POST['_search'] == 'true') {
                if (isset($_POST['filters'])) {
                    $json = stripslashes($_POST['filters']);

                    $filters = json_decode($json);

                    $where = generateSearchStringFromObj($filters);
                    $extraSQL .= " AND " . $where . " ";
                }
            }

             $now = date('y/m/d', time());
             $date_now = explode('/', $now);

            try {
               $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT c.client_id, c.name, MAX(o.date) AS last_order , c.avg_difference FROM `iwater_clients` AS c LEFT JOIN `iwater_orders` AS o ON (c.client_id = o.client_id) WHERE c.avg_difference IS NOT NULL " . $extraSQL . " GROUP BY c.client_id ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $count = 0;
            $s_add_after_calc = "";


            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
             if (($r['avg_difference'] * 3) < ($current_time  - $r['last_order'])) {
                 $count++;
                 $last = date('y/m/d', $r['last_order']);
                 $date_last = explode('/', $last);

                 $date_time_str = ($date_now[2] - $date_last[2] < 0) ? ($date_now[1] - $date_last[1] - 1) . "м. " . ($date_now[2] - $date_last[2] + 30) . "д." : ($date_now[1] - $date_last[1]) . "м. " . ($date_now[2] - $date_last[2]) . "д.";

                   $s_add_after_calc .= "<row>";
                   $s_add_after_calc .= "<cell id='id'><![CDATA[" . $r['client_id'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='name'><![CDATA[" . $r['name'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='last_order'><![CDATA[" . $date_time_str . "]]></cell>";
                   $s_add_after_calc .= "<cell id='next_order'><![CDATA[" . date('j F Y',( $r['last_order'] +  $r['avg_difference'])) . "]]></cell>";
                   $s_add_after_calc .= "</row>";
             }
            }

            $total_pages = ceil($count/$limit);

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            $s .= '<records>' . $count . '</records>';
            $s .= $s_add_after_calc;
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
         }
         if (isset($_GET['list_delay_order'])) {
            //Список клиентов
            try {
                $res = $dbh->query("SELECT * FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (u.company_id = c.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $periods = array();
            $current_date = date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));

            $data = $res->fetch(PDO::FETCH_ASSOC);

            $period = json_decode($data['period']);
            $timing = json_decode($data['timing']);
            $company = $data['company_id'];

            foreach ($period as $key => $value) {
              $periods[$value->unit] = substr($timing[$key]->unit, -5);
            }

            $page = $_POST['page'];
            $limit = $_POST['rows'];
            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];

            $extraSQL = " AND o.company_id = " . $company;

            if (isset($_GET['date'])) {
              $current_date = trim(filter_input(INPUT_GET, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
              $date_array = explode(".", $current_date);
              $current_date = date(mktime(0, 0, 0, $date_array[1], $date_array[0], $date_array[2]));
           }

            if ($_POST['_search'] == 'true') {
                if (isset($_POST['filters'])) {
                    $json = stripslashes($_POST['filters']);

                    $filters = json_decode($json);

                    $where = generateSearchStringFromObj($filters);
                    $extraSQL .= " AND " . $where . " ";
                }
            }

            try {
                $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT o.id, u.name AS driver, o.time AS o_time, o.period, o.status, d.time AS d_time, o.date FROM `iwater_orders` AS o LEFT JOIN `iwater_dcontrol` AS d ON (o.id = d.order_id) LEFT JOIN `iwater_users` AS u ON (o.driver = u.id) WHERE `date` = '$current_date' " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $count = 0;
            $s_add_after_calc = "";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
              if (($r['status'] == 0 && $periods[$r['period']] < date('H:i', strtotime("+3 hours", $r['d_time']))) || ($periods[$r['period']] < date('H:i', $r['d_time'])) || ($current_date < date(mktime(0, 0, 0, date('m'), date('d'), date('Y'))) && $r['status'] == 0)) {
                   // Вывод информации о недоставленных заказах
                   $come = ($r['d_time'] == "" ? "☒" : date('H:i', strtotime("+3 hours", $r['d_time'])));

                   $count++;

                   $s_add_after_calc .= "<row>";
                   $s_add_after_calc .= "<cell id='id'><![CDATA[" . $r['id'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='driver'><![CDATA[" . $r['driver'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='time'><![CDATA[" . $r['o_time'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='period'><![CDATA[" . $r['period'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='come'><![CDATA[" . $come . "]]></cell>";
                   $s_add_after_calc .= "</row>";
                }
            }

            $total_pages = ceil($count/$limit);

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            $s .= '<records>' . $count . '</records>';
            $s .= $s_add_after_calc;
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
         }

         if (isset($_GET['list_info_storage'])) {
            // Информация о конкретном складе
            $storage_id = trim(filter_input(INPUT_GET, 'list_info_storage', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
                $res = $dbh->query("SELECT * FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (u.company_id = c.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
            }
             $data = $res->fetch(PDO::FETCH_ASSOC);
            $company = $data['company_id'];

            $page = $_POST['page'];
            $limit = $_POST['rows'];
            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];

            $extraSQL = " AND s.company_id = '" . $company . "' ";
            $today_unixtime = strtotime((string)date('d.m.Y'));

            if ($_POST['_search'] == 'true') {
                if (isset($_POST['filters'])) {
                    $json = stripslashes($_POST['filters']);

                    $filters = json_decode($json);

                    $where = generateSearchStringFromObj($filters);
                    $extraSQL .= " AND " . $where . " ";
                }
            }

           try {
                $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT c.id, c.unit_id AS product_id, a.name AS product, s.name AS storage_name, c.storage AS storage_id, c.count, c.last_update FROM `iwater_storage_count` AS c LEFT JOIN `iwater_units_agr` AS a ON (c.unit_id = a.id) JOIN `iwater_units` AS u ON (a.id = u.id) JOIN `iwater_storage_agr` AS s ON (s.id = c.storage) WHERE c.storage = " . $storage_id . " AND s.date_finish > " . $today_unixtime . $extraSQL . " LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $count = 0;
            $s_add_after_calc = "";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {

                   $count++;

                   $s_add_after_calc .= "<row>";
                   $s_add_after_calc .= "<cell id='count_id'><![CDATA[" . $r['id'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='product_id'><![CDATA[" . $r['product_id'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='product'><![CDATA[" . $r['product'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='count'><![CDATA[" . $r['count'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='storage_id'><![CDATA[" . $r['storage_id'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='storage_name'><![CDATA[" . $r['storage_name'] . "]]></cell>";
                   $s_add_after_calc .= "<cell id='last_update'><![CDATA[" .  date('j F Y',$r['last_update']) . "]]></cell>";
                   $s_add_after_calc .= "</row>";
            }

            $total_pages = ceil($count/$limit);

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            $s .= '<records>' . $count . '</records>';
            $s .= $s_add_after_calc;
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
         }

/**
    *****
    ********
    *************
    *******************
    * КОМАНДЫ НА ВЫГРУЗКУ ДАННЫХ В JSON ФОРМАТЕ
    *******************
    ****************
    ************
    **********
    ********
    ******
    ****
    ***
    **
    *
*/
         if (isset($_POST['category_list'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $today_unixtime = strtotime((string)date('d.m.Y'));

            try {
              $storage = $dbh->query("SELECT s.id, s.name FROM `iwater_storage` AS s_agr JOIN `iwater_storage_agr` AS s ON (s_agr.id = s.id) WHERE s.company_id = '$company' AND s.date_finish > " . $today_unixtime);
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
          }

          $out_array = array();

          while ($s = $storage->fetch(PDO::FETCH_ASSOC)) {
               array_push($out_array, array('id' => $s['id'], 'name' => $s['name']));
          }

          echo json_encode($out_array);
         }

         if (isset($_POST['storage_limit_action'])) {
            try {
               $res = $dbh->query("SELECT `data` FROM `iwater_settings` WHERE `name` = 'storage_limit_action'");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $r = $res->fetch(PDO::FETCH_ASSOC);
            echo $r['data'];
         }
         if (isset($_POST['company_info'])) {
            $id = trim(filter_input(INPUT_POST, 'company_info', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
               $res = $dbh->query("SELECT * FROM `iwater_company` WHERE `id` = '$id'");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            echo json_encode($res->fetch(PDO::FETCH_ASSOC));
         }

         if (isset($_POST['today_drivers'])) {
             $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            if ($date == "") {
              $date = date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));
            }else{
              $date = explode('.', $date);
              $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
            }

             try {
                 $res = $dbh->query("SELECT * FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (u.company_id = c.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $periods = array();

             $data = $res->fetch(PDO::FETCH_ASSOC);

             $period = json_decode($data['period']);
             $timing = json_decode($data['timing']);

             foreach ($period as $key => $value) {
                $periods[$value->unit] = substr($timing[$key]->unit, -5);
             }

             try {
                 $res = $dbh->query("SELECT DISTINCT u.name AS udriver, u.id AS uid FROM `iwater_users` AS u LEFT JOIN `iwater_orders` AS o ON (o.driver = u.id) WHERE `date` = " . $date);
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $driver = array();
             $i = 0;

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $driver[$i]['name'] = $r['udriver'];
                $driver[$i]['id'] = $r['uid'];

                $output_text = '';
                $bad_time = false;
                $alert_index = 0;

                try {
                    $orders_info = $dbh->query("SELECT *, o.coords AS o_coords, o.name as name, a.coords AS a_coords, d.coord AS d_coords, d.time AS d_time, d.tank AS d_tank, d.notice AS d_n FROM `iwater_dcontrol` AS d LEFT JOIN `iwater_orders` AS o ON (o.id = d.order_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) WHERE `date` = " . $date . " AND `driver` = " . $r['uid'] . " AND o.status IN (0, 2) ORDER BY `d_time` DESC");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                   echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while($o = $orders_info->fetch(PDO::FETCH_ASSOC)) {
                    $error_image = '<img src = "/iwaterTest/css/image/tick.png">';

                    if ($o['o_coords'] == "" && $o['a_coords'] == "") {
                        $distance = 'нет данных';
                    } else {
                       $order_cord = ($o['o_coords'] == "") ? ($o['a_coords']) : ($o['o_coords']);
                       $order_cord = explode(",", $order_cord);
                       $driver_cord = $o['d_coords'];
                       $driver_cord = explode(",", $driver_cord);
                       $distance = round(getDistance($order_cord[0], $order_cord[1], $driver_cord[0], $driver_cord[1])) . "м.";
                    }

                    // Проверка, сдана ли точка вовремя и расстояние до неё во время сдачи
                    if ($periods[$o['period']] < date('H:i', $o['d_time'])) { $text = '<tr style="background: #fff; border-bottom: 5px solid #eff3f6; height: 55px;">'; $alert_index = 1; $error_image = '<img src = "/iwaterTest/css/image/time-left.png">';
                    } else if ($distance > 500) {
                       $text = '<tr style="background: #fff; border-bottom: 5px solid #eff3f6; height: 55px;">'; $alert_index = 1; $error_image = '<img src = "/iwaterTest/css/image/pin.png">';
                    } else {
                       $text = '<tr style="background: #fff; border-bottom: 5px solid #eff3f6; height: 55px;">';
                    }
                    // Если заказ не доставлен, а время прошло
                    if ($o['status'] == 0 && $periods[$o['period']] < date()) { $text = '<tr style="background: #fff; border-bottom: 5px solid #eff3f6;">'; $alert_index = 1; $error_image = '<img src = "/iwaterTest/css/image/time-left.png" height: 55px;>'; }

                    $text .= "<td style='border-radius: 10px 0 0 10px;'>" . $o['name'] . "</td><td>" . $o['address'] . "</td><td>" . date('H:i', $o['d_time']) . "</td><td>" . $o['d_tank'] . "шт.</td><td>" . $distance . "</td><td>" . $o['d_n'] . "</td><td style='border-radius: 0 10px 10px 0;'>" . $error_image . "</td></tr>";
                    $output_text .= $text;
                }

                $driver[$i]['text'] = $output_text;
                $driver[$i]['alert'] = $alert_index;
                $i++;
             }
             echo json_encode($driver);
         }
         if (isset($_POST['unit_info'])) {
            $id = trim(filter_input(INPUT_POST, 'unit_info', FILTER_SANITIZE_SPECIAL_CHARS));
            $today_unixtime = strtotime((string)date('d.m.Y'));

            try {
               $res = $dbh->query("SELECT * FROM `iwater_units_agr` WHERE `id` = '$id' AND `date_finish` > '$today_unixtime' LIMIT 1");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $out = $res->fetch(PDO::FETCH_ASSOC);
            echo json_encode($out);
         }

         if (isset($_POST['cut_list'])) {
             $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
             $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));

             try {
                 $res = $dbh->query("SELECT `id`, `address`, `map_num` FROM `iwater_orders` WHERE `driver` = " . $driver . " AND `date` = " . $date);
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $return = array();

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                 array_push($return, $r);
             }

             echo json_encode($return);
         }

         if (isset($_GET['category'])) {
             $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
             $usersess = $res->fetch();
             $company = $usersess['company_id'];

             try {
                 $res = $dbh->query("SELECT `category`, `category_id` FROM `iwater_category` WHERE `company_id` = '$company'");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             if (isset($_POST['category'])) {
                 header('content-type: application/json;charset=utf8');
                 echo json_encode($res->fetchAll());
                 exit();
             }
             echo json_encode('Запрос не удался');
         }

         if (isset($_POST['period']) && !isset($_GET['app'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

             try {
                 $res = $dbh->query("SELECT DISTINCT `period` FROM `iwater_company` WHERE `id` = '$company'");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $r = $res->fetchAll();

             header('content-type: application/json;charset=utf8');
             echo json_encode($r);
         }

         if (isset($_POST['migrate_ord'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
           $usersess = $res->fetch();
           $company = $usersess['company_id'];

           try {
               $res = $dbh->prepare("SELECT o.address, `date`, o.period, `notice`, `water_equip`, `status`, com.region, o.client_id, o.id, c.name, o.name AS o_name, c.phone FROM `iwater_orders_app` AS o LEFT JOIN `iwater_clients_app` AS c ON (o.client_id = c.id) LEFT JOIN `iwater_company` AS com ON (com.id = o.company_id) WHERE o.id = ?");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               $res->execute(array($_POST['migrate_ord']));
           } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $return = $res->fetch(PDO::FETCH_ASSOC);

           $temp_str = $return['water_equip'];
           $temp_str = json_decode($temp_str);
           $order = array();

           if ($return['name'] == "") {
              $return['name'] = $return['o_name'];
           }

           foreach ($temp_str as $key => $value) {
              $value = (array) $value;
              $order[$value['id']] = $value['count'];
           }

           $cash_formula = "";

           try {
               $units = $dbh->query("SELECT * FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id)");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $units_array = array();

           while ($u = $units->fetch(PDO::FETCH_ASSOC)) {
              $units_array[$u['id']] = $u['price'];
           }

           foreach ($order as $key => $value) {
              if (array_key_exists($key, $units_array)) {
                 $price = explode(';', $units_array[$key]);

                 foreach ($price as $key_l => $value_l) {
                    $index = explode(':', $value_l);
                    $next_index = explode(':', $price[$key_l + 1]);

                    if (!array_key_exists($key_l + 1, $price) || $next_index[0] > $index[0]) {
                       $cash_formula .= $key . "*" . $index[1] . "+";
                       break;
                    }
                 }
              }
           }


           $cash_formula = substr($cash_formula, 0, -1);

           $return['cash_formula'] = $cash_formula;

           header('content-type: application/json;charset=utf8');
           echo json_encode($return);
         }

         if (isset($_POST['update_ord'])) {
             try {
                 $res = $dbh->prepare("SELECT * FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id = c.id) WHERE o.id = ?");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                 $res->execute(array($_POST['update_ord']));
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             header('content-type: application/json;charset=utf8');
             $exit = $res->fetchAll();

             $equip = unserialize($exit[0]['water_equip']);

             $ret = array();

             foreach ($equip as $key => $value) {
                 array_push($ret, array('id' => $key, 'count' => $value));
             }

             $exit[0]['water_equip'] = json_encode($ret);

             echo json_encode($exit);
             exit();
         }

         if (isset($_POST['water_list'])) {
             $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
             $usersess = $res->fetch();
             $company = $usersess['company_id'];

             $return_array = array();

             try {
                 $res = $dbh->query("SELECT u_a.shname FROM `iwater_units_agr` AS u_a LEFT JOIN `iwater_category` AS c ON (u_a.category = c.category_id) LEFT JOIN `iwater_units` AS u ON (u_a.id = u.id) WHERE company_id = '$company' AND u_a.shname IS NOT NULL");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                   array_push($return_array, array('name' => $r['shname'], 'index' => $r['shname'], 'width' => 40, 'align' => 'center', 'editable' => false, 'sortable' => false));
             }

             echo json_encode($return_array);
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

         if (isset($_POST['name_l'])) {
             //Поиск имени клиента
             $name = trim(filter_input(INPUT_POST, 'name_l', FILTER_SANITIZE_SPECIAL_CHARS));
             $name = $name;
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
                    'value' => $r['name'],
                    'label' => $r['name'],
                    'desc' => $r['client_id'] . " |  " . $r['address']
                 );
             }
             $response = json_encode($json);
             echo $response;
             die();
         }

         if (isset($_POST['address_l'])) {
             //Поиск адреса
             $address = trim(filter_input(INPUT_POST, 'address_l', FILTER_SANITIZE_SPECIAL_CHARS));
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
                    'value' => $r['address'],
                    'label' => $r['address'],
                    'desc' => $r['client_id'] . " | " . $r['name']
                 );
             }
             $response = json_encode($json);
             echo $response;
             die();
         }

         if (isset($_POST['fill_order'])) {
             $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
             try {
                 $res = $dbh->query("SELECT *, u.id AS u_id FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON o.driver = u.id  WHERE o.id='$id' ORDER BY o.id DESC LIMIT 1;");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }
             $regions = array('В работе', 'Отмена', 'Доставлен', 'Перенос');
             $s = "<?xml version='1.0' encoding='utf8'?>";
             $s .= "<rows>";

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                 $s .= "<row>";
                 $s .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['time'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['period'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . htmlspecialchars_decode($r['notice']) . ']]></cell>';

                 $equip = unserialize($r['water_equip']);
                 $ret = array();
                 foreach ($equip as $key => $value) {
                    array_push($ret, array('id' => $key, 'count' => $value));
                 }

                 $s .= '<cell><![CDATA[' . json_encode($ret) . ']]></cell>';
                 $s .= '<cell><![CDATA[' . htmlspecialchars_decode($r['equip']) . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['dep'] . ']]></cell>';
                 if ($r['cash'] == "") {
                    $s .= '<cell><![CDATA[' . $r['cash'] . ']]></cell>';
                 } else {
                    $s .= '<cell><![CDATA[' . $r['cash_formula'] . ']]></cell>';
                 }
                 if ($r['cash_b'] == "") {
                    $s .= '<cell><![CDATA[' . $r['cash_b'] . ']]></cell>';
                 } else {
                    $s .= '<cell><![CDATA[' . $r['cash_formula'] . ']]></cell>';
                 }
                 $s .= '<cell><![CDATA[' . $r['on_floor'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['tank_b'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['tank_empty_now'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['u_id'] . ']]></cell>';
                 $s .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                 $s .= "</row>";
             }

             $s .= "</rows>";
             header("Content-type: text/xml;charset=utf8");
             echo $s;
         }

         if (isset($_POST['driver_map_info'])) {
             $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
             $driver_id = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));

             $water_array = array();

             try {
                 $res = $dbh->query("SELECT * FROM `iwater_units_agr` WHERE `shname` IS NOT NULL");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                 array_push($water_array, $r['id']);
             }

             $extraSQL = $date;

             if ($driver_id != "") {
                 $extraSQL .= " AND `driver` =" . $driver_id;
             }

             if (isset($_POST['exception_driver'])) {
                 $exc = $_POST['exception_driver'];
                 for ($i = 0; $i < count($exc); $i++) {
                     $extraSQL .= " AND `driver` !=" . $exc[$i];
                 }
             }

             try {
                 $res = $dbh->query("SELECT *, o.address, a.coords AS cor_p, o.coords AS cor_new, o.id AS order_id, u.name as driver_name  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND o.date =" . $date . " AND o.status IN (0, 2)");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             $extraSQL = $date;

             if ($driver_id != "" && $driver_id != "all") {
                 $extraSQL .= " AND `driver` =" . $driver_id;
             }

             try {
                 $res = $dbh->query("SELECT o.list AS d_list, u.id as driver_id, u.name as driver_name, SUM(o.water_total) AS total, `water_equip`,
                 (SELECT COUNT(DISTINCT o.id) FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id)
                   LEFT JOIN `iwater_addresses` as a ON (o.address = a.address)
                   LEFT JOIN `iwater_users` as u ON (o.driver = u.id)
                   WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0)
                   AND o.date = " . $extraSQL . " AND o.status IN (0, 2) AND `list` = d_list
                   ORDER BY map_num, o.id DESC) AS count1
                   FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id)
                   LEFT JOIN `iwater_addresses` as a ON (o.address = a.address)
                   LEFT JOIN `iwater_users` as u ON (o.driver = u.id)
                   WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0)
                   AND o.date = " . $extraSQL . " AND o.status IN (0, 2)
                   GROUP BY `d_list` ORDER BY map_num, o.id DESC");

                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }
             $array = array();
             $i = 0;
             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                if ($r['d_list'] !== NULL) {
                    $array[$i] = array();
                    $total = 0;
                    $file_name = "";

                    array_push($array[$i], $r['d_list']);
                    array_push($array[$i], $r['driver_name']);
                    $wt = $dbh->query("SELECT DISTINCT o.id, `water_equip`, l.file as file_name FROM `iwater_orders` AS o LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) LEFT JOIN `iwater_lists` as l ON (o.list = l.id) WHERE o.status IN (0, 2) AND l.id = " . $r['d_list']);

                    while ($w = $wt->fetch(PDO::FETCH_ASSOC)) {
                      $water = unserialize($w['water_equip']);
                      $file_name = $w['file_name'];
                      foreach ($water as $key => $value) {
                          if (in_array($key, $water_array)) {
                              $total += $value;
                          }
                       }
                    }
                    array_push($array[$i], $total);
                    array_push($array[$i], $r['count1']);
                    array_push($array[$i], $r['driver_id']);
                    array_push($array[$i], '<option value="' . $r['driver_id'] . '" selected>' . $r['driver_name'] . '</option>');
                    array_push($array[$i],$file_name);
                    $i++;
                 }
             }
             print_r(json_encode($array));
         }

         if (isset($_POST['storage_list'])) {
           $current_date = date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));
           $final_array = array();

           try {
             $res = $dbh->query("SELECT s.id, s.name FROM `iwater_storage_agr` AS s LEFT JOIN `iwater_users` AS u ON (s.company_id = u.company_id) WHERE session = '" . $_SESSION['fggafdfc'] . "' AND `date_finish` > " . $current_date);
             $dbh->setAttribute(POD::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
             array_push($final_array, array('id' => $r['id'], 'name' => $r['name']));
           }

           echo json_encode($final_array);
         }

         if (isset($_POST['storage_info'])) {
           $id = trim(filter_input(INPUT_POST, 'storage_info', FILTER_SANITIZE_SPECIAL_CHARS));
           $current_date = date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));

           try {
             $res = $dbh->query("SELECT * FROM `iwater_storage_agr` WHERE `id` = '$id' AND `date_finish` > '$current_date'");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: '. $e->getMessage();
           }

           $r = $res->fetch(PDO::FETCH_ASSOC);
           echo json_encode($r);
         }
         if (isset($_POST['provider_list'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $out_array = array();

            try {
             $res = $dbh->query("SELECT * FROM `iwater_providers`;");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
             array_push($out_array, array('id' => $r['id'], 'name' => $r['name']));
          }

          echo json_encode($out_array);
         }
/**
    *****
    ********
    *************
    *******************
    * КОМАНДЫ НЕ СОРТИРОВАННЫЕ
    *******************
    ****************
    ************
    **********
    ********
    ******
    ****
    ***
    **
    *
*/

        if (isset($_POST['save_storage'])) {
            // Сохранить склад
            $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            $priority = trim(filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $storeman = trim(filter_input(INPUT_POST, 'storeman', FILTER_SANITIZE_SPECIAL_CHARS));
            $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS));
            $coords = trim(filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_SPECIAL_CHARS));
            $today_unixtime = strtotime(date('Y-m-d'));

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            /**
             * Удаление записи, если изменения на сегодня уже были
            */
            try {
               $res = $dbh->query("SELECT * FROM `iwater_storage_agr` WHERE `date_start` = " . $today_unixtime . " AND `id` = " . $id);
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            if ($res->rowCount() > 0) {
               try {
                  $dbh->query("DELETE FROM `iwater_storage_agr` WHERE `id` = " . $id . " AND `date_start` = " . $today_unixtime);
                  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               } catch (Exception $e) {
                  echo 'Подключение не удалось: ' . $e->getMessage();
               }
            }

            try {
               $res = $dbh->prepare("UPDATE `iwater_storage_agr` SET `date_finish` = ? WHERE `id` = ? AND `date_finish` > ?");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $res->execute(array(strtotime(date('Y-m-d')) - 86400, $id, strtotime(date('Y-m-d')) - 86400));

            try {
               $res = $dbh->prepare("INSERT INTO `iwater_storage_agr`(`id`, `company_id`, `name`, `priority`, `address`, `coords`, `storeman_name`, `storeman_phone`, `date_start`, `date_finish`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $res->execute(array($id, $company, $name, $priority, $address, $coords, $storeman, $contact, mktime(0, 0, 0, date('m'), date('d'), date('Y')), 2147483647));

            setActionLog("storage", "Индивидуальное редактирование", "iwater_storage_agr", "Изменена инфомарция о складе: " . $id);
            return;
        }
        if (isset($_POST['gallery_list'])) {
           $id = trim(filter_input(INPUT_POST, 'gallery_list', FILTER_SANITIZE_SPECIAL_CHARS));

           $images_list = scandir($_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $id);
           $exist_array = array();

           foreach ($images_list as $value) {
              $str = explode('.', $value);

              if (is_numeric($str[0])) {
                 array_push($exist_array, $value);
              }
           }

           echo json_encode($exist_array);
        }
        if (isset($_POST['delete_gallery'])) {
           $unit = trim(filter_input(INPUT_POST, 'delete_gallery', FILTER_SANITIZE_SPECIAL_CHARS));
           $image = trim(filter_input(INPUT_POST, 'image', FILTER_SANITIZE_SPECIAL_CHARS));

           try {
              $res = $dbh->query("SELECT * FROM `iwater_units_agr` AS u LEFT JOIN `iwater_category` AS k ON (u.category = k.category_id) LEFT JOIN `iwater_company` AS c ON (k.company_id = c.id) WHERE u.id = '$unit' AND `session` = '" . $_SESSION['fggafdfc'] . "'");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           if ($res->rowCount() > 0) {
             unlink('/iwater_api/images/' . $unit . '/' . $image);
           }

           echo 'Success';
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
            if ($type == "on") {
                $type = 1;
            }
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

            setActionLog("client", "Индивидуальное редактирование", "iwater_clients", "Изменение клиента: " . $num_c);
            header('Location: /iwaterTest/admin/list_clients/');
        }

        if (isset($_POST['delete_client'])) {
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

        if (isset($_POST['restablish_client'])) {
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
        if (isset($_POST['list_clients'])) {
            //Список клиентов

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $json = stripcslashes($_POST['list_clients']);
            $data = json_decode($json, true);
            $query = " WHERE for_delete = 0 AND `company_id` = '$company'";

            if ($data[0] != null || $data[1] != null || $data[2] != null) {
                if ($data[0] != null) {
                    if ($data[1] != null || $data[2] != null) {
                        $query .= " AND c.client_id LIKE '%" . $data[0] . "%' AND";
                    } else {
                        $query .= " AND c.client_id LIKE '%" . $data[0] . "%'";
                    }
                }
                if ($data[1] != null) {
                    if ($data[0] == null) {
                        $query .= ' AND ';
                    }
                    if ($data[2] == null) {
                        $query .= ' c.name LIKE "%' . $data[1] . '%"';
                    } else {
                        $query .= ' c.name LIKE "%' . $data[1] . '%" AND';
                    }
                }
                if ($data[2] != null) {
                    if ($data[0] == null && $data[1] == null) {
                        $query .= ' AND ';
                    }
                   // $query .= ' a.contact LIKE "%' . $data[2] . '%"';
			$query .= ' AD.contact_search_form LIKE "%' . $data[2] . '%" ';
                }
            }

            $current_page = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $offset = $lists_in_page * ($current_page - 1);

		$str_sql = "SELECT *, c.id AS c_id, AD.contact_search_form FROM `iwater_clients` " .
                       " AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id " .
                       " INNER JOIN (select client_id, Replace(replace( contact,'-',''),'+','') as contact_search_form FROM `iwater_addresses`)as AD ON c.client_id = AD.client_id " . 
                       $query . " LIMIT " . $lists_in_page . " OFFSET " . $offset . ";";


            try {
               // $res = $dbh->prepare("SELECT *, c.client_id, c.type, c.id AS c_id FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id " . $query . " LIMIT " . $lists_in_page . " OFFSET " . $offset);
		$res = $dbh->prepare($str_sql);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= "<rows>";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row id='" . $r['c_id'] . "'>";
                $s .= "<cell>" . $r['c_id'] . "</cell>";
                $s .= "<cell></cell>";
                $s .= "<cell>" . $r['type'] . "</cell>";
                $s .= "<cell><![CDATA[" . trim(filter_var($r['name'], FILTER_SANITIZE_SPECIAL_CHARS)) . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['client_id'] . "]]></cell>";
                try {
                    $res_addr = $dbh->prepare("SELECT * FROM `iwater_addresses` WHERE `client_id` =" . $r['client_id'] . ";");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res_addr->execute();
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r_a = $res_addr->fetch(PDO::FETCH_ASSOC)) {
                    $s .= '<cell class="addr"><cell><![CDATA[' . $r_a['region'] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a['address'] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . stripcslashes(trim(filter_var($r_a['contact'], FILTER_SANITIZE_SPECIAL_CHARS))) . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a['coords'] . "]]></cell></cell>";
                }

                $s .= "</row>";
            }
            $s .= $query . "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }
        if (isset($_POST['delete_list_clients'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $res = $dbh->prepare("SELECT *, c.id AS c_id, u.login AS u_name, c.name AS c_name FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id LEFT JOIN `iwater_users` AS u ON c.user_changing = u.id  WHERE c.for_delete = 1 AND c.company_id = '$company';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= "<rows>";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row id='" . $r['c_id'] . "'>";
                $s .= "<cell>" . $r['c_id'] . "</cell>";
                $s .= "<cell></cell>";
                $s .= "<cell>" . $r['type'] . "</cell>";
                $s .= "<cell><![CDATA[" . trim(filter_var($r['c_name'], FILTER_SANITIZE_SPECIAL_CHARS)) . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['client_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['u_name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . date("d/m/Y H:i:s", $r['time_change']) . "]]></cell>";
                try {
                    $res_addr = $dbh->prepare("SELECT * FROM `iwater_addresses` WHERE `client_id` = " . $r['client_id'] . ";");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res_addr->execute();
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r_a = $res_addr->fetch(PDO::FETCH_ASSOC)) {
                    $s .= '<cell class="addr"><cell><![CDATA[' . $r_a['region'] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a['address'] . "]]></cell>";
                    $s .= "<cell><![CDATA[" . stripcslashes(trim(filter_var($r_a['contact'], FILTER_SANITIZE_SPECIAL_CHARS))) . "]]></cell>";
                    $s .= "<cell><![CDATA[" . $r_a['coords'] . "]]></cell></cell>";
                }

                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }
        if (isset($_POST['list_lists'])) {
            $page = $_POST['trav_page'];
            $limit = 7;
            $extraSQL = '';

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            if ($_GET['from']) {
               $extraSQL .= " AND l.date > " . strtotime($_GET['from']);
            }

            if ($_GET['to']) {
               $extraSQL .= " AND l.date < " . strtotime($_GET['to']);
            }

            $result = $dbh->query("SELECT COUNT(DISTINCT l.id) as count FROM `iwater_lists` AS l JOIN `iwater_orders` AS o ON (l.id = o.list) WHERE l.company_id = '$company'");
            $count = $result->fetch(PDO::FETCH_ASSOC);
            $count = $count['count'];

            $total_pages = ceil($count/$limit);
            $start = ($page - 1) * $limit;

            $last_date = 0;

            try {
                $res = $dbh->query("SELECT DISTINCT l.file, MAX(l.map_num) AS map,l.date AS list_date, l.id AS list_id, l.create_date, u.login, u.id, u2.id AS driver_id, u2.name AS driver_name, o.date AS order_date FROM `iwater_lists` AS l LEFT JOIN `iwater_users` AS u ON l.user_id = u.id INNER JOIN `iwater_orders` AS o ON l.id = o.list LEFT JOIN `iwater_users` AS u2 ON l.driver_id = u2.id WHERE l.company_id = '$company' " . $extraSQL . " GROUP BY l.file ORDER BY l.id DESC LIMIT " . $start . ", " . $limit);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= '<rows>';
            $s .= '<page>' . $page . '</page>';
            $s .= '<total>' . $total_pages . '</total>';
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {

                  if ($last_date != $r['order_date'] && strlen($r['file']) > 13) {
                     $s .= "<row>";
                     $s .= '<cell>' . date('d.m.Y', $r['order_date']) . "(" . $company . ").xlsx</cell>";
                     $s .= '<cell><a class="xlsx" href="/iwaterTest/files/' . date('d.m.Y', $r['order_date']) . "(" . $company . ").xlsx" . '">' . "Скачать" . "</a></cell>";
                     $s .= "<cell><![CDATA[" . $r['login'] . "]]></cell>";
                     $s .= "<cell>" . date("d/m/Y H:i:s", $r['create_date']) . "</cell>";
                     $s .= "<cell><![CDATA[]]>" . $r['date'] . "</cell>";
                     $s .= "<cell><![CDATA[]]></cell>";
                     $s .= "<cell><![CDATA[]]></cell>";
                     $s .= "<cell><![CDATA[]]></cell>";
                     $s .= "<cell><![CDATA[" . $r['list_date'] . "]]></cell>";
                     $s .= "</row>";
                  }

                $file = $r['file'];
                $s .= "<row id='" . $r['list_id'] . "'>";
                $s .= '<cell>' . $file . "</cell>";
                $s .= '<cell><a class="xlsx" href="/iwaterTest/files/' . $r['file'] . '">' . "Скачать" . "</a></cell>";
                $s .= "<cell><![CDATA[" . $r['login'] . "]]></cell>";
                $s .= '<cell>' . date("d/m/Y H:i:s", $r['create_date']) . "</cell>";
                $s .= "<cell><![CDATA[" . $date[0] . "?driver_id=" . $r['driver_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['list_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['driver_id'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['driver_name'] . "]]></cell>";
                $s .= "<cell><![CDATA[" . $r['list_date'] . "]]></cell>";
                $s .= "</row>";
                $last_date = $r['order_date'];
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }

        if(isset($_GET['list_provider'])){
           $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
             $res = $dbh->query("SELECT * FROM `iwater_providers`WHERE company_id = $company");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

            $out = "<?xml version='1.0' encoding='utf8'?>";
            $out .= '<rows>';
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $out .= "<row id='" . $r['id'] . "'>";
                $out .= '<cell><![CDATA[' . $r['name'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['contact'] . ']]></cell>';
                $out .= '<cell><![CDATA[<a href="/iwaterTest/admin/edit_provider?id=' . $r['id'] . '"><img src="/iwaterTest/css/image/edit.png"></a>]]></cell>';
                $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                $out .= '</row>';
            }
            $out .= '</rows>';
            header("Content-type: text/xml;charset=utf8");
            echo $out;
        }

        if (isset($_GET['list_cat'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $res = $dbh->query("SELECT `category`, `priority` FROM `iwater_category` WHERE company_id = $company ORDER BY `priority`");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $out = "<?xml version='1.0' encoding='utf8'?>";
            $out .= '<rows>';
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $out .= "<row id='" . $r['category'] . "'>";
                $out .= '<cell><![CDATA[' . $r['category'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['priority'] . ']]></cell>';
                $out .= '</row>';
            }
            $out .= '</rows>';
            header("Content-type: text/xml;charset=utf8");
            echo $out;
        }

        if (isset($_POST['region']) && !isset($_POST['address'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $reg = $dbh->query("SELECT `regions` FROM `iwater_company` WHERE `id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $json_str = $reg->fetch();
            $return_str = $json_str['regions'];

            echo $return_str;
        }

        if (isset($_POST['get_formula'])) {
            $id = trim(filter_input(INPUT_POST, 'get_formula', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
                $reg = $dbh->query("SELECT `cash_formula` FROM `iwater_orders` WHERE `id` = " . $id);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $json_str = $reg->fetch();
            $return_str = $json_str['cash_formula'];

            echo $return_str;
        }

        if (isset($_POST['edit_company'])) {
           $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
           $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS));
           $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
           $schedule = trim(filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_SPECIAL_CHARS));
           $city = trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS));
           $regions = trim(filter_input(INPUT_POST, 'regions', FILTER_SANITIZE_SPECIAL_CHARS));

           try {
              $res = $dbh->prepare("UPDATE `iwater_company` SET name = ?, contact = ?, schedule = ?, city = ?, regions = ? WHERE `id` = ?");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $res->execute(array($name, $contact, $schedule, $city, $regions, $id));
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           setActionLog("company", "Редактирование", "iwater_company", "");
           header('Location: /iwaterTest/admin/list_company/');
        }
        if (isset($_POST['setting_mail'])) {
            try {
                $res = $dbh->query("SELECT * FROM `iwater_settings`");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $out_string = '';

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $out_string .= $r['data'];
            }

            echo $out_string;
        }

        if (isset($_POST['page_clients'])) {
            //Список клиентов
            $json = stripcslashes($_POST['page_clients']);
            $data = json_decode($json, true);
            $get = "&";
            if ($data[0] != null || $data[0] != "") {
                $get .= "num=" . $data[0] . "&";
            }
            if ($data[1] != null || $data[1] != "") {
                $get .= "name=" . $data[1] . "&";
            }
            if ($data[2] != null || $data[2] != "") {
                $get .= "cont=" . $data[2] . "&";
            }

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $query = ' WHERE for_delete = 0 AND company_id = ' . $company;
            if ($data[0] != null || $data[1] != null || $data[2] != null) {
                if ($data[0] != null) {
                    if ($data[1] != null || $data[2] != null) {
                        $query .= ' AND c.client_id LIKE "%' . $data[0] . '%" AND';
                    } else {
                        $query .= ' AND c.client_id LIKE "%' . $data[0] . '%"';
                    }
                }
                if ($data[1] != null) {
                    if ($data[0] == null) {
                        $query .= ' AND ';
                    }
                    if ($data[2] == null) {
                        $query .= ' c.name LIKE "%' . $data[1] . '%"';
                    } else {
                        $query .= ' c.name LIKE "%' . $data[1] . '%" AND';
                    }
                }
                if ($data[2] != null) {
                    if ($data[0] == null && $data[1] == null) {
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
            $count_pages = ceil($count_lists / $lists_in_page) - 2; /* Вот тут я поставил минус 2, иначе даёт 2 пустые страницы в конце */
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
                if ($left < floor($count_show_pages / 2)) {
                    $start = 1;
                } else {
                    $start = $active - floor($count_show_pages / 2);
                }
                $end = $start + $count_show_pages - 1;
                if ($end > $count_pages) {
                    $start -= ($end - $count_pages);
                    $end = $count_pages;
                    if ($start < 1) {
                        $start = 1;
                    }
                } ?>
                <!-- Дальше идёт вывод Pagination -->
                <div id="pagination">
                    <span>Pages: </span>
                    <?php if ($active != 1) {
                    ?>
                        <a href="<?= $url . $get ?>" title="Первая страница">&lt;&lt;&lt;</a>
                        <a href="<?php if ($active == 2) {
                        ?><?= $url . $get ?><?php
                    } else {
                        ?><?= $url_page . ($active - 1) . $get ?><?php
                    } ?>"
                           title="Предыдущая страница">&lt;</a>
                    <?php
                } ?>
                    <?php for ($i = $start; $i <= $end; $i++) {
                    ?>
                        <?php if ($i == $active) {
                        ?><span><?= $i ?></span><?php
                    } else {
                        ?><a
                            href="<?php if ($i == 1) {
                            ?><?= $url . $get ?><?php
                        } else {
                            ?><?= $url_page . $i . $get ?><?php
                        } ?>"><?= $i ?></a><?php
                    } ?>
                    <?php
                } ?>
                    <?php if ($active != $count_pages) {
                    ?>
                        <a href="<?= $url_page . ($active + 1) . $get ?>" title="Следующая страница">&gt;</a>
                        <a href="<?= $url_page . $count_pages . $get ?>" title="Последняя страница">&gt;&gt;&gt;</a>
                    <?php
                } ?>
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
        if (isset($_POST['edit_order'])) {
            $id = trim(filter_input(INPUT_POST, 'db_id', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query("SELECT *,u.name AS d_name, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON(o.driver=u.id)  WHERE o.id='$id';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            };
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                setActionLog("order", "Индивидуальное редактирование", "iwater_orders", "Клиент: " . $r['client_id'] . " Водитель: " . $r['name'] . " Старые данные: " . $r['id'] . " " . $r['client_id'] . " " . $r['address'] . " " . $r['contact'] . " " . $r['date'] . " " . $r['no_date'] . " " . $r['time'] . " " . $r['time_d'] . " " . $r['notice'] . " " . $r['water_equip'] . " " . $r['water_total'] . " " . $r['equip'] . " " . $r['dep'] . " " . $r['cash'] . " " . $r['cash_b'] . " " . $r['on_floor'] . " " . $r['tank_b'] . " " . $r['tank_empty_now'] . " " . $r['driver'] . " " . $r['status'] . " " . $r['reason'] . " " . $r['region']);
            }

            $client_id = trim(filter_input(INPUT_POST, 'client_num', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST,'name'));
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
            $cash = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
            $cash_b = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $no_date = trim(filter_input(INPUT_POST, 'no_date', FILTER_SANITIZE_SPECIAL_CHARS));
            $time = trim(filter_input(INPUT_POST, 'time'));
            $time_d = trim(filter_input(INPUT_POST, 'time_d', FILTER_SANITIZE_SPECIAL_CHARS));
            $notice = trim(filter_input(INPUT_POST, 'notice'));
            $water_equip = trim(filter_input(INPUT_POST, 'water_equip', FILTER_SANITIZE_SPECIAL_CHARS));
            $equip = trim(filter_input(INPUT_POST, 'equip'));
            $dep = trim(filter_input(INPUT_POST, 'dep', FILTER_SANITIZE_SPECIAL_CHARS));
            $cash_formula = trim(filter_input(INPUT_POST, 'cash_formula'));
            $on_floor = trim(filter_input(INPUT_POST, 'on_floor', FILTER_SANITIZE_SPECIAL_CHARS));
            $tank_b = trim(filter_input(INPUT_POST, 'tank_b', FILTER_SANITIZE_SPECIAL_CHARS));
            $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
            $storage = trim(filter_input(INPUT_POST, 'storage', FILTER_SANITIZE_SPECIAL_CHARS));
            $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
            $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = explode('/', $date);
            $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

            $Cal = new Field_calculate();

            if ($no_date) {
                $date = '';
                $no_date = 1;
            }

            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `client_id`=?, `name`=?, `address`=?, `coords`=?, `contact`=?, `date`=?, `no_date`=?, `time`=?, `period`=?, `notice`=?, `water_equip`=?, `equip`=?, `dep`=?, `cash`=?, `cash_b`=?,`cash_formula`=?, `cash_b_formula`=?, `on_floor`=?, `tank_b`=?, `tank_empty_now`=?, `driver`=?, `status`=?, `reason`=?, `region`=? WHERE `id`='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($client_id, $name, $address, $cords, $contact, $date, $no_date, $time, $time_d, $notice, $water_equip, $equip, $dep, $cash, $cash_b, $cash_formula, $cash_b_formula, $on_floor, $tank_b, $tank_empty_now, $driver, $status, $reason, $region));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            // header('Location: /iwaterTest/admin/list_orders/');
            // + костыль из-за echo выше
            echo '<script>location.replace("/iwaterTest/admin/list_orders/");</script>'; exit;
        }

        if (isset($_POST['price_units'])) {
            $id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_SPECIAL_CHARS);

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $res = $dbh->query("SELECT `water_equip` FROM `iwater_orders_app` WHERE `id` = " . $id);
                $dbh->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }



            $full_price = 0; // Общая цена

          //while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
          //  $pr = $dbh->query("SELECT * FROM `iwater_units` WHERE `id` = " . );
          //}
        }

         if (isset($_GET['info_storage'])) {

                    $id = trim(filter_input(INPUT_POST, 'count_id', FILTER_SANITIZE_SPECIAL_CHARS));
                    $product_id =  trim(filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_SPECIAL_CHARS));
                    $storage =  trim(filter_input(INPUT_POST, 'storage_name', FILTER_SANITIZE_SPECIAL_CHARS));
                    $count =  trim(filter_input(INPUT_POST, 'count', FILTER_SANITIZE_SPECIAL_CHARS));
                    $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS));

                    echo $id . ";". $storage ." ; ". $count ." ; ". $comment ." ; ". $old_storage;
                    $last_update = time();

                    $old_unit_id = "";
                    $old_storage = "";
                    $operation = "";
                    $old_count = "";
                    $old_date = "";
                    $id_exist = "";
                    $count_product = "";
                    try {
                        $res = $dbh->query("SELECT * FROM `iwater_storage_count` WHERE `id` = " . $id);
                        $dbh->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                     while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $old_unit_id = $r['unit_id'];
                        $old_storage = $r['storage'];
                        $old_count = $r['count'];
                        $old_date = $r['last_update'];
                     }

                     if ($old_count > $count) {
                        $operation = "Списание";
                     }
                     if ($old_count < $count){
                        $operation = "Приход";
                     }
                     if ($old_count == $count){
                        $operation = "Перенос";
                     }

                     try {
                        $res_count = $dbh->query("SELECT id, COUNT(*) AS count_id, count AS count_product FROM `iwater_storage_count` WHERE `unit_id` = " . $product_id . "  AND storage = " . $storage . "");
                        $dbh->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                    while ($r = $res_count->fetch(PDO::FETCH_ASSOC)) {
                        if($r['count_id'] == 1){
                            $id_exist = $r['id'];
                            $count_product = $r['count_product'] + $count;
                        }
                    }


                    try {
                        $res = $dbh->prepare("INSERT INTO `iwater_storage_history` (`unit_id`, `storage`, `operation`, `count`, `comment`, `date`) VALUES(?, ?, ?, ?, ?, ?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array($old_unit_id, $old_storage, $operation, $old_count, $comment, $old_date));
                    } catch (Exception $e) {
                        echo "Подключение не удалось: " . $e->getMessage();
                    }

                    if(!empty($id_exist)){
                     try {
                        $res=$dbh->prepare('UPDATE `iwater_storage_count` SET `storage` = ?, `count` = ?, `last_update`=? WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array( $storage,$count_product, $last_update, $id_exist ));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                    try {
                        $res=$dbh->prepare('DELETE FROM `iwater_storage_count` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array($id));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                    }else{
                    try {
                        $res=$dbh->prepare('UPDATE `iwater_storage_count` SET `storage` = ?, `count` = ?, `last_update`=? WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array( $storage,$count, $last_update, $id ));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                  }
                  setActionLog("storages", "Изменение", "iwater_storage", "Совершен перенос на складах №" . $id_exist . " на " . $id);
                  return;
                }

        if (isset($_POST['oper'])) {
            if ($_POST['oper'] == 'add') {
                if (isset($_GET['edit_cat'])) {
                    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                    $usersess = $res->fetch();
                    $company = $usersess['company_id'];

                    $name = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
                    $priority = trim(filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_SPECIAL_CHARS));

                    try {
                        $res = $dbh->prepare("INSERT INTO `iwater_category` (`category`, `company_id`, `priority`) VALUES(?, ?, ?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array($name, $company, $priority));
                    } catch (Exception $e) {
                        echo "Подключение не удалось: " . $e->getMessage();
                    }

                    setActionLog("category", "Добавление", "iwater_category", "Добавлена новая категория: " . $name);

                    return;
                }
                if (isset($_GET['units'])) {
                   $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                   $gl_id =  trim(filter_input(INPUT_POST, 'gl_id', FILTER_SANITIZE_SPECIAL_CHARS));
                   $name =  trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                   $shname =  trim(filter_input(INPUT_POST, 'shname', FILTER_SANITIZE_SPECIAL_CHARS));
                   $about =  trim(filter_input(INPUT_POST, 'about', FILTER_SANITIZE_SPECIAL_CHARS));
                   $price =  trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_SPECIAL_CHARS));
                   $discount = trim(filter_input(INPUT_POST, 'discount', FILTER_SANITIZE_SPECIAL_CHARS));
                   $category =  trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));

                   if ($shname == '') { $shname = null; }

                   try {
                      $res = $dbh->query("INSERT INTO `iwater_units`() VALUES()");
                      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   } catch (Exception $e) {
                      echo 'Подключение не удалось: ' . $e->getMessage();
                   }

                   $last_id = $dbh->lastInsertId();
                   $today_unixtime = strtotime((string)date('d.m.Y'));

                   try {
                       $res=$dbh->prepare("INSERT INTO `iwater_units_agr`(`id`, `gl_id`, `name`, `shname`, `price`, `discount`, `category`, `about`, `date_start`, `date_finish` ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                       $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                       $res->execute(array( $last_id, $gl_id, $name, $shname, $price, $discount, $category, $about, $today_unixtime, 2147483647));
                   } catch (Exception $e) {
                       echo "Подключение не удалось: " . $e->getMessage();
                   }

                   /**
                    * Записали всю информацию в базу и теперь начинаем выгражуть файлы
                   */

                   // Создаём каталог для товара
                   mkdir($_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $last_id , 0700);

                   // Создаём нарезанные логотипы
                   // Сначала тянем формат файла, чтобы сохранить в том же
                   $file_name = $_FILES["logo"]["name"];
                   $file_name = explode('.', $file_name);
                   move_uploaded_file($_FILES["logo"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $last_id . '/0.' . $file_name[1]);
                   scaleImage($last_id, 150);

                   for ($number_file = 1; $number_file <= count($_FILES['gallery']['name']); $number_file++) {
                      // Сначала тянем формат файла, чтобы сохранить в том же
                      $file_name = $_FILES["gallery"]["name"][$number_file];
                      $file_name = explode('.', $file_name);

                      move_uploaded_file($_FILES["gallery"]["tmp_name"][$number_file], $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $last_id . '/' . $number_file . '.' . $file_name[1]);
                   }

                   setActionLog("units", "Добавление", "iwater_units", "Добавлен новый товар: " . $name);

                   return;
               }
            }
            if ($_POST['oper'] == 'edit') {
               if (isset($_GET['units'])) {
                   $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                   $gl_id =  trim(filter_input(INPUT_POST, 'gl_id', FILTER_SANITIZE_SPECIAL_CHARS));
                   $name =  trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                   $shname =  trim(filter_input(INPUT_POST, 'shname', FILTER_SANITIZE_SPECIAL_CHARS));
                   $about =  trim(filter_input(INPUT_POST, 'about', FILTER_SANITIZE_SPECIAL_CHARS));
                   $price =  trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_SPECIAL_CHARS));
                   $discount = trim(filter_input(INPUT_POST, 'discount', FILTER_SANITIZE_SPECIAL_CHARS));
                   $category =  trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
                   $gallery =  trim(filter_input(INPUT_POST, 'gallery', FILTER_SANITIZE_SPECIAL_CHARS));
                   $logo =  trim(filter_input(INPUT_POST, 'logo', FILTER_SANITIZE_SPECIAL_CHARS));

                   if ($shname == '') { $shname = null; }

                   $last_id = $dbh->lastInsertId();
                   $today_unixtime = strtotime((string)date('d.m.Y'));
                   $yesterday_unixtime = $today_unixtime - 86400;

                   try {
                      $res = $dbh->query("UPDATE `iwater_units_agr` SET `date_finish` = " . $yesterday_unixtime . " WHERE `id` = " . $id . " AND `date_finish` >= " . $today_unixtime);
                      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   } catch (Exception $e) {
                      echo "Подключение не удалось: " . $e->getMessage();
                   }

                   try {
                       $res=$dbh->prepare("INSERT INTO `iwater_units_agr`(`id`, `gl_id`, `name`, `shname`, `price`, `discount`, `category`, `about`, `gallery`, `date_start`, `date_finish` ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                       $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                       $res->execute(array( $id, $gl_id, $name, $shname, $price, $discount, $category, $about, $gallery, $today_unixtime, 2147483647));
                       if (StrLen($logo) > 5) {
                           scaleImage($logo, $dbh->lastInsertId(), 150);
                       }
                   } catch (Exception $e) {
                       echo "Подключение не удалось: " . $e->getMessage();
                   }

                   setActionLog("units", "Изменение", "iwater_units", "Изменён товар: " . $name);

                   return;
               }
                if (isset($_GET['app'])) {
                    //Обновить данные заказа

                    //Последний статутс для сравнения
                    $last = '';

                    $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                    try {
                        $res=$dbh->query("SELECT `client_id`, `address`, `status` FROM `iwater_orders_app` WHERE `id`='$id'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        setActionLog("order", "Редактирование", "iwater_orders_app", "Клиент:" . $r['client_id'] . " Старые данные:". $r['client_id'] . " " . $r['address'] . " " . $r['status']);
                        $last = $r['status'];
                    }

                    $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
                    $notice =  trim(filter_input(INPUT_POST, 'notice', FILTER_SANITIZE_SPECIAL_CHARS));
                    $order =  trim(filter_input(INPUT_POST, 'water_equip', FILTER_SANITIZE_SPECIAL_CHARS));
                    $status =  trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));


                    $orderFilt1 = array(" ", "id", ",", "-");
                    $orderFilt2 = array("", '{"id":"', '"},', '","count":"');
                    $order = "[" . str_replace($orderFilt1, $orderFilt2, $order) . '"}]';

                    try {
                        $res=$dbh->prepare('UPDATE `iwater_orders_app` SET `address`=?,`notice`=?,`status`=?, `water_equip`=? WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array($address, $notice, $status, $order, $id));
                        if ($status != $last) {
                            changeStatus($id);
                        }

                        return;
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                }
                //Обновить данные заказа
                $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                try {
                    $res = $dbh->query("SELECT `client_id`,`name`, `address`,`time`,`notice`,`water_equip`,`status`,`water_total`,`equip` FROM `iwater_orders` WHERE `id`='$id' ");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    setActionLog("order", "Редактирование", "iwater_orders", "Клиент:" . $r['client_id'] . " " . $r['name'] . " Старые данные:" . $r['address'] . " " . $r['time'] . " " . $r['water_equip'] . " " . $r['status'] . " " . $r['water_total'] . " " . $r['equip']);
                }

                $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
                $time = trim(filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS));
                $notice = trim(filter_input(INPUT_POST, 'notice', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_equip = trim(filter_input(INPUT_POST, 'water_equip', FILTER_SANITIZE_SPECIAL_CHARS));
                $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
                $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
                $equip = trim(filter_input(INPUT_POST, 'equip', FILTER_SANITIZE_SPECIAL_CHARS));
                $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                $water_total = $water_equip;

                $water_equip = str_replace(array(" ", ",", "id"), array("", "-",""), $water_equip);

                $water_filt = explode("-", $water_equip);
                $water = array();

                for ($i = 0; $i <= count($water_filt) - 1; $i = $i + 2) {
                    $water[intval($water_filt[$i])] = intval($water_filt[$i + 1]);
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `name`=?,`address`=?,`time`=?,`notice`=?, `tank_empty_now`=?,`status`=?,`water_total`=?,`equip`=? WHERE `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($name, $address, $time, $notice, $tank_empty_now, $status, $water_total, $equip, $id));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
            }
            if ($_POST['oper'] == 'del') {
                if (isset($_GET['edit_cat'])) {
                    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
                    $usersess = $res->fetch();
                    $company = $usersess['company_id'];

                    $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));

                    try {
                        $res = $dbh->prepare("DELETE FROM `iwater_category` WHERE `category`=? AND `company_id` = ?");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array( $id, $company ));

                        setActionLog("category", "Удаление", "iwater_category", "Категория " . $id . " удалена");

                        return;
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                }
                if (isset($_GET['units'])) {
                    $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));

                    $yesterday_unixtime = strtotime((string)date('d.m.Y')) - 86400;

                    try {
                        $res = $dbh->prepare('DELETE FROM `iwater_units` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                     try {
                        $res = $dbh->prepare('UPDATE `iwater_units_agr` SET `date_finish` = ? WHERE `id` = ? AND `date_finish` > ?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array($yesterday_unixtime, $id, $yesterday_unixtime));
                     } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                     }

                    unlink('../iwater_api/nusoap/images/product/hdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');
                    unlink('../iwater_api/nusoap/images/product/xhdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');
                    unlink('../iwater_api/nusoap/images/product/xxhdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');
                    unlink('../iwater_api/nusoap/images/product/xxxhdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');

                    setActionLog("units", "Удаление", "iwater_units", "Товар: №" . $id);
                    return;
                }

                if (isset($_GET['app'])) {
                    try {
                        $res=$dbh->prepare('SELECT * FROM `iwater_orders_app` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        setActionLog("order_app", "Удаление", "iwater_orders_app", "Клиент:".$r['client_id']." Старые данные:". $r['address'] ." ".$r['water_equip']." ".$r['status']);
                    }

                    try {
                        $res=$dbh->prepare('DELETE FROM `iwater_orders_app` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));

                        return;
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
                }


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

                setActionLog("order", "Удаление", "iwater_orders", "Заказ: №" . $order['id'] . " Клиент: " . $order['client_id'] . " Дата: " . gmdate("Y-m-d", $order['date']) . " Время: " . $order['time']);
            }
        }
        if (isset($_POST['add_list'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];
            $user_id = $usersess['id'];

            //Добавить путевой лист
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = explode('/', $date);
            $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

            try {
                $res = $dbh->prepare("SELECT count(`id`) as count, `date`, `file` FROM `iwater_lists` WHERE `date` = '$date' AND `company_id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $exist = $r['count'];
            }

            $list_today = array(); // Тут будем хранить путевые, которые уже есть в базе, чтобы суммировать нужное
            $orders_not_list = array(); // Массив заказов без путевых, на выбранную дату
            $driver_list = array(); // Хранилище имён водителей
            $driver_list_all = array(); // Хранилище имён всех водителей с заказами

            /**
             * Получить id путевого листа без водителя, чтобы не плодить путевые листы без водителей
            */
            try {
               $res = $dbh->query("SELECT `list`, `driver` FROM `iwater_orders` WHERE `date` = '$date' AND `list` IS NOT NULL GROUP BY `driver`");
               $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
               echo 'Подключение не удалось: ' . $e->getMessage();
            }

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
               array_push($list_today, array($r['list'], $r['driver']));
            }

             /**
              * Собираем массив заказов, у которых нету листа и группируем их по водителям(ключ)
             */
             try {
                 $res = $dbh->prepare("SELECT o.id, u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver = u.id) WHERE `date` = ? AND o.company_id = ? AND o.list IS NULL");
                 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                 $res->execute(array($date, $company));
             } catch (Exception $e) {
                 echo 'Подключение не удалось: ' . $e->getMessage();
             }

             while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                 $orders_not_list[$r['driver_id']][] = $r['id'];
                 $driver_list[$r['driver_id']] = $r['driver_n'];
             }

             if (count($list_today) > 0) {
                /**
                 * Обновление номера путевого листа
                */
                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list` = ?, `map_num` = ? WHERE `id` = ?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                foreach ($list_today as $key => $value) {
                   if (array_key_exists($value[1], $orders_not_list)) {
                      foreach ($orders_not_list[$value[1]] as $key_m => $value_m) {
                         $res->execute(array($value[0], 2, $value_m));
                      }
                      unset($orders_not_list[$value[1]]);
                   }
                }
             }

             try {
                $create_list = $dbh->prepare("INSERT INTO `iwater_lists` (`date`, `file`, `user_id`, `create_date`, `map_num`, `driver_id`, `company_id`) VALUES(?, ?, ?, ?, ?, ?, ?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
             }

             try {
                $set_list = $dbh->prepare("UPDATE `iwater_orders` SET `list` = ? WHERE `id` = ?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
             }

             /**
              * Общий список водителей, для отображения на экране
             */
             try {
                $driver_l = $dbh->query("SELECT DISTINCT u.name, u.id FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver = u.id) WHERE `date` = '$date' AND u.company_id = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
             }

             while ($d = $driver_l->fetch(PDO::FETCH_ASSOC)) {
                $driver_list_all[$d['id']] = $d['name'];
             }

             /**
              * Тут висят поля которые нужны записать в таблицу путевых, но не нужно считать
             */
             $file = "";
             $map_num = "";

             foreach ($orders_not_list as $key => $value) {
                if ($key == 0) {
                   $file = date('j.m.Y', $date) . '(' . $company .')' . '.xlsx';
                } else {
                   $file = date('j.m.Y', $date) . '(' . $company .')(driver)' . $driver_list[$key] . '.xlsx';
                }

                $map_num = $date . "?driver_id=" . $key;

                $create_list->execute(array($date, $file, $user_id, time(), $map_num, $key, $company));
                $list_id = $dbh->lastInsertId();

                foreach ($value as $order) {
                   $set_list->execute(array($list_id, $order));
                }
             } ?>
                <form method="post" id="lists" action="/iwaterTest/lists">
                   <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
                    <input name="date" type="hidden" value=<?php echo $date ?>>
                    <input name="list_id" type="hidden" value=<?php echo $list_id ?>>
                    <input name="count" type="hidden" value=<?php echo count($driver_list_all) ?>>
                    <?php $counter = 0; //Используется при создании формы, чтобы потом можно было легче разобрать
                    foreach ($driver_list_all as $key => $value) { ?>
                        <input name="<?php echo "driver_id_" . $counter ?>" type="hidden"
                               value="<?php echo $key ?>">
                        <input name="<?php echo "driver_name_" . $counter ?>" type="hidden"
                               value="<?php echo $value ?>">
                    <?php
                           $counter++;
                        }
                    ?>
                </form>
                <script type="text/javascript">
                   document.getElementById('lists').submit();
                </script>
                <?php
        }
        if (isset($_POST['client_num_s'])) {
            //Поиск адреса и названия клиента по id
            $client_num = trim(filter_input(INPUT_POST, 'client_num_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = $address;
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id = '$client_num' AND a.address  LIKE '%$address%' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='utf8'?>";
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
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }
        if (isset($_POST['name_s'])) {
            //Поиск id и адреса по имени клиента
            $name = trim(filter_input(INPUT_POST, 'name_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = html_entity_decode($name);
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.name = '$name' AND a.address LIKE '%$address%' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='utf8'?>";
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
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }
        if (isset($_POST['address_s'])) {
            //Поиск имени и id клиента по адресу
            $address = trim(filter_input(INPUT_POST, 'address_s', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE a.address = '$address' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='utf8'?>";
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
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }
        if (isset($_POST['list_p'])) {
            //Координаты заказов

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $water_array = array();

            try {
                $res = $dbh->query("SELECT * FROM `iwater_units_agr` WHERE `shname` IS NOT NULL");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                array_push($water_array, $r['id']);
            }

            $date = trim(filter_input(INPUT_POST, 'list_p', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));

            $trash = explode('?driver_id=', $date);

            if ($trash[1] !== 'undefined') {
               $date = $trash[0];
               $driver_id = $trash[1];
            }

            $extraSQL =  $date;
            if ($driver_id != "") {
                $extraSQL .= " AND `driver` =" . $driver_id;
            }
            if (isset($_POST['exception_driver'])) {
                $exc = $_POST['exception_driver'];
                for ($i=0;$i<count($exc);$i++) {
                    $extraSQL.=" AND `driver` !=" . $exc[$i];
                }
            }

            if (isset($_POST['exception_list'])) {
                $exc = $_POST['exception_list'];
                // $extraSQL .= " AND `list` IN (" . implode(",", $exc) . ")";
                for ($i=0;$i<count($exc);$i++) {
                    $extraSQL.=" AND `list` !=" . $exc[$i];
                }
            }

            try {
                $res = $dbh->query("SELECT *, o.address, a.coords AS cor_p, o.coords AS cor_new, o.id AS order_id, u.name as driver_name  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND o.date =" . $extraSQL . " AND o.status IN (0, 2) GROUP BY o.id ORDER BY o.id");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='utf8'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $total = 0;

                $water = unserialize($r['water_equip']);

                foreach ($water as $key => $value) {
                    if (in_array($key, $water_array)) {
                        $total += $value;
                    }
                }

                $s .= "<row>";
                if ($r['cor_new'] != null) {
                    $s .= "<cell id='cord' class='temp'><![CDATA[" . $r['cor_new'] . "]]></cell>";
                } else {
                    $s .= "<cell id='cord'><![CDATA[" . $r['cor_p'] . "]]></cell>";
                }
                $s .= "<cell id='time'><![CDATA[" . $r['time'] . "]]></cell>";
                $s .= "<cell id='tank_b'><![CDATA[" . $total . "]]></cell>";
                $s .= "<cell id='client_id'><![CDATA[" . $r['client_id'] . "]]></cell>";
                $s .= "<cell id='period'><![CDATA[" . $r['period'] . "]]></cell>";
                $s .= "<cell id='id'><![CDATA[" . $r['order_id'] . "]]></cell>";
                $s .= "<cell id='driver_name'><![CDATA[" . $r['driver_name'] . "]]></cell>";
                $s .= "<cell id='changed_driver'><![CDATA[" . $r['changed_driver'] . "]]></cell>";
                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=utf8");
            echo $s;
        }
        if (isset($_POST['createExcell'])) {
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_n = trim(filter_input(INPUT_POST, 'driver_n', FILTER_SANITIZE_SPECIAL_CHARS));
            $list = trim(filter_input(INPUT_POST, 'list', FILTER_SANITIZE_SPECIAL_CHARS));
            $file_name = trim(filter_input(INPUT_POST, 'file_name', FILTER_SANITIZE_SPECIAL_CHARS));
            $start = trim(filter_input(INPUT_POST, 'start', FILTER_SANITIZE_SPECIAL_CHARS));
            $finish = trim(filter_input(INPUT_POST, 'finish', FILTER_SANITIZE_SPECIAL_CHARS));
            try { 
            $res = $dbh->prepare("SELECT COUNT(DISTINCT `list`) AS count FROM `iwater_orders` WHERE `date` = ? AND `driver` = ?");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array($date,$driver_id));
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }
            $result = $res->fetch();
           $count = $result[0]['count'];

           createExcelFile($date, $driver_id, $driver_n, $list, $file_name, $start, $finish, $count);
           setActionLog("list", "Формирование", "iwater_lists", "На дату: " . date('j.m.Y', $date));
        }
        if (isset($_POST['unique'])) {
            $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS));
            $value = trim(filter_input(INPUT_POST, 'value', FILTER_SANITIZE_SPECIAL_CHARS));
            if (isset($_POST['current_id'])) {
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
                $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `company_id`, `name`, `address`, `contact`, `date`, `no_date`, `time`, `period`, `notice`, `water_equip`, `water_total`, `equip`, `dep`, `cash`, `cash_b`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `history`, `coords`, `cash_formula`, `cash_b_formula`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($order['client_id'], $order['company_id'], $order['name'], $order['address'], $order['contact'], $date, 0, $order['time'], $order['period'], $order['notice'], $order['water_equip'], $order['water_total'], $order['equip'], $order['dep'], $order['cash'], $order['cash_b'], $order['on_floor'], $order['tank_b'], $order['tank_empty_now'], "0", $order['status'], $order['reason'], $order['region'], $notice . $new_notice, $order['coords'], $order['cash_formula'], $order['cash_b_formula']));
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
            if ($response == "null") {
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
        if (isset($_POST['statuses_order_list'])) {
            $ids = trim(filter_input(INPUT_POST, 'statuses_order_list', FILTER_SANITIZE_SPECIAL_CHARS));
            $ids = json_decode($ids);
            $extraSQL = " WHERE ";
            foreach ($ids as $key => $value) {
                if ($key > 0) { $extraSQL .= " AND "; }
                $extraSQL .= " `id`=" . $value;

                setActionLog("orders", "Изменение", "iwater_orders", "Изменён статус заказа №" . $value);
            }
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `status`=?" . $extraSQL);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($_POST['status']));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
        }
        if (isset($_POST['statuses_app_list'])) {
            $extraSQL = " WHERE ";
            for ($i = 0; $i < count($_POST['statuses_app_list']); $i++) {
                if ($i > 0) {
                    $extraSQL .= " OR ";
                }
                $extraSQL .= " `id`=" . $_POST['get_status_selected'][$i];
            }
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders_app` SET `status`=?" . $extraSQL);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($_POST['status']));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            setActionLog("orders", "Изменение", "iwater_orders", "Изменён статус необработанного заказа №" . $_POST['get_status_selected'][$i]);
        }
        if (isset($_POST['order_excel_file'])) {
            $from = trim(filter_input(INPUT_POST, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
            $to = trim(filter_input(INPUT_POST, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
            $order_excel_file = trim(filter_input(INPUT_POST, 'order_excel_file', FILTER_SANITIZE_SPECIAL_CHARS));
            createOrderExcelFile($from, $to, $order_excel_file);
            setActionLog("order", "Формирование Excel файла", "iwater_order", "На дату: " . $from . " " . $to);
        }
        if (isset($_POST['order_app_excel_file'])) {
            $from = trim(filter_input(INPUT_POST, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
            $to = trim(filter_input(INPUT_POST, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
            $order_excel_file = trim(filter_input(INPUT_POST, 'order_app_excel_file', FILTER_SANITIZE_SPECIAL_CHARS));
            createOrderAppExcelFile($from, $to, $order_excel_file);
            setActionLog("order", "Формирование Excel файла", "iwater_order_app", "На дату: " . $from . " " . $to);
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
        if (isset($_POST['create_no_man_list'])) {
           $order = trim(filter_input(INPUT_POST, 'create_no_man_list', FILTER_SANITIZE_SPECIAL_CHARS));
           $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));

           $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
           $usersess = $res->fetch();
           $creater_list = $usersess['id'];
           $company_id = $usersess['company_id'];

           try {
              $res = $dbh->query("SELECT MAX(`list`) AS last_list FROM `iwater_orders`");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $last_id_list = $res->fetch(PDO::FETCH_ASSOC);
           $last_id_list = $last_id_list['last_list'] + 1;

           $file_name = date('j.m.Y', $date) . '(0007)' . '.xlsx';

           $order = explode(',', $order);

           try {
              $res = $dbh->prepare("UPDATE `iwater_orders` SET `list` = ?, `driver` = 0 WHERE `id` = ?");
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              foreach ($order as $key => $value) {
                 $res->execute(array($last_id_list, $value));
              }
           } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
           }

           try {
             $res = $dbh->prepare("INSERT INTO `iwater_lists` (`date`, `file`,`user_id`,`create_date`, `map_num`, `driver_id`, `company_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             $res->execute(array($date, $file_name, $creater_list, time(), $date, '80', $company_id));
          } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

           setActionLog("map", "Добавить путевой лист", "iwater_list", "Добавлен новый путевой лист без водителя №" . $dbh->lastInsertId());
           echo $last_id_list;
        }
        if (isset($_POST['update_list_driver'])) {
           $list = trim(filter_input(INPUT_POST, 'update_list_driver', FILTER_SANITIZE_SPECIAL_CHARS));
           $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
           $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
           $number = trim(filter_input(INPUT_POST, 'number', FILTER_SANITIZE_SPECIAL_CHARS));

           try {
             $res = $dbh->query("SELECT `name`, `company_id` FROM `iwater_users` WHERE `id` = '$driver'");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          $file_name = $res->fetch(PDO::FETCH_ASSOC);
          $file_name = date('j.m.Y', $date) . '(' . $file_name['company_id'] . ')(driver)' . $file_name['name'] . $number . '.xlsx';

           try {
            $swap = $dbh->query("SELECT * FROM `iwater_lists` WHERE `driver_id` = '$driver'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }
           try{
            $old = $dbh->query("SELECT * FROM `iwater_lists` WHERE `id` = '$list'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
           }

           $old_file = $old->fetch(PDO::FETCH_ASSOC);
           
          while ($s = $swap->fetch(PDO::FETCH_ASSOC)) {
            if ($file_name == $s['file']){
          try {
            $res = $dbh->prepare("UPDATE `iwater_lists` SET `file` = ? WHERE `id` = ?");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array($old_file['file'], $s['id']));
          } catch (Exception $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
          }
            }
          }

           try {
             $res = $dbh->prepare("UPDATE `iwater_orders` SET `driver` = ? WHERE `list` = ?");
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             $res->execute(array($driver, $list));
           } catch (Exception $e) {
             echo 'Подключение не удалось: ' . $e->getMessage();
          }

          try {
            $res = $dbh->prepare("UPDATE `iwater_lists` SET `driver_id` = ?, `file` = ?, `map_num` = ? WHERE `id` = ?");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array($driver, $file_name, $list . '?driver_id=' . $driver, $list));
          } catch (Exception $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
         }

          setActionLog("map", "Присвоен путевой лист водителю", "iwater_list", "Путевой лист №" . $list . " водителю " . $file_name['name']);
          echo $list;
        }
        if (isset($_POST['mass'])) {
            foreach ($_POST['mass'] as $k=>$m) {
                if (!empty($m)) {
                    $mass[$k] = $m;
                }
            }
            print '<pre>';
            print_r($mass);
        }
        if (isset($_POST['add_route_to_DB'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $data = $_POST['data'];
            try {
                $res=$dbh->prepare("UPDATE `iwater_orders` SET `number_visit`=? WHERE `id`=?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                for ($i=1,$j=1;$i<count($data);$i++) {
                    if (isset($data[$i]) || $data[$i]!="") {
                        if (isset($data[$i][6])) {
                            $res->execute(array($j, $data[$i][6]));
                            $j++;
                        }
                    }
                }
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            $map = trim(filter_input(INPUT_POST, 'map', FILTER_SANITIZE_SPECIAL_CHARS));
            $map = explode("/", $map);
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
            if (isset($_POST['cash'])) {
                $formula = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
            } else {
                $formula = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
            }
            $Cal = new Field_calculate();

            $total = $Cal->calculate($formula);
            echo $total;
            die();
        }
        if (isset($_POST['settings'])) {
            $e_mail = trim(filter_input(INPUT_POST, 'e_mail', FILTER_SANITIZE_SPECIAL_CHARS));
            $arr_mail = explode(",", $e_mail);
            $arr_mail = json_encode($arr_mail);

            try {
                $res = $dbh->prepare("UPDATE `iwater_settings` SET `data`='".$arr_mail."' WHERE `name`='email_to_smtp'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage() . '</br>';
            }
            setActionLog("settings", "Изменение e-mail", "iwater_settings", "Изменение настроек е-майла".$arr_mail);
            header('Location: /iwaterTest/admin/settings/');
        }

            if(isset($_POST['order_map']))  {
      $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,"http://yourwater.ru/iwaterTest/redirect.php");
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, 
         http_build_query(array('order_map' => "")),
         http_build_query(array('address' => $address))); 

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $server_output = curl_exec($ch);
      curl_close ($ch);
      echo $server_output;
    }

    }


    if ($_GET) {
            if (isset($_GET['delete_user'])) { // Удаление пользователя
                $id = trim(filter_input(INPUT_GET, 'delete_user', FILTER_SANITIZE_SPECIAL_CHARS));

            // Сверяем с номером компании, чтобы избежать "хулиганства"
                try {
                    $res = $dbh->query("SELECT * FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (u.company_id = c.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

                $data = $res->fetch(PDO::FETCH_ASSOC);
                $company = $data['company_id'];

                try {
                    $role = $dbh->query("SELECT `role` FROM `iwater_users` WHERE `id` = " . $id);
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }

            // Удаление аккаунта водителя "iWater Logistic"
                $role_id = $role->fetch();
                if ($role_id['role'] == 3) {
                    try {
                        $delete_driver_acc = $dbh->query("DELETE FROM `iwater_driver` WHERE `id` = '$id'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo 'Подключение не удалось: ' . $e->getMessage();
                    }

                }

                try {
                    $res = $dbh->prepare("DELETE FROM `iwater_users` WHERE `id` = ? AND `company_id` = ?");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($id, $company));
                } catch (Exception $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                setActionLog("user", "Удаление пользователя", "iwater_users", "Клиент c id: " . $id . " удалён ");

                header('Location: /iwaterTest/admin/list_users/');
            }
         if (isset($_GET['period_list'])) {
            $res = $dbh->query("SELECT `period` FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (c.id = u.company_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $period_array = array();
            $r = $res->fetch(PDO::FETCH_ASSOC);
            $resurse = json_decode($r['period']);

            foreach ($resurse as $value) {
               array_push($period_array, $value->unit);
            }

            echo json_encode($period_array);
         }
         if (isset($_GET['company_id'])) {
             $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
             $usersess = $res->fetch();
             $company = $usersess['company_id'];

             $today_unixtime = strtotime((string)date('d.m.Y'));

             $unit = $dbh->query("SELECT u_a.id, u_a.name, u_a.price FROM `iwater_units_agr` AS u_a LEFT JOIN `iwater_category` AS c ON (c.category_id = u_a.category) JOIN `iwater_units` AS u ON (u_a.id = u.id) WHERE date_finish > " . $today_unixtime . " AND c.company_id = '$company' AND u_a.name > '' ORDER BY CASE WHEN `priority` = 0 THEN 9999 ELSE `priority` END, u_a.name");
             $units = $unit->fetchAll(PDO::FETCH_ASSOC);

             print json_encode($units);
         }
        if (isset($_GET['ban'])) {
            //Бан пользователя
            $id = trim(filter_input(INPUT_GET, 'ban', FILTER_SANITIZE_SPECIAL_CHARS));

            // Сверяем с номером компании, чтобы избежать "хулиганства"
            try {
                $res = $dbh->query("SELECT * FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (u.company_id = c.id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
              echo 'Подключение не удалось: ' . $e->getMessage();
            }

            $data = $res->fetch(PDO::FETCH_ASSOC);
            $company = $data['company_id'];

            try {
                $res=$dbh->prepare("UPDATE `iwater_users` SET `ban`='1' WHERE `id`=? AND `company_id` = ?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($id, $company));
            } catch (Exception $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
            setActionLog("user", "Блокировка пользователя", "iwater_users", "Клиент c id: ".$id." забанен ");


            header('Location: /iwaterTest/admin/list_users/');
        }
        if (isset($_GET['logout'])) {
            session_destroy();
            $_SESSION['fggafdfc'] = '111';
            header('Location: /iwaterTest');
        }
    }


function setActionLog($operation, $action, $table, $data)
{
    $dbh=connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    if (isset($_SESSION['fggafdfc'])) {
        $session=array();
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
    try {
        $sth = $dbh->prepare("INSERT INTO `iwater_logs`(`user_id`, `operation`, `action`, `table`, `data`, `time`) VALUES (?, ?, ?, ?, ?, ?)");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sth->execute(array($session['id'], $operation, $action, $table, $data, mktime()));
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage().'</br>';
    }
    return $session['id'];
}

function createExcelFile($date, $driver = "", $driver_name = "", $list = "", $file_name = "", $start = "", $finish = "", $count = "")
{ //водителю
    $dbh = connect_db();
    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    $extraName = "";
    $extraSQL = "";
    $extraLimit = "";

    if ($start != "" && $finish != "") {
        $extraLimit .= " LIMIT " . $start . ", " . ($finish - $start);

        if ($start == 0) {
           $extraName .= "(1)";
        } else {
           $extraName .= "(2)";
        }
    }

    /**
     * ИМЯ ДОКУМЕНТА
    */
    if ($driver != "") {
      $extraName = '(driver)' . $driver_name;
      if ($file_name != "") {
         $area = explode($driver_name, $file_name);

         if (substr($area[1], 0, 1) == "2") {
           $driver_name .= '2';
        } else if ($count != 1) {
           $driver_name .= '1';
        }
      }
   } else {
      $driver_name = 'Общий';
   }

    $file = date('j.m.Y', $date) . '(' . $company . ')' . $extraName . '.xlsx';

    if ($file_name != "") {
      $file = $file_name;
   }

   /**
    * ФОРМИРОВАНИЕ УСЛОВИЙ ДЛЯ ВЫГРУЗКИ
   */

   if ($driver != "") {
      $extraSQL = " AND u.id = " . $driver;
   }

   if ($date != "") {
      $extraSQL .= " AND o.date = " . $date;
   }

   if ($list != "") {
     $extraSQL = " AND o.list = " . $list;
  }

  // if ($driver_name != 'Общий') {
    $extraSQL .= ' ORDER BY o.id';
  // } else {
  //   $extraSQL .= ' ORDER BY o.id DESC';
  // }

    $filename = '/iwaterTest/files/' . $file;
    // if (@fopen($_SERVER['DOCUMENT_ROOT'] . $filename, "r")) {
    //     echo $file;
    //     return 1;
    // }

    $objPHPExcel = PHPExcel_IOFactory::load("files/order_blank2.xlsx");
    $objPHPExcel->getActiveSheet()->setCellValue('A3', substr($file, 0, 10));
    $dbh = connect_db();

    try {
        $res = $dbh->query("SELECT *, o.address AS o_address, o.contact AS o_contact, o.name as client_name, u.name AS driver_n, c.type AS type, c.last_date AS count_orders FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON (driver = u.id) LEFT JOIN `iwater_clients` AS c ON o.client_id = c.client_id  WHERE o.status IN (0, 2) " . $extraSQL . " " . $extraLimit);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    /** ПОСТРОЕНИЕ ШАПКИ ДОКУМЕНТА
    */

    //Вторая
    if ($driver_name != "") {
        $objPHPExcel->getActiveSheet()->setCellValue("E2", $driver_name);
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue("E2", 'Общий');
    }

    //Третья строка
    $objPHPExcel->getActiveSheet()->setCellValue("I3", ''); //наличка к сдаче

    //Четвёртая строка
    $objPHPExcel->getActiveSheet()->setCellValue("G4", 'Общая загрузка:');

    //Пятая строка(последняя, дальше уже вывод самих данных)
    $start_loop = 8;
    $water_array = array(); //массив с id воды
    $today_unixtime = strtotime((string)date('d.m.Y'));

    // Выгружаем список оборудования, чтобы потом подставлять в excel'e
    try {
       $tool = $dbh->query("SELECT u.id, u.name FROM `iwater_units_agr` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE `company_id` = '$company' AND `priority` != 100 AND `date_finish` > '$today_unixtime' ORDER BY u.id");
       $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
       echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $products_array = array();
    while ($t = $tool->fetch(PDO::FETCH_ASSOC)) {
      array_push($products_array, $t);
    }

    //Выгрузка воды
    try {
        $wat = $dbh->query("SELECT u.id, `shname` FROM `iwater_units_agr` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE `company_id` = '$company' AND `shname` IS NOT NULL AND `date_finish` > '$today_unixtime' ORDER BY u.id");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $columns = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ'); //Алфавит

    while ($w = $wat->fetch(PDO::FETCH_ASSOC)) {
          $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "4", '=SUM(' . $columns[$start_loop] . '6:' . $columns[$start_loop] . '1000)');
          $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "5", $w['shname']);
          array_push($water_array, $w['id']);
          $start_loop++;
    }

    $objPHPExcel->getActiveSheet()->getStyle($columns[8] . "6:" . $columns[$start_loop] . "200")->getFont()->setSize(14);
    $objPHPExcel->getActiveSheet()->getStyle($columns[8] . "6:" . $columns[$start_loop] . "200")->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->mergeCells("I3:" . $columns[$start_loop] . "3");
    $objPHPExcel->getActiveSheet()->setCellValue("I3", 'Бутыли в заказе');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "4", '=SUM(' . $columns[$start_loop] . '6:' . $columns[$start_loop] . '1000)');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "5", 'Всего');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . '5', 'Взачёт или залог');
    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop] . '5')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . '5', ''); //наличка
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . '4', '=SUM(' . $columns[$start_loop + 2] . "6:" . $columns[$start_loop + 2] . '399)');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . '3', ''); //наличка к сдаче
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . '1', 'Доп. Оборудование:');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . '3', '=' . $columns[$start_loop + 2] . '4-' . $columns[$start_loop + 4] . '4');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . '5', 'БЕЗНАЛ');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . '4', '=SUM(' . $columns[$start_loop + 3] . "6:" . $columns[$start_loop + 3] . '399)');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 4] . '5', 'Подъём на этаж');
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 4] . '4', '=SUM(' . $columns[$start_loop + 4] . "6:" . $columns[$start_loop + 4] . '399)');

    $objPHPExcel->getActiveSheet()->getStyle("H4:" . $columns[$start_loop + 4] . "4")->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => array('rgb' => '000000')
            )
        )
    )
);

    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 3] . "3:" . $columns[$start_loop + 4] . "3")->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => array('rgb' => '000000')
            )
        )
    )
);

    $objPHPExcel->getActiveSheet()->getStyle("A6:" . $columns[$start_loop + 7] . "150")->applyFromArray(
      array(
        'borders' => array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
          )
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 4] . '5')->getAlignment()->setWrapText(true);

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 5] . '5', 'Оборудование');
    $objPHPExcel->getActiveSheet()->getColumnDimension($columns[$start_loop + 5])->setWidth(35);

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 6] . '5', 'Примечание');
    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 6] . '5')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension($columns[$start_loop + 6])->setWidth(45);

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 7] . '5', 'Порядок заезда');
    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 7] . '5')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension($columns[$start_loop + 7])->setWidth(15);

    /** ШАПКА СГЕНЕРИРОВАНА
    */

    $x = 6;
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
            'color' => array('rgb' => '595959')
        )
    );
    $style_Yellow = array(
        'fill' => array(
            'color' => array('rgb' => 'FFFF00')
        )
    );

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . '3', ''); //наличка к сдаче

    for ($colorIndex = 0; $colorIndex <= $start_loop + 7; $colorIndex++) {
        $objPHPExcel->getActiveSheet()->getStyle($columns[$colorIndex] . '5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('595959');
    } //Покраска пятой строки

    $objPHPExcel->getActiveSheet()->getStyle("A5:" . $columns[$start_loop + 5] . "200")->applyFromArray(array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if ($r['history'] != "") {
            $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $x . ':U' . $x)
                ->applyFromArray($style_Yellow);
        }
        if ($r['history'] != "") {
            $objPHPExcel->getActiveSheet()
                ->getStyle('D' . $x . ':G' . $x)
                ->applyFromArray($style_Yellow);
        }
        $newMapNum++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $x, $newMapNum);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $x, $r['client_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $x, $r['client_id']);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

        $styleArray_forNew = array(
            'fill' => array(
                'color' => array('rgb' => '000000')
            ),
            'font'  => array(
                'color' => array('rgb' => 'FFFFFF')
            ));

        if ($r['count_orders'] == null && $r['client_id'] > 0) { $objPHPExcel->getActiveSheet()->getStyle('C' . $x)->applyFromArray($styleArray_forNew);  }

        $objPHPExcel->getActiveSheet()->setCellValue('D' . $x, $r['o_address']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $x, $r['o_contact']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $x, $r['time']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $x, $r['driver_n']);

        $order_array = unserialize($r['water_equip']);

       $tool_string = ''; // проверка
       $tools_array = array(); //массив с id оборудования

       for ($l = 8; $l < $start_loop; $l++) {
           foreach ($order_array as $key => $value) {
               if ($water_array[$l - 8] == $key) {
                   $objPHPExcel->getActiveSheet()->setCellValue($columns[$l] . $x, $value);
               } else if (!in_array($key, $water_array) && $l == 8) {
                   array_push($tools_array, array('key' => $key, 'count' => $value));
               }
           }
       }

       for ($i = 6; $i <= $x; $i++) {
           $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . $i, '=SUM(' . $columns[8] . $i . ':' . $columns[$start_loop - 1] . $i . ')');
       }

       foreach ($products_array as $t) {
           foreach ($tools_array as $key => $value) {
               if ($value['key'] == $t['id']) {
                   $tool_string .= $value['count'] . ' - ' . $t['name'] . '
 ';
               }
           }
       }

       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . "1", "Доп. Оборудование:");
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . $x, $r['dep']);
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . $x, $r['cash']);
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . $x, $r['cash_b']);
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 4] . $x, $r['on_floor']);
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 5] . $x, $tool_string);
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 6] . $x, $r['notice']);
       $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 7] . $x, $r['number_visit']);

       // Выравнивание
       for ($ch = 5; $ch < 100; $ch++) {
           $objPHPExcel->getActiveSheet()->getRowDimension($ch)->setRowHeight(70);
       }

       /** Далее идут вычисляемые поля
       */
       $objPHPExcel->getActiveSheet()->setCellValue('H4', '=SUM(H6:H200)');

       $styleArray = array(
           'font'  => array(
               'color' => array('rgb' => 'FF0000')
           ));
       if ($r['type'] == 1) {
           $objPHPExcel->getActiveSheet()
               ->getStyle('A' . $x . ':U' . $x)
               ->applyFromArray($styleArray);
       }
       $x++;
   }

   $objPHPExcel->setActiveSheetIndex(0);
   $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
   $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
   $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
   $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
   $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(50);
   $objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:' . $columns[$start_loop + 7] . ($x - 1));

   $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
   $objWriter->setPreCalculateFormulas(false);
   $objWriter->save('files/' . $file);

   echo $file;
}

function createOrderAppExcelFile($from, $to, $all)
{
    $objPHPExcel = PHPExcel_IOFactory::load("files/order_app_blank.xlsx");
    $dbh=connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    $file = 'order_from_';

    $from=explode('/', $from);
    $file.=strval($from[0]). strval($from[1]). strval($from[2]);
    $from=mktime(0, 0, 0, $from[1], $from[0], $from[2]);

    $to = explode('/', $to);
    $file .= '_to_' . strval($to[0]) . strval($to[1]) . strval($to[2]) . '(' . $company . ')' . '.xlsx';
    $to = mktime(23, 59, 59, $to[1], $to[0], $to[2]);
    $extraSQL = "";
    if ($all != "all") {
        $extraSQL = " AND o.date >= ? AND o.date <= ?";
    }

    try {
        $res=$dbh->prepare("SELECT *, o.address, o.client_id, c.name, c.phone FROM `iwater_orders_app` AS o JOIN `iwater_clients_app` AS c ON o.client_id = c.id WHERE o.company_id = ?".$extraSQL." ORDER BY o.date, o.id");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $res->execute(array($company, $from, $to));
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $x=4;
    $newMapNum = 0;

    $waterFilt1 = array('{"id":"', '","count":"', '"}', '[', ']');
    $waterFilt2 = array('id ', ' - ', '', '', '');

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $notice = ($r['notice'] != '' ? $r['notice'] : 'пусто');

        $date = date('j.m.Y', $r['date']);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $date);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$x, $r['name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$x, $r['client_id']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$x, $r['address']);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$x, $r['phone']);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$x, $r['period']);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$x, str_replace($waterFilt1, $waterFilt2, $r['water_equip']));
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$x, $notice);
        $x++;
    }


    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    if ($all=="all") {
        $objWriter->save('files/(' . $company . ')all_app_order.xlsx');
    } else {
        $objWriter->save('files/' . $file);
    }

    return $file;
}

function createOrderExcelFile($from, $to, $all)
{
    $objPHPExcel = PHPExcel_IOFactory::load("files/order_blank.xlsx");
    $dbh=connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc']. "' ORDER BY `number_visit`");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    $file = 'order_from_';

    $from=explode('/', $from);
    $file.=strval($from[0]). strval($from[1]). strval($from[2]);
    $from=mktime(0, 0, 0, $from[1], $from[0], $from[2]);

    $to=explode('/', $to);
    $file.='_to_'.strval($to[0]). strval($to[1]). strval($to[2]) . '(' . $company . ')' . '.xlsx';
    $to=mktime(23, 59, 59, $to[1], $to[0], $to[2]);
    $extraSQL ="";
    if ($all != "all") {
        $extraSQL = " AND o.date >= ? AND o.date <= ?";
    }

    try {
        $res=$dbh->prepare("SELECT *,o.name as client_name ,u.name AS driver_n FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON(driver=u.id) WHERE o.company_id = ?".$extraSQL." ORDER BY o.date, o.id");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $res->execute(array($company, $from, $to));
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $x=4;
    $newMapNum = 0;

    $waterFilt1 = array('{"', '}', '":', ',"');
    $waterFilt2 = array('id ', '', ' - ', ',id ');



    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $date = date('j.m.Y', $r['date']);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $date);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$x, ($r['client_name']));
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$x, $r['client_id']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$x, $r['address']);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$x, ($r['contact']));
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$x, ($r['time']));
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$x, $r['driver_n']);
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$x, $r['tank_empty_now']);
        $objPHPExcel->getActiveSheet()->setCellValue('I'.$x, str_replace($waterFilt1, $waterFilt2, json_encode(unserialize($r['water_equip']))));
        $objPHPExcel->getActiveSheet()->setCellValue('J'.$x, $r['water_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('K'.$x, $r['dep']);
        $objPHPExcel->getActiveSheet()->setCellValue('L'.$x, $r['cash']);
        $objPHPExcel->getActiveSheet()->setCellValue('M'.$x, $r['cash_b']);
        $objPHPExcel->getActiveSheet()->setCellValue('N'.$x, $r['on_floor']);
        $objPHPExcel->getActiveSheet()->setCellValue('O'.$x, $r['equip']);
        $objPHPExcel->getActiveSheet()->setCellValue('P'.$x, $r['notice']);
        $x++;
    }


    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    if ($all=="all") {
        $objWriter->save('files/all_order.xlsx');
    } else {
        $objWriter->save('files/' . $file);
    }

    return $file;
}

function get_contact_by_client_id($client_id, $address = "")
{
    $dbh=connect_db();
    $contact = "";

    try {
        $res=$dbh->prepare("SELECT c.name, a.contact FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id =? AND a.address  LIKE '%$address%' LIMIT 1");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $res->execute(array($client_id));
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $contact .= $r['contact'];
    }
    return $contact;
}

function generateSearchStringFromObj($filters)
{
    $where = '';

    // Генерация условий группы фильтров
    if (count($filters)) {
        foreach ($filters->rules as $index => $rule) {
            $rule->data = addslashes($rule->data);

            $where .= "".preg_replace('/-|\'|\"/', '', $rule->field)."";
            switch ($rule->op) { // В будущем будет больше вариантов для всех вохможных условий jqGrid
        case 'eq': $where .= " = '".$rule->data."'"; break;
        case 'ne': $where .= " != '".$rule->data."'"; break;
        case 'bw': $where .= " LIKE '".$rule->data."%'"; break;
        case 'bn': $where .= " NOT LIKE '".$rule->data."%'"; break;
        case 'ew': $where .= " LIKE '%".$rule->data."'"; break;
        case 'en': $where .= " NOT LIKE '%".$rule->data."'"; break;
        case 'cn': $where .= " LIKE '%".$rule->data."%'"; break;
        case 'nc': $where .= " NOT LIKE '%".$rule->data."%'"; break;
        case 'nu': $where .= " IS NULL"; break;
        case 'nn': $where .= " IS NOT NULL"; break;
        case 'in': $where .= " IN ('".str_replace(",", "','", $rule->data)."')"; break;
        case 'ni': $where .= " NOT IN ('".str_replace(",", "','", $rule->data)."')"; break;
    }

            // Добавить логику соединения, если это не последние условие
            if (count($filters->rules) != ($index + 1)) {
                $where .= " ".addslashes($filters->groupOp)." ";
            }
        }
    }

    // Генерация условий подгруппы фильтров
    $isSubGroup = false;
    if (isset($filters->groups)) {
        foreach ($filters->groups as $groupFilters) {
            $groupWhere = self::generateSearchStringFromObj($groupFilters);
            // Если подгруппа фильтров содержит условия, то добавить их
            if ($groupWhere) {
                // Добавить логику соединения, если условия подгруппы фильтров добавляются после условий фильтров этой группы
                // или после условий других подгрупп фильтров
                if (count($filters->rules) or $isSubGroup) {
                    $where .= " ".addslashes($filters->groupOp)." ";
                }
                $where .= $groupWhere;
                $isSubGroup = true; // Флаг, определяющий, что было хоть одно условие подгрупп фильтров
            }
        }
    }

    if ($where) {
        return $where;
    }

    return ''; // Условий нет
}

function notf($id, $title, $body)
{
    $registrationIds = array( $id );

    $notification = array(
            'title'      => 'IWater', //загаловок оповещения
            'body'       => $body, //тело оповещения
            'icon'       => 'ic_notifications', //логотип
            'color'	     => '#FF4ACEFF',
            'sound'      => 'stand',
            'tag'        => 'tag'
        );

    $data = array(
            'message' 	   => 'message body',
            'click_action' => "PUSH_INTENT"
        );

    $fields = array(
            'registration_ids'  => $registrationIds,
            'notification'      => $notification,
            'data'              => $data,
            'priority'          => 'normal'

        );

    $headers = array(
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json',
        );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return true;
}

function getDistance($lat1, $lon1, $lat2, $lon2) {
    $lat1 *= M_PI / 180;
    $lat2 *= M_PI / 180;
    $lon1 *= M_PI / 180;
    $lon2 *= M_PI / 180;

    $d_lon = $lon1 - $lon2;

    $slat1 = sin($lat1);
    $slat2 = sin($lat2);
    $clat1 = cos($lat1);
    $clat2 = cos($lat2);
    $sdelt = sin($d_lon);
    $cdelt = cos($d_lon);

    $y = pow($clat2 * $sdelt, 2) + pow($clat1 * $slat2 - $slat1 * $clat2 * $cdelt, 2);
    $x = $slat1 * $slat2 + $clat1 * $clat2 * $cdelt;

    return atan2(sqrt($y), $x) * 6372795;
}

function changeStatus($id)
{
    $dbh=connect_db();

    $results = array('0' => 'отправлен', '1' => 'принят', '2' => 'подтверждён', '3' => 'доставлен');
    $notf = '';
    $status = '';

    try {
        $dbh->query("SET CHARACTER SET 'utf8'");
        $res = $dbh->query("SELECT notification, o.status FROM `iwater_clients_app` AS c LEFT JOIN `iwater_orders_app` AS o ON c.id = o.client_id WHERE o.id = " . $id);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $notf =  $r['notification'];
        $status = $r['status'];
    }

    $result = notf($notf, 'IWater', "Ваш заказ был " . $results[$status] . ".");

    if ($result) {
        setActionLog("order_app", "Уведомление", "iwater_orders_app", "Уведомление по заказу №" . $id . " отправлено");

        return true;
    } else {
        return false;
    }
}

function searchUnit($id)
{
    try {
        $res = $dbh->query("SELECT * FROM `iwater_units` WHERE id = " . $id);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        return $r['name'];
    }
}

function scaleImage($name, $max)
{
    $path = $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $name . '/0.jpg';
    $image = imageCreateFromJpeg($path);
    // Размеры старой картинки
    $old_w = imageSX($image);
    $old_h = imageSY($image);

    /** Коэффициент для кратного уменьшения изображения
    */
    $k = $old_h / $old_w; //y коэффициент
    $t = 1; //x коэффициент

    if ($k > 1) {
       $t = $old_w / $old_h;
       $k = 1;
    }

    /** HDPI
    */
    $new = imageCreateTrueColor($max * $t, $max * $k);
    imageCopyResampled($new, $image, 0, 0, 0, 0, $max * $t, $max * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $name . '/hdpi.jpg', 90);
    imagedestroy($new);

    /** xHDPI
    */
    $new = imageCreateTrueColor($max * 4 / 3 * $t, $max * 4 / 3 * $k);
    imageCopyResampled($new, $image, 0, 0, 0, 0, $max * 4 / 3 * $t, $max * 4 / 3 * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $name . '/xhdpi.jpg', 90);
    imagedestroy($new);

    /** xxHDPI
    */
    $new = imageCreateTrueColor($max * 2 * $t, $max * 2 * $k);
    imageCopyResampled($new, $image, 0, 0, 0, 0, $max * 2 * $t, $max * 2 * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $name . '/xxhdpi.jpg', 90);
    imagedestroy($new);

    /** xxxHDPI
    */
    $new = imageCreateTrueColor($max * 8 / 3 * $t, $max * 8 / 3 * $k);
    imageCopyResampled($new, $image, 0, 0, 0, 0, $max * 8 / 3 * $t, $max * 8 / 3 * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, $_SERVER['DOCUMENT_ROOT'] . '/iwater_api/images/' . $name . '/xxxhdpi.jpg', 90);
    imagedestroy($new);
}

//if (isset($_GET['tester'])){
				 // $result = $dbh->query("SELECT *,u.name AS d_name, u.session AS u_sess, o.id AS o_id, o.name AS o_name, ADT.contact_search FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) inner join (select client_id,Replace(replace(contact,'-',''),'+','') AS contact_search from `iwater_orders`) as ADT on o.client_id = ADT.client_id WHERE o.company_id = '0007'AND ADT.contact_search like '%89112743809%'");
				 // echo $result;
				// print_r($result);
			  //$result = $dbh->query("SELECT *,u.name AS d_name, u.session AS u_sess, o.id AS o_id, o.name AS o_name, ADT.contact_search FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) inner join (select client_id,Replace(replace(contact,'-',''),'+','') AS contact_search from `iwater_orders`) as ADT on o.client_id = ADT.client_id " . $extraSQL);
			//  }
?>
