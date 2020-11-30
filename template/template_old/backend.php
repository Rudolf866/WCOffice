<?php


  // ini_set('display_errors', 1);
  // error_reporting(E_ALL);


  define("API_ACCESS_KEY", "AAAAGB4Wiws:APA91bFULbzx6kdNXtwGy8k1fuA6-t_HcSffLexDg7PZGz99CuUtXLUpylQ-CEXdndpk2qDmBkaR2sjHWRSx-QfjIYVdbg_88lbcPLcCK9M2QKK8X7AxN2LQtOEw2V4YkbMgd0VzHHbc");

    require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/functions.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/Classes/PHPExcel.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/Classes/PHPExcel/IOFactory.php');

    $dbh=connect_db();


    if ($_POST) {
        if (isset($_POST['add_user'])) {
            //�������� ������������
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
            $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);

            $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
            $max = 29;
            $size = StrLen($chars) - 1;
            $salt = '$2a$08$';
            while ($max--) {
                $salt .= $chars[rand(0, $size)];
            }

            if (CRYPT_BLOWFISH == 1) {
                $hash = crypt($password, $salt);
            }
            try {
                $res = $dbh->query("SELECT count(`login`) FROM `iwater_users` WHERE `login`='" . $login . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
                    echo '����������� �� �������: ' . $e->getMessage() . '</br>';
                }
            }
            setActionLog("user", "����������", "iwater_user", "�������� ������������: " . $name);
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
                    echo '����������� �� �������: ' . $e->getMessage();
                }
            }
        }
        if (isset($_POST['login_form'])) {
            //�����������

            $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS));
            $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS));
            $error = "";
            try {
                $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `login`='$login'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            if ($login !="") {
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
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                } else {
                    $error = "?err=login";
                }
            }

            header('Location: /iwaterTest' . $error);
        }
        if (isset($_POST['add_role'])) {
            //�������� ����
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            $arr = $_POST['perms'];
            $perms = json_encode($arr);
            try {
                $res = $dbh->prepare("INSERT INTO `iwater_roles`(`name`, `perms`) VALUES (?, ?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($name, $perms));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }

            setActionLog("role", "����������", "iwater_roles", "��������� ����" . $name);
            header('Location: /iwaterTest/');
        }
        if (isset($_POST['list_users'])) {
            //�������������� ������ �������������
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
                        $size = StrLen($chars) - 1;
                        $salt = '$2a$08$';
                        while ($max--) {
                            $salt .= $chars[rand(0, $size)];
                        }

                        if (CRYPT_BLOWFISH == 1) {
                            $hash = crypt($password, $salt);
                        }

                        $res->execute(array(strip_tags($_POST['names'][$z]), strip_tags($_POST['phones'][$z]), strip_tags($_POST['roles'][$z]), $hash, $salt, strip_tags($_POST['ids'][$z])));
                    } else {
                        $res->execute(array(strip_tags($_POST['names'][$z]), strip_tags($_POST['phones'][$z]), strip_tags($_POST['roles'][$z]), strip_tags($_POST['ids'][$z])));
                    }
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                $z++;
            }

            setActionLog("user", "��������������", "iwater_user", "");
            header('Location: /iwaterTest/admin/list_users/');
        }
        if (isset($_POST['add_company'])) {
            //�������� ��������
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
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $res->execute(array($id, $name, $city, $city, $contact, '[{"unit":"�����"},{"unit":"���"},{"unit":"�� �������"},{"unit":"�������"},{"unit":"�� ��������"}]', $schedule, $regions));

            setActionLog("company", "����������", "iwater_company", "���������� ��������: " . $id);
            header('Location: /iwaterTest/admin/add_company/');
        }
        if (isset($_POST['select_period'])) {
            //������� ������ ��������
            try {
                $dbh->query("SET NAMES utf8");
                $res = $dbh->query("SELECT `period`, `timing` FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (c.id = u.company_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $r = $res->fetch();

            echo '{"period":"' . addslashes($r['period']) . '","timing":"' . addslashes($r['timing']) . '"}';
        }
        if (isset($_POST['change_period'])) {
            //�������� �������
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];
            $values = trim(filter_input(INPUT_POST, 'change_period'));
            $timing = trim(filter_input(INPUT_POST, 'timing'));
            try {
                $dbh->query("SET NAMES utf8");
                $res = $dbh->prepare("UPDATE `iwater_company` SET `period` = ?, `timing` = ? WHERE `id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            echo $values;
            $res->execute(array($values, $timing));

            setActionLog("company", "���������", "iwater_company", "�������� ������� ��������");
            // header('Location: /iwaterTest/');
        }
        if (isset($_POST['add_client'])) {
            //�������� �������
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
                echo '����������� �� �������: ' . $e->getMessage();
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
                    echo '����������� �� �������: ' . $e->getMessage() . '</br>';
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
                        echo '����������� �� �������: ' . $e->getMessage() . '</br>';
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
                setActionLog("client", "����������", "iwater_clients", "���������� �������: " . $num_c);
                header('Location: /iwaterTest/admin/add_client/');
            }
        }

        if (isset($_POST['edit_client'])) {
            //������������� �������
            $id = trim(filter_input(INPUT_POST, 'id_db', FILTER_SANITIZE_SPECIAL_CHARS));
            $num_c = trim(filter_input(INPUT_POST, 'num_c', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON(c.client_id=a.client_id)  WHERE c.id='$id';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            };
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                setActionLog("client", "�������������� ��������������", "iwater_clients", "������ id: " . $r['id'] . " ������ ������: " . $r['client_id'] . " " . $r['region'] . " " . $r['address'] . " " . $r['coords'] . " " . $r['contact']);
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
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_addresses` WHERE client_id='$num_c'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($num_c));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }


            $z = count($_POST['region']);
            $i = 0;
            $flagDouble = 0;
            while ($i < $z) {
                try {
                    $res = $dbh->prepare("INSERT INTO `iwater_addresses`(`client_id`, `contact`, `region`, `address`, `coords`,`full_address`) VALUES (?, ?, ?, ?, ?,?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage() . '</br>';
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

            setActionLog("client", "�������������� ��������������", "iwater_clients", "��������� �������: ".$num_c);
            header('Location: /iwaterTest/admin/list_clients/');
        }

        if (isset($_POST['delete_client'])) {
            //�������� �������
            $id = trim(filter_input(INPUT_POST, 'delete_client', FILTER_SANITIZE_SPECIAL_CHARS));

            $user_id = setActionLog("client", "����������� � �������", "iwater_clients", "����������� � �������: " . $id);
            try {
                $res = $dbh->prepare("UPDATE `iwater_clients` SET `for_delete`=1, `time_change`=?, `user_changing`=?  WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array(mktime(), $user_id));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `status`=1 WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }
            echo 1;
        }

        if (isset($_POST['restablish_client'])) {
            // �������������� ������� �� �������
            $id = trim(filter_input(INPUT_POST, 'restablish_client', FILTER_SANITIZE_SPECIAL_CHARS));
            $user_id = setActionLog("client", "�������������� �� �������", "iwater_clients", "�������������� �� �������: " . $id);
            try {
                $res = $dbh->prepare("UPDATE `iwater_clients` SET `for_delete`=0, `time_change`=?, `user_changing`=? WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array(mktime(), $user_id));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }
            echo 1;
        }

        if (isset($_POST['destroy_client'])) {
            // ������ �������� �������
            $id = trim(filter_input(INPUT_POST, 'delete_client', FILTER_SANITIZE_SPECIAL_CHARS));
            setActionLog("client", "������ ��������", "iwater_clients", "������ ��������: " . $id);

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_clients` WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_addresses` WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }

            try {
                $res = $dbh->prepare("DELETE FROM `iwater_orders` WHERE client_id='$id'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }
            echo 1;
        }
        if (isset($_POST['list_clients'])) {
            //������ ��������

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $json = stripcslashes($_POST['list_clients']);
            $data = json_decode($json, true);
            $data[1] = iconv(mb_detect_encoding($data[1]), "CP1251", $data[1]);
            $data[2] = iconv(mb_detect_encoding($data[2]), "CP1251", $data[2]);
            $query = " WHERE for_delete = 0 AND `company_id` = '$company'";

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

            $current_page = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $offset = $lists_in_page * ($current_page - 1);

            try {
                $res = $dbh->prepare("SELECT *, c.id AS c_id FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id = a.client_id " . $query . " LIMIT " . $lists_in_page . " OFFSET " . $offset . ";");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $last_id = "";

            $s = "<?xml version='1.0' encoding='cp1251'?>";
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
                    echo '����������� �� �������: ' . $e->getMessage();
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
            header("Content-type: text/xml;charset=cp1251");
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
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $s = "<?xml version='1.0' encoding='cp1251'?>";
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
                    echo '����������� �� �������: ' . $e->getMessage();
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
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['list_lists'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            //������ ��������
            $json = stripcslashes($_POST['list_lists']);
            $data = json_decode($json, true);
            $query = " WHERE l.company_id = " . $company . " AND `file` LIKE '%(" . $company . ")%' ";
            $date = $data;
            if ($date[0] != null) {
                $date[0] = explode('.', $date[0]);
                $date[0] = mktime(0, 0, 0, $date[0][1], $date[0][0], $date[0][2]);
                //$count = count($data);
                //$half_sec_in_day = 86400 / 2;
                if ($date[1] == "") {
                    $query .= ' AND `date` > ' . $date[0] . ' ';
                } else {
                    $date[1] = explode('.', $date[1]);
                    $date[1] = mktime(0, 0, 0, $date[1][1], $date[1][0], $date[1][2]);
                    $query .= ' AND `date` > ' . $date[0] . ' AND `date` < ' . $date[1];
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
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $file = iconv("utf-8", "cp1251", $r['file']);
                $s .= "<row>";
                $s .= '<cell>' . $file . "</cell>";
                $s .= '<cell><a class="xlsx" href="/iwaterTest/files/' . $r['file'] . '">' . "�������" . "</a></cell>";
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
        if (isset($_GET['driver_control'])) {
            //������������ ������������ ���������
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
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $count = $res->fetchColumn();
            $total_pages = ceil($count / $limit);

            $out = "<?xml version='1.0' encoding='cp1251'?>";
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
            header("Content-type: text/xml;charset=cp1251");
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

            //������� ���������� �������
            try {
                $cou = $dbh->query("SELECT * FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE c.company_id = $company");
                $count = $cou->rowCount();
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            //����� ������ ������� �������� ������ ����������
            try {
                $start = ($page - 1) * $limit;
                $finish = $page * $limit;
                $res = $dbh->query("SELECT `id`, `name`, `shname`, `about`, `price`, `discount`, `gallery`, u.category, c.category AS cat_name, category_id FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE c.company_id = $company ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $total_pages = ceil($count/$limit);

            $out = "<?xml version='1.0' encoding='cp1251'?>";
            $out .= '<rows>';
            $out .= '<page>' . $page . '</page>';
            $out .= '<total>' . $total_pages . '</total>';
            $out .= '<records>' . $count . '</records>';
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $dir = (file_exists('../wsdl/images/product/hdpi/' . $r['id'] . '.jpg') ? "+" : "-");
                $out .= "<row id='" . $r['id'] . "'>";
                $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['name'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['shname'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['about'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['price'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['discount'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['gallery'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['category'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $dir . ']]></cell>';
                $out .= '</row>';
            }
            $out .= '</rows>';
            header("Content-type: text/xml;charset=cp1251");
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
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $out = "<?xml version='1.0' encoding='cp1251'?>";
            $out .= '<rows>';
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $out .= "<row id='" . $r['category'] . "'>";
                $out .= '<cell><![CDATA[' . $r['category'] . ']]></cell>';
                $out .= '<cell><![CDATA[' . $r['priority'] . ']]></cell>';
                $out .= '</row>';
            }
            $out .= '</rows>';
            header("Content-type: text/xml;charset=cp1251");
            echo $out;
        }
        if (isset($_GET['category'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $dbh->query("set names utf8");
                $res = $dbh->query("SELECT `category`, `category_id` FROM `iwater_category` WHERE `company_id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            if (isset($_POST['category'])) {
                header('content-type: application/json;charset=utf8');
                echo json_encode($res->fetchAll());
                exit();
            }
            echo json_encode('������ �� ������');
        }

        if (isset($_POST['region']) && !isset($_POST['add_order'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $dbh->query("set names windows-1251");
                $reg = $dbh->query("SELECT `regions` FROM `iwater_company` WHERE `id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $json_str = $reg->fetch();
            $return_str = $json_str['regions'];

            echo $return_str;
        }

        if (isset($_POST['get_formula'])) {
            $id = trim(filter_input(INPUT_POST, 'get_formula', FILTER_SANITIZE_SPECIAL_CHARS));

            try {
                $dbh->query("set names utf8");
                $reg = $dbh->query("SELECT `cash_formula` FROM `iwater_orders` WHERE `id` = " . $id);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $json_str = $reg->fetch();
            $return_str = $json_str['cash_formula'];

            echo $return_str;
        }

        if (isset($_POST['period']) && !isset($_GET['app'])) {
            try {
                $dbh->query("set names utf8");
                $res = $dbh->query("SELECT DISTINCT `period` FROM `iwater_company` WHERE `id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $r = $res->fetchAll();

            header('content-type: application/json;charset=utf8');
            echo $r;
        }

        if (isset($_POST['migrate_ord'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $dbh->query("set names utf8");
                $res = $dbh->prepare("SELECT o.address, `date`, o.period, `notice`, `water_equip`, `status`, com.region, o.client_id, o.id, c.name, c.phone FROM `iwater_orders_app` AS o LEFT JOIN `iwater_clients_app` AS c ON (o.client_id = c.id) LEFT JOIN `iwater_company` AS com ON (com.id = o.company_id) WHERE o.id = ? AND o.company_id = ?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($_POST['migrate_ord'], $company));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            header('content-type: application/json;charset=utf8');
            echo json_encode($res->fetchAll());
            exit();
        }

        if (isset($_POST['setting_mail'])) {
            try {
                $res = $dbh->query("SELECT * FROM `iwater_settings`");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $out_string = '';

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $out_string .= $r['data'];
            }

            echo $out_string;
        }

        if (isset($_POST['update_ord'])) {
            try {
                $dbh->query("set names utf8");
                $res = $dbh->prepare("SELECT * FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id = c.id) WHERE o.id = ?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($_POST['update_ord']));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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

        if (isset($_POST['delete_list'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $name = (trim(filter_input(INPUT_POST, 'delete_list', FILTER_SANITIZE_SPECIAL_CHARS)));
            unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $name);
            try {
                $res = $dbh->prepare('DELETE FROM `iwater_lists` WHERE `file` LIKE "' . $name . '";');
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
        }
        if (isset($_POST['page_lists'])) {
            //������ ��������
            $json = stripcslashes($_POST['page_lists']);
            $data = json_decode($json, true);
            $query = '';
            $date = $data;
            if ($date[0] != null) {
                $date[0] = explode('.', $date[0]);
                $date[0] = mktime(0, 0, 0, $date[0][1], $date[0][0], $date[0][2]);
                $count = count($data);
                $half_sec_in_day = 86400 / 2;
                if ($date[1] == "") {
                    $query = 'WHERE `date` > ' . strval($date[0] - $half_sec_in_day) . ' AND `date` < ' . strval($date[0] + $half_sec_in_day);
                } else {
                    $date[1] = explode('.', $date[1]);
                    $date[1] = mktime(0, 0, 0, $date[1][1], $date[1][0], $date[1][2]);
                    $query = 'WHERE `date` > ' . strval($date[0] - $half_sec_in_day) . ' AND `date` < ' . strval($date[1] + $half_sec_in_day);
                }
            }
            try {
                $res = $dbh->prepare("SELECT COUNT(DISTINCT `file`) AS count_l FROM `iwater_lists`" . $query . " ORDER BY `date` DESC");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
            if ($count_pages > 1) { // �� ��� ������ ���� ���������� ������� ������ 1
                /* ������ ��� ���������� ������ ��������� �������� � ��������� (����� ������� �������� ���� ���-�� ���������, ���� ��� ��������, � ����� ����� ����� ��������� ������� ���� ����� count_show_pages, ���� ������, ���� ���������� ������� ������������) */
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

                <div id="pagination">
                    <span>Pages: </span>
                    <?php if ($active != 1) {
                    ?>
                        <a href="<?= $url ?>" title="������ ��������">&lt;&lt;&lt;</a>
                        <a href="<?php if ($active == 2) {
                        ?><?= $url ?><?php
                    } else {
                        ?><?= $url_page . ($active - 1) ?><?php
                    } ?>"
                           title="���������� ��������">&lt;</a>
                    <?php
                } ?>
                    <?php for ($i = $start; $i <= $end; $i++) {
                    ?>
                        <?php if ($i == $active) {
                        ?><span><?= $i ?></span><?php
                    } else {
                        ?><a
                            href="<?php if ($i == 1) {
                            ?><?= $url ?><?php
                        } else {
                            ?><?= $url_page . $i ?><?php
                        } ?>"><?= $i ?></a><?php
                    } ?>
                    <?php
                } ?>
                    <?php if ($active != $count_pages) {
                    ?>
                        <a href="<?= $url_page . ($active + 1) ?>" title="��������� ��������">&gt;</a>
                        <a href="<?= $url_page . $count_pages ?>" title="��������� ��������">&gt;&gt;&gt;</a>
                    <?php
                } ?>
                </div>
                <?php
            }
        }

        if (isset($_POST['page_clients'])) {
            //������ ��������
            $json = stripcslashes($_POST['page_clients']);
            $data = json_decode($json, true);
            $data[1] = iconv(mb_detect_encoding($data[1]), "CP1251", $data[1]);
            $data[2] = iconv(mb_detect_encoding($data[2]), "CP1251", $data[2]);
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

            $get = iconv("cp1251", "utf-8", $get);

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
                echo '����������� �� �������: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $count_lists = $r['count_l'];
            }

            $lists_in_page = intval(trim(filter_input(INPUT_POST, 'lists_in_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            $count_pages = ceil($count_lists / $lists_in_page) - 2; /* ��� ��� � �������� ����� 2, ����� ��� 2 ������ �������� � ����� */
            $count_show_pages = 5;
            $active = intval(trim(filter_input(INPUT_POST, 'current_page', FILTER_SANITIZE_SPECIAL_CHARS)));
            if (!is_int($active)) {
                $active = 1;
            }
            $url = "/iwaterTest/admin/list_clients/?";
            $url_page = "/iwaterTest/admin/list_clients/?page=";
            if ($count_pages > 1) { // �� ��� ������ ���� ���������� ������� ������ 1
                /* ������ ��� ���������� ������ ��������� �������� � ��������� (����� ������� �������� ���� ���-�� ���������, ���� ��� ��������, � ����� ����� ����� ��������� ������� ���� ����� count_show_pages, ���� ������, ���� ���������� ������� ������������) */
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
                <!-- ������ ��� ����� Pagination -->
                <div id="pagination">
                    <span>Pages: </span>
                    <?php if ($active != 1) {
                    ?>
                        <a href="<?= $url . $get ?>" title="������ ��������">&lt;&lt;&lt;</a>
                        <a href="<?php if ($active == 2) {
                        ?><?= $url . $get ?><?php
                    } else {
                        ?><?= $url_page . ($active - 1) . $get ?><?php
                    } ?>"
                           title="���������� ��������">&lt;</a>
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
                        <a href="<?= $url_page . ($active + 1) . $get ?>" title="��������� ��������">&gt;</a>
                        <a href="<?= $url_page . $count_pages . $get ?>" title="��������� ��������">&gt;&gt;&gt;</a>
                    <?php
                } ?>
                </div>
                <?php
            }
        }

        if (isset($_POST['list_clients_upd'])) {
            //�������� ������ �������
            $z = 0;
            $ze = count($_POST['type']);

            try {
                $res = $dbh->prepare('UPDATE `iwater_clients` SET `type`=?,`name`=? WHERE client_id = ?');
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            while ($z < $ze) {
                $address = explode(', ', strip_tags($_POST['address'][$z]));
                $region = $address[0];
                unset($address[0]);
                sort($address);
                $res->execute(array(strip_tags($_POST['type'][$z]), strip_tags($_POST['names'][$z]), strip_tags($_POST['client_id'][$z])));
                $z++;
            }
            setActionLog("client", "��������������", "iwater_clients", "");
            header('Location: /iwaterTest/admin/list_clients/');
        }
        if (isset($_POST['add_order'])) {

            //�������� �����
            $mobile = trim(filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_SPECIAL_CHARS));
            $client_id = trim(filter_input(INPUT_POST, 'client_num', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $formula = trim(filter_input(INPUT_POST, 'cash_formula', FILTER_SANITIZE_SPECIAL_CHARS));

            $dbh->query("SET NAMES CP1251;");
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            try {
                $res = $dbh->query("SELECT count(`id`) FROM `iwater_clients` WHERE `client_id`='" . $client_id . "'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $ok = $r['count(`id`)'];
            }
            if ($ok || $client_id == "--") {
                $name = trim(filter_input(INPUT_POST, 'name'));
                $region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS));
                if ($region == "default") {
                    try {
                        $res = $dbh->query("SELECT a.region as rg FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id = '$client_id' AND a.address  LIKE '%$address%' LIMIT 1");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $region = $r['rg'];
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
                $water_equip = trim(filter_input(INPUT_POST, 'water_equip', FILTER_SANITIZE_SPECIAL_CHARS));
                $equip = trim(filter_input(INPUT_POST, 'equip'));
                $dep = trim(filter_input(INPUT_POST, 'dep', FILTER_SANITIZE_SPECIAL_CHARS));
                $cash = trim(filter_input(INPUT_POST, 'cash', FILTER_SANITIZE_SPECIAL_CHARS));
                $cash_b = trim(filter_input(INPUT_POST, 'cash_b', FILTER_SANITIZE_SPECIAL_CHARS));
                $on_floor = trim(filter_input(INPUT_POST, 'on_floor', FILTER_SANITIZE_SPECIAL_CHARS));
                $tank_b = trim(filter_input(INPUT_POST, 'tank_b', FILTER_SANITIZE_SPECIAL_CHARS));
                $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
                $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
                $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS));
                $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
                $cords = trim(filter_input(INPUT_POST, 'cords', FILTER_SANITIZE_SPECIAL_CHARS));

                if ($cords == "") {
                    $cords = null;
                }
                $date = explode('/', $date);
                $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
                if ($no_date) {
                    $date = '';
                    $no_date = 1;
                }

                try {
                    $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `company_id`, `name`, `address`, `contact`, `date`, `no_date`, `time`, `period`, `notice`, `water_equip`, `equip`, `dep`, `cash`, `cash_b`,`cash_formula`, `cash_b_formula`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `coords`, `mobile`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($client_id, $company, $name, $address, $contact, $date, $no_date, $time, $time_d, $notice, $water_equip, $equip, $dep, $cash, $cash_b, $formula, $formula, $on_floor, $tank_b, $tank_empty_now, $driver, $status, $reason, $region, $cords, $mobile));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage() . '</br>';
                }


                setActionLog("order", "����������", "iwater_orders", "������: " . $name . " ����: " . trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS)) . " ��������: " . $driver);
                header('Location: /iwaterTest/admin/list_orders/');
            } else {
                header('Location: /iwaterTest/admin/add_order/');
            }
        }
        if (isset($_POST['edit_order'])) {
            $id = trim(filter_input(INPUT_POST, 'db_id', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $dbh->query("SET NAMES CP1251;");
                $res = $dbh->query("SELECT *,u.name AS d_name, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON(o.driver=u.id)  WHERE o.id='$id';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            };
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                setActionLog("order", "�������������� ��������������", "iwater_orders", "������: " . $r['client_id'] . " ��������: " . $r['name'] . " ������ ������: " . $r['id'] . " " . $r['client_id'] . " " . $r['address'] . " " . $r['contact'] . " " . $r['date'] . " " . $r['no_date'] . " " . $r['time'] . " " . $r['time_d'] . " " . $r['notice'] . " " . $r['water_equip'] . " " . $r['water_total'] . " " . $r['equip'] . " " . $r['dep'] . " " . $r['cash'] . " " . $r['cash_b'] . " " . $r['on_floor'] . " " . $r['tank_b'] . " " . $r['tank_empty_now'] . " " . $r['driver'] . " " . $r['status'] . " " . $r['reason'] . " " . $r['region']);
            }


            $id = trim(filter_input(INPUT_POST, 'db_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $client_id = trim(filter_input(INPUT_POST, 'client_num', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = trim(filter_input(INPUT_POST,'name'));
            $region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_SPECIAL_CHARS));


            if ($region == "default") {
                try {
                    $res = $dbh->query("SELECT `region` FROM `iwater_clients` WHERE `client_id`='" . $client_id . "'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
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
            $cash_formula = trim(filter_input(INPUT_POST, 'cash_formula', FILTER_SANITIZE_SPECIAL_CHARS));
            $cash_b_formula = trim(filter_input(INPUT_POST, 'cash_formula', FILTER_SANITIZE_SPECIAL_CHARS));
            $on_floor = trim(filter_input(INPUT_POST, 'on_floor', FILTER_SANITIZE_SPECIAL_CHARS));
            $tank_b = trim(filter_input(INPUT_POST, 'tank_b', FILTER_SANITIZE_SPECIAL_CHARS));
            $tank_empty_now = trim(filter_input(INPUT_POST, 'tank_empty_now', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
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
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }
            // header('Location: /iwaterTest/admin/list_orders/');
            // + ������� ��-�� echo ����
            echo '<script>location.replace("/iwaterTest/admin/list_orders/");</script>'; exit;
        }

        if (isset($_POST['water_list'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            echo 'this';

            $return_array = array();

            try {
                $dbh->query("SET CHARSET 'UTF8';");
                $res = $dbh->query("SELECT * FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE company_id = '$company' AND `shname` IS NOT NULL");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $i = 21;
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                  array_push($return_array, array('name' => $r['shname'], 'index' => $r['shname'], 'width' => 40, 'align' => 'center', 'editable' => false, 'sortable' => false));
                  $i++;
            }

            echo json_encode($return_array);
        }

        if (isset($_POST['page'])) {
            if (isset($_GET['order'])) {
                $page = $_POST['page'];
                $limit = $_POST['rows'];
                $sidx = $_POST['sidx'];
                $sord = $_POST['sord'];


                $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
                $usersess = $res->fetch();
                $company = $usersess['company_id'];

                //������ �������
                $extraSQL = "WHERE o.company_id = '$company'";
                if (isset($_GET['client_order'])) {
                    $client_id = trim(filter_input(INPUT_GET, 'client_order', FILTER_SANITIZE_SPECIAL_CHARS));
                    $extraSQL .= " AND o.client_id = '$client_id' ";
                }
                if (isset($_GET['no_date_order'])) {
                    iconv("utf-8", "windows-1251", $extraSQL = 'AND o.date = "" ');
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
                    if (isset($_POST['filters'])) {
                        $json = stripslashes($_POST['filters']);

                        $filters = json_decode($json);

                        $where = generateSearchStringFromObj($filters);
                        $extraSQL = " WHERE " . $where . " ";

                        $extraSQL = iconv('utf-8', 'windows-1251', $extraSQL);
                    }
                }


                $result = $dbh->query("SELECT *,u.name AS d_name, u.session AS u_sess, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id)" . $extraSQL);
                $count = $result->rowCount();
                $dbh->query(null);

                $total_pages = ceil($count/$limit);


                try {
                    $start = ($page - 1) * $limit;
                    $finish = $page * $limit;
                    $res = $dbh->query("SELECT *, o.address, u.name AS d_name, o.id AS o_id, o.name AS o_name FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(o.driver=u.id) " . $extraSQL . " ORDER BY $sidx $sord LIMIT " . $start . ", " . $finish);
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                $out = "<?xml version='1.0' encoding='cp1251'?>";
                $out .= '<rows>';
                $out .= '<page>' . $page . '</page>';
                $out .= '<total>' . $total_pages . '</total>';
                $out .= '<records>' . $count . '</records>';
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $newdate = "";
                    if ($r['date'] != "") {
                        $newdate = iconv("cp1251", "utf-8", (date("d/m/Y", $r['date'])));
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
                    $out .= '<cell></cell>';
                    $out .= '<cell><![CDATA[' . $r['client_id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['o_name'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['time'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['d_name'] . ']]></cell>';

                    //������������� ����� � ���������� ���� �� ��������� �������

                    $water_order = unserialize($r['water_equip']);

                    try {
                        $unit_w = $dbh->query("SELECT * FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE company_id = '$company'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
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
                    $out .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $string_equip . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['history'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['notice'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['reason'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $client . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['checked'] . ']]></cell>';
                    $out .= '</row>';
                }
                $out .= '</rows>';
                header("Content-type: text/xml;charset=cp1251");
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

                //������ ������� � ���������� ��� ����������� Android � iOS
                $extraSQL = "WHERE company_id = " . $company;
                if ($_GET['client_order']) {
                    $client_id = trim(filter_input(INPUT_GET, 'client_order', FILTER_SANITIZE_SPECIAL_CHARS));
                    $extraSQL .= " AND client_id = '$client_id'";
                }
                if ($_GET['no_date_order']) {
                    iconv("utf-8", "windows-1251", $extraSQL .= ' AND date = "" ');
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

                        $extraSQL = iconv('utf-8', 'windows-1251', $extraSQL);
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
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                $out = "<?xml version='1.0' encoding='cp1251'?>";
                $out .= '<rows>';
                $out .= '<page>' . $page . '</page>';
                $out .= '<total>' . $total_pages . '</total>';
                $out .= '<records>' . $count . '</records>';
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $newdate = "";
                    if ($r['date'] != "") {
                        $newdate = iconv("cp1251", "utf-8", (date("d/m/Y H:i", $r['date'])));
                    }

                    $equip = '';
                    $id = unserialize($r['water_equip']);
                    if (is_array($id)) {
                        foreach (array_keys(unserialize($r['water_equip'])) as $index => $value) {
                            $equip .= $value . ' - ' . $id[$index + 1];
                            if ($index + 1 < count($id)) {
                                $equip .= ', ';
                            }
                        }
                    } else {
                        $equip = 'null';
                    }

                    $waterFilt1 = array('{"id":"', '","count":"', '"}', '[', ']');
                    $waterFilt2 = array('id ', ' - ', '', '', '');

                    $out .= "<row id='" . $r['id'] . "'>";
                    $out .= '<cell></cell>';
                    $out .= '<cell><![CDATA[' . $r['id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['client_id'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['address'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['period'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['notice'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . str_replace($waterFilt1, $waterFilt2, $r['water_equip']) . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
                    $out .= '</row>';
                }
                $out .= '</rows>';
                header("Content-type: text/xml;charset=cp1251");
                echo $out;
            }

            if (isset($_GET['logs'])) {
                $page = $_POST['page'];
                $rows = $_POST['rows'];

                $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
                $usersess = $res->fetch();
                $company = $usersess['company_id'];

                if ($_GET['logs'] == "order") {
                    $extraSQL = " AND `operation` LIKE '%order%'";
                }
                /**
                $result = $dbh->query("SELECT count(`id`) FROM `iwater_logs` WHERE company_id = " . $company . $extraSQL);
                $count = $result->rowCount();
                */

                $count = 24502;

                try {
                    $start = ($page - 1) * $rows;
                    $finish = $page * $rows;

                    $res = $dbh->query("SELECT *,u.name AS admin FROM `iwater_logs` AS l JOIN `iwater_users` AS u ON(l.user_id=u.id) WHERE u.company_id = " . $company . $extraSQL . " ORDER BY l.time DESC" . " LIMIT " . $start . ", " . $finish);
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $page_count = ceil($count / $rows);

                $out = "<?xml version='1.0' encoding='cp1251'?>";
                $out .= '<rows>';
                $out .= '<page>' . $page . '</page>';
                $out .= '<total>' . $page_count . '</total>';
                $out .= '<records>' . $count . '</records>';
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $newdate = "";

                    if ($r['time'] != "") {
                        $newdate = iconv("cp1251", "utf-8", (date("d/m/Y H:i:s", $r['time'])));
                    }
                    $out .= "<row id='" . $r['id'] . "'>";
                    $out .= '<cell><![CDATA[' . $newdate . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['login'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['operation'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' .  iconv(mb_detect_encoding($r['action']), "cp1251", $r['action']) . ']]></cell>';
                    $out .= '<cell><![CDATA[' . $r['table'] . ']]></cell>';
                    $out .= '<cell><![CDATA[' . iconv(mb_detect_encoding($r['data']), "cp1251", $r['data']) . ']]></cell>';
                    $out .= '</row>';
                }
                $out .= '</rows>';
                header("Content-type: text/xml;charset=cp1251");
                echo $out;
            }
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
                echo '����������� �� �������: ' . $e->getMessage();
            }



            $full_price = 0; // ����� ����

          //while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
          //  $pr = $dbh->query("SELECT * FROM `iwater_units` WHERE `id` = " . );
          //}
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
                        $res->execute(array(iconv(mb_detect_encoding($name), 'cp1251', $name), $company, $priority));
                    } catch (Exception $e) {
                        echo "����������� �� �������: " . $e->getMessage();
                    }

                    setActionLog("category", "����������", "iwater_category", "��������� ����� ���������: " . $name);

                    return;
                }
                if (isset($_GET['units'])) {
                    $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                    $name =  trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                    $shname =  trim(filter_input(INPUT_POST, 'shname', FILTER_SANITIZE_SPECIAL_CHARS));
                    $about =  trim(filter_input(INPUT_POST, 'about', FILTER_SANITIZE_SPECIAL_CHARS));
                    $price =  trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_SPECIAL_CHARS));
                    $discount = trim(filter_input(INPUT_POST, 'discount', FILTER_SANITIZE_SPECIAL_CHARS));
                    $category =  trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
                    $gallery =  trim(filter_input(INPUT_POST, 'gallery', FILTER_SANITIZE_SPECIAL_CHARS));
                    $logo =  trim(filter_input(INPUT_POST, 'logo', FILTER_SANITIZE_SPECIAL_CHARS));

                    $shname = iconv(mb_detect_encoding($shname), "utf-8", $shname);
                    if ($shname == '') { $shname = null; }

                    try {
                        $dbh->query("set names utf8");

                        $res=$dbh->prepare("INSERT INTO `iwater_units` (`name`, `shname`, `price`, `discount`, `category`, `about`, `gallery`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(
                            iconv(mb_detect_encoding($name), "utf-8", $name),
                            $shname,
                            iconv(mb_detect_encoding($price), "utf-8", $price),
                            $discount,
                            iconv(mb_detect_encoding($category), "utf-8", $category),
                            iconv(mb_detect_encoding($about), "utf-8", $about),
                            iconv(mb_detect_encoding($gallery), "utf-8", $gallery)));
                        if (StrLen($logo) > 5) {
                            scaleImage($logo, $dbh->lastInsertId(), 150);
                        }
                    } catch (Exception $e) {
                        echo "����������� �� �������: " . $e->getMessage();
                    }

                    setActionLog("units", "����������", "iwater_units", "�������� ����� �����: " . $name);

                    return;
                }
            }
            if ($_POST['oper'] == 'edit') {
                if (isset($_GET['units'])) {
                    //�������� ������ ������
                    $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                    try {
                        $dbh->query("set names utf8");
                        $res=$dbh->query("SELECT `name`, `price`, `category`, `about` FROM `iwater_units` WHERE `id`='$id' ");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }

                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        setActionLog("units", "��������������", "iwater_units", "�����:" . $r['name'] . " ������ ������:" . $r['price'] . "�. " . $r['category'] . " " . $r['about']);
                    }

                    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
                    $shname = trim(filter_input(INPUT_POST, 'shname', FILTER_SANITIZE_SPECIAL_CHARS));
                    $about = trim(filter_input(INPUT_POST, 'about', FILTER_SANITIZE_SPECIAL_CHARS));
                    $price = trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_SPECIAL_CHARS));
                    $discount = trim(filter_input(INPUT_POST, 'discount', FILTER_SANITIZE_SPECIAL_CHARS));
                    $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
                    $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                    $gallery = trim(filter_input(INPUT_POST, 'gallery', FILTER_SANITIZE_SPECIAL_CHARS));
                    $logo = trim(filter_input(INPUT_POST, 'logo', FILTER_SANITIZE_SPECIAL_CHARS));

                    $shname = iconv(mb_detect_encoding($shname), "utf-8", $shname);
                    if ($shname == '') { $shname = null; }

                    if (StrLen($logo) > 5) {
                        scaleImage($logo, $id, 150);
                    }

                    try {
                        $dbh->query("set names utf8");
                        $res=$dbh->prepare('UPDATE `iwater_units` SET `name`=?, `shname`= ?, `about`=?,`price`=?, `discount` = ?, `category`=?, `gallery`=? WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(
                            iconv(mb_detect_encoding($name), "utf-8", $name),
                            $shname,
                            iconv(mb_detect_encoding($about), "utf-8", $about),
                            iconv(mb_detect_encoding($price), "utf-8", $price),
                            iconv(mb_detect_encoding($discount), "utf-8", $discount),
                            iconv(mb_detect_encoding($category), "utf-8", $category),
                            iconv(mb_detect_encoding($gallery), "utf-8", $gallery),
                            $id));

                        return;
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                }
                if (isset($_GET['app'])) {
                    //�������� ������ ������

                    //��������� ������� ��� ���������
                    $last = '';

                    $id =  trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                    try {
                        $dbh->query("SET NAMES utf8");
                        $res=$dbh->query("SELECT `client_id`, `address`, `status` FROM `iwater_orders_app` WHERE `id`='$id'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }

                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        setActionLog("order", "��������������", "iwater_orders_app", "������:" . $r['client_id'] . " ������ ������:". $r['client_id'] . " " . iconv(mb_detect_encoding($r['address']), "utf8", $r['address']) . " " . $r['status']);
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
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                }
                //�������� ������ ������
                $id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
                try {
                    $res = $dbh->query("SELECT `client_id`,`name`, `address`,`time`,`notice`,`water_equip`,`status`,`water_total`,`equip` FROM `iwater_orders` WHERE `id`='$id' ");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    setActionLog("order", "��������������", "iwater_orders", "������:" . $r['client_id'] . " " . $r['name'] . " ������ ������:" . $r['address'] . " " . $r['time'] . " " . $r['water_equip'] . " " . $r['status'] . " " . $r['water_total'] . " " . $r['equip']);
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
                $name = iconv(mb_detect_encoding($name), "CP1251", $name);
                $address = iconv(mb_detect_encoding($address), "CP1251", $address);
                $equip = iconv(mb_detect_encoding($equip), "CP1251", $equip);
                $notice = iconv(mb_detect_encoding($notice), "CP1251", $notice);
                $time = iconv(mb_detect_encoding($time), "CP1251", $time);

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
                    echo '����������� �� �������: ' . $e->getMessage();
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
                        $res->execute(array( iconv(mb_detect_encoding($id), "CP1251", $id) , $company ));

                        setActionLog("category", "��������", "iwater_category", "��������� " . $id . " �������");

                        return;
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                }
                if (isset($_GET['units'])) {
                    $units = array();

                    try {
                        $res = $dbh->prepare('SELECT * FROM `iwater_units` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        $units = $r;
                    }

                    try {
                        $res = $dbh->prepare('DELETE FROM `iwater_units` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));

                        unlink('../iwater_api/nusoap/images/product/hdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');
                        unlink('../iwater_api/nusoap/images/product/xhdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');
                        unlink('../iwater_api/nusoap/images/product/xxhdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');
                        unlink('../iwater_api/nusoap/images/product/xxxhdpi/' . trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)) . '.jpg');

                        setActionLog("units", "��������", "iwater_units", "�����: �" . $units['id'] . " ��������: " . iconv(mb_detect_encoding($units['name']), "cp1251", $units['name']));

                        return;
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                }

                if (isset($_GET['app'])) {
                    try {
                        $res=$dbh->prepare('SELECT * FROM `iwater_orders_app` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                        setActionLog("order_app", "��������", "iwater_orders_app", "������:".$r['client_id']." ������ ������:". iconv(mb_detect_encoding($r['address']), "cp1251", $r['address']) ." ".$r['water_equip']." ".$r['status']);
                    }

                    try {
                        $res=$dbh->prepare('DELETE FROM `iwater_orders_app` WHERE `id`=?');
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));

                        return;
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                }


                $order = array();
                try {
                    $res = $dbh->prepare('SELECT * FROM `iwater_orders` WHERE `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $order = $r;
                }

                try {
                    $res = $dbh->prepare('DELETE FROM `iwater_orders` WHERE `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array(trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS))));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                setActionLog("order", "��������", "iwater_orders", "�����: �" . $order['id'] . " ������: " . iconv(mb_detect_encoding($order['client_id']), "cp1251", $order['client_id']) . " ����: " . gmdate("Y-m-d", $order['date']) . " �����: " . iconv(mb_detect_encoding($order['time']), "cp1251", $order['time']));
            }
        }
        if (isset($_POST['add_list'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            //�������� ������� ����
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $date = explode('/', $date);
            $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

            try {
                $res = $dbh->prepare("SELECT count(`id`) as count, `date`, `file` FROM `iwater_lists` WHERE `date` = '$date' AND `company_id` = '$company'");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute();
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $exist = $r['count'];
            }

            // ���� ���� ������� ������ �� ��������� ���� ��� ����� �� ����������
            if ($exist != 0 || isset($_POST['extra_list_exist'])) {
                try {
                    $res = $dbh->query("SELECT max(`id`) AS `max` FROM `iwater_lists`");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                $r = $res->fetch(PDO::FETCH_ASSOC);
                $list_id = $r['max'] + 1;

                if ($list_id == 1) {
                    try {
                        $res = $dbh->query("SELECT max(`list`) AS `max` FROM `iwater_orders`");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }

                    $r = $res->fetch(PDO::FETCH_ASSOC);
                    $list_id = $r['max'] + 1;
                }

                if ($_POST['driver'] != "All") {
                    try {
                        $res = $dbh->query("SELECT `id` FROM `iwater_lists` WHERE `date`='$date'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }

                    $r = $res->fetch(PDO::FETCH_ASSOC);
                    $list_id = $r['id'];
                }

                try {
                    $res = $dbh->query("SELECT `regions` FROM `iwater_company`");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                $region = '';

                $regions = $res->fetchAll();
                if (strlen($regions['regions']) > 1) {
                    $region = 'AND `region` IN (' . $regions['regions'] . ')';
                }

                try {
                    $res = $dbh->prepare("SELECT o.id, u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver=u.id) WHERE `date`=? '$region' AND o.company_id = '$company'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                    $drivers[] = $r['driver_n'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $map_num = 2;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT `id` FROM `iwater_orders` WHERE `date`=? AND `company_id` = '$company'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $ids_order = null;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $map_num = 1;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT `id` FROM `iwater_orders` WHERE `date`=? AND `company_id` = '$company'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $ids_order = null;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $map_num = 0;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT DISTINCT u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver=u.id) WHERE list=? ");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($list_id));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $i = 0;

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $driversInList[$i][] = $r['driver_id'];
                    $driversInList[$i][] = $r['driver_n'];
                    $i++;
                }
                $file = date('j.m.Y', $date) . '(' . $company .')' . '.xlsx';
                $dbh = connect_db();
                if (isset($_SESSION['fggafdfc'])) {
                    $session = array();
                    try {
                        $sth = $dbh->prepare("SELECT `id`, `login`,`name`  FROM  `iwater_users` WHERE `session`=?");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sth->execute(array(($_SESSION['fggafdfc'])));
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                    while ($r = $sth->fetch(PDO::FETCH_ASSOC)) {
                        $session = $r;
                    }
                }
                if ((@fopen($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file, "r"))) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file);
                }
                try {
                    $sth = $dbh->prepare("INSERT INTO `iwater_lists`(`date`, `file`,`user_id`,`create_date`, `map_num`, `company_id`) VALUES (?, ?, ?, ?, ?, ?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sth->execute(array($date, $file, $session['id'], mktime(), $list_id, $company));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage() . '</br>';
                }
                for ($k = 0; $k < count($driversInList); $k++) {
                    $file_d = date('j.m.Y', $date) . '(' . $company . ')' . '(driver)' . iconv("CP1251", "UTF-8", $driversInList[$k][1]) . '.xlsx';
                    if ((@fopen($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file_d, "r"))) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file_d);
                    }

                    try {
                        $sth = $dbh->prepare("INSERT INTO `iwater_lists`(`date`, `file`,`user_id`,`create_date`, `map_num`, `driver_id`, `company_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sth->execute(array($date, $file_d, $session['id'], mktime(), $list_id . "?driver_id=" . $driversInList[$k][0], $driversInList[$k][0], $company));
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage() . '</br>';
                    }
                }

                $file = date('j.m.Y', $date) . '(' . $company . ')' . '.xlsx'; ?>
                <form method="post" id="lists" action="/iwaterTest/lists">
                    <input name="date" type="hidden" value=<?php echo $date ?>>
                    <input name="list_id" type="hidden" value=<?php echo $list_id ?>>
                    <input name="count" type="hidden" value=<?php echo count($driversInList) ?>>
                    <?php for ($k = 0; $k < count($driversInList); $k++) {
                    ?>
                        <input name="<?php echo "driver_id_" . $k ?>" type="hidden"
                               value="<?php echo $driversInList[$k][0] ?>">
                        <input name="<?php echo "driver_name_" . $k ?>" type="hidden"
                               value="<?php echo $driversInList[$k][1] ?>">
                    <?php
                } ?>
                </form>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
                <script>
                    if (confirm("������� ���� �� ���� ����� ��� �����������, ��������?"))   {
                        $('#lists').submit();
                    } else {
                        document.location.href = '/iwaterTest/admin/add_list/';
                    }
                </script>

                <form method="post" id="extraList" action="/iwaterTest/backend.php">
                    <input name="date" type="hidden" placeholder="���� ��������" value=<?php echo $_POST['date'] ?>>
                    <input name="driver" type="hidden" value=<?php echo $_POST['driver'] ?>>
                    <input name="add_list" type="hidden">
                    <input name="extra_list_exist" type="hidden">
                </form>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
                <script>
                if (confirm('')) {
                    $('#extraList').submit();
                }
                </script>
                <?php
                    //header('Location: /iwaterTest/admin/add_list/;  charset=utf-8' );
            } else {
                try {
                    $res = $dbh->query("SELECT max(`id`) AS `max` FROM `iwater_lists`");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                $r = $res->fetch(PDO::FETCH_ASSOC);
                $list_id = $r['max'] + 1;

                if ($list_id == 1) {
                    try {
                        $res = $dbh->query("SELECT max(`list`) AS `max` FROM `iwater_orders`");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }

                    $r = $res->fetch(PDO::FETCH_ASSOC);
                    $list_id = $r['max'] + 1;
                }

                if ($_POST['driver'] != "All") {
                    try {
                        $res = $dbh->query("SELECT `id` FROM `iwater_lists` WHERE `date`='$date'");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }

                    $r = $res->fetch(PDO::FETCH_ASSOC);
                    $list_id = $r['id'];
                }

                try {
                    $res = $dbh->query("SELECT `regions` FROM `iwater_company`");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $region = '';

                $regions = $res->fetchAll();
                if (strlen($regions['regions']) > 1) {
                    $region =  "AND `region` IN (" . $regions['regions'] . ") ";
                }

                try {
                    $res = $dbh->prepare("SELECT o.id, u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver=u.id) WHERE `date`=? '$region' AND o.company_id = '$company'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                    $drivers[] = $r['driver_n'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $map_num = 2;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT `id` FROM `iwater_orders` WHERE `date`=? AND `company_id` = '$company'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $ids_order = null;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $map_num = 1;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT `id` FROM `iwater_orders` WHERE `date`=? AND `company_id` = '$company'");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($date));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $ids_order = null;
                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $ids_order[] = $r['id'];
                }

                try {
                    $res = $dbh->prepare('UPDATE `iwater_orders` SET `list`=?,`map_num`=? WHERE `date`=? AND `id`=?');
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $map_num = 0;
                $i = 0;
                $z = count($ids_order);
                while ($i < $z) {
                    $id_order = $ids_order[$i];
                    $i++;
                    $res->execute(array($list_id, $map_num, $date, $id_order));
                }
                try {
                    $res = $dbh->prepare("SELECT DISTINCT u.id AS driver_id, u.name AS driver_n FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON(driver=u.id) WHERE list=? ");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $res->execute(array($list_id));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage();
                }
                $i = 0;

                while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                    $driversInList[$i][] = $r['driver_id'];
                    $driversInList[$i][] = $r['driver_n'];
                    $i++;
                }
                $file = date('j.m.Y', $date) . '(' . $company .')' . '.xlsx';
                $dbh = connect_db();
                if (isset($_SESSION['fggafdfc'])) {
                    $session = array();
                    try {
                        $sth = $dbh->prepare("SELECT `id`, `login`,`name`  FROM  `iwater_users` WHERE `session`=?");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sth->execute(array(($_SESSION['fggafdfc'])));
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage();
                    }
                    while ($r = $sth->fetch(PDO::FETCH_ASSOC)) {
                        $session = $r;
                    }
                }
                if ((@fopen($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file, "r"))) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file);
                }
                try {
                    $sth = $dbh->prepare("INSERT INTO `iwater_lists`(`date`, `file`,`user_id`,`create_date`, `map_num`, `company_id`) VALUES (?, ?, ?, ?, ?, ?)");
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sth->execute(array($date, $file, $session['id'], mktime(), $list_id, $company));
                } catch (Exception $e) {
                    echo '����������� �� �������: ' . $e->getMessage() . '</br>';
                }
                for ($k = 0; $k < count($driversInList); $k++) {
                    $file_d = date('j.m.Y', $date) . '(' . $company . ')' . '(driver)' . iconv("CP1251", "UTF-8", $driversInList[$k][1]) . '.xlsx';
                    if ((@fopen($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file_d, "r"))) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/files/' . $file_d);
                    }

                    try {
                        $sth = $dbh->prepare("INSERT INTO `iwater_lists`(`date`, `file`,`user_id`,`create_date`, `map_num`, `driver_id`, `company_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sth->execute(array($date, $file_d, $session['id'], mktime(), $list_id . "?driver_id=" . $driversInList[$k][0], $driversInList[$k][0], $company));
                    } catch (Exception $e) {
                        echo '����������� �� �������: ' . $e->getMessage() . '</br>';
                    }
                }

                $file = date('j.m.Y', $date) . '(' . $company . ')' . '.xlsx'; ?>
                <form method="post" id="lists" action="/iwaterTest/lists">
                    <input name="date" type="hidden" value=<?php echo $date ?>>
                    <input name="list_id" type="hidden" value=<?php echo $list_id ?>>
                    <input name="count" type="hidden" value=<?php echo count($driversInList) ?>>
                    <?php for ($k = 0; $k < count($driversInList); $k++) {
                    ?>
                        <input name="<?php echo "driver_id_" . $k ?>" type="hidden"
                               value="<?php echo $driversInList[$k][0] ?>">
                        <input name="<?php echo "driver_name_" . $k ?>" type="hidden"
                               value="<?php echo $driversInList[$k][1] ?>">
                    <?php
                } ?>
                </form>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
                <script>
                    $('#lists').submit();
                </script>

                <form method="post" id="extraList" action="/iwaterTest/backend.php">
                    <input name="date" type="hidden" placeholder="���� ��������" value=<?php echo $_POST['date'] ?>>
                    <input name="driver" type="hidden" value=<?php echo $_POST['driver'] ?>>
                    <input name="add_list" type="hidden">
                    <input name="extra_list_exist" type="hidden">
                </form>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
                <script>
                    $('#extraList').submit();
                </script>
                <?php
            //header('Location: /iwaterTest/admin/add_list/;  charset=utf-8' );
            }
        }
        if (isset($_POST['client_num_l'])) {
            //����� id ������� �� �����
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
                echo '����������� �� �������: ' . $e->getMessage();
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
            //����� ������ � �������� ������� �� id
            $client_num = trim(filter_input(INPUT_POST, 'client_num_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id  WHERE c.client_id = '$client_num' AND a.address  LIKE '%$address%' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
            //����� ����� �������
            $name = trim(filter_input(INPUT_POST, 'name_l', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = iconv(mb_detect_encoding($name), "CP1251", $name);
            try {
                //				$dbh->query('SET CHARACTER SET utf8');
                $res = $dbh->query("SELECT DISTINCT c.name,c.client_id,a.address FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.name LIKE ('%$name%')");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
            //����� id � ������ �� ����� �������
            $name = trim(filter_input(INPUT_POST, 'name_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $name = iconv(mb_detect_encoding($name), "CP1251", $name);
            $name = html_entity_decode($name);
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.name = '$name' AND a.address LIKE '%$address%' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
            //����� ������
            $address = trim(filter_input(INPUT_POST, 'address_l', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT DISTINCT a.address,c.client_id,c.name FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE a.address LIKE ('%$address%')");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
            //����� ����� � id ������� �� ������
            $address = trim(filter_input(INPUT_POST, 'address_s', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = iconv(mb_detect_encoding($address), "CP1251", $address);
            try {
                $res = $dbh->query("SELECT * FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE a.address = '$address' LIMIT 1");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
            //���������� �������

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $water_array = array();

            try {
                $res = $dbh->query("SELECT * FROM `iwater_units` WHERE `shname` IS NOT NULL");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                array_push($water_array, $r['id']);
            }

            $list_id = trim(filter_input(INPUT_POST, 'list_p', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $extraSQL = "";
            $list_id = explode('?', $list_id);
            $list_id = $list_id[0];
            $extraSQL .=  $list_id;
            if ($driver_id != "") {
                $extraSQL .= " AND `driver` =" . $driver_id;
            }
            if (isset($_POST['exception_driver'])) {
                $exc = $_POST['exception_driver'];
                for ($i=0;$i<count($exc);$i++) {
                    $extraSQL.=" AND `driver` !=" . $exc[$i];
                }
            }
            try {
                $res = $dbh->query("SELECT *, o.address, a.coords AS cor_p, o.coords AS cor_new, o.id AS order_id, u.name as driver_name  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND `list` =" . $extraSQL . " AND o.status=0 GROUP BY o.id ORDER BY map_num, o.id DESC");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
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
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
        if (isset($_POST['createExcell'])) {
            $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_n = trim(filter_input(INPUT_POST, 'driver_n', FILTER_SANITIZE_SPECIAL_CHARS));
            createExcelFile($date, $driver_id, $driver_n);
            setActionLog("list", "������������", "iwater_lists", "�� ����: " . date('j.m.Y', $date));
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
                        echo '����������� �� �������: ' . $e->getMessage();
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
                echo '����������� �� �������: ' . $e->getMessage();
            }
            setActionLog("order", "��������� ���������", "iwater_orders", "�����: " . $order_id);
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
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $order = array();
            $notice = "";
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                setActionLog("order", "��������� ����", "iwater_order", "������: " . $r['client_id'] . " �������: " . $reason . " ������ ����: " . date('j.m.Y', $r['date']) . "����� ����: " . date('j.m.Y', $date));
                $notice = $r['history'];
                $old_date = $r['date'];
                $order = $r;
            }

            try {
                $res = $dbh->query("SELECT `id` FROM `iwater_orders` ORDER BY `id` DESC LIMIT 1 ");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $last_number = $r['id'];
            }

            $old_notice = $notice . " �������� � " . date('j.m.Y', $old_date) . " �� " . date('j.m.Y', $date) . ". ����� ������ ������: " . ($last_number + 1);
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `reason`='$reason',`history`='$old_notice', `status`=3 WHERE `id`=?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($id));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $new_notice = " �������� � " . date('j.m.Y', $old_date) . " �� " . date('j.m.Y', $date) . ". ����� c������ ������: " . $id;

            try {
                $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `company_id`, `name`, `address`, `contact`, `date`, `no_date`, `time`, `period`, `notice`, `water_equip`, `water_total`, `equip`, `dep`, `cash`, `cash_b`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `history`, `coords`, `cash_formula`, `cash_b_formula`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($order['client_id'], $order['company_id'], $order['name'], $order['address'], $order['contact'], $date, 0, $order['time'], $order['period'], $order['notice'], $order['water_equip'], $order['water_total'], $order['equip'], $order['dep'], $order['cash'], $order['cash_b'], $order['on_floor'], $order['tank_b'], $order['tank_empty_now'], "0", $order['status'], $order['reason'], $order['region'], $notice . $new_notice, $order['coords'], $order['cash_formula'], $order['cash_b_formula']));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
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
                echo '����������� �� �������: ' . $e->getMessage();
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
                    echo '����������� �� �������: ' . $e->getMessage();
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
                $res = $dbh->query("SELECT *, u.id AS u_id FROM `iwater_orders` AS o LEFT JOIN `iwater_users` AS u ON o.driver = u.id  WHERE o.id='$id' ORDER BY o.id DESC LIMIT 1;");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $regions = array('� ������', '������', '���������', '�������');
            $s = "<?xml version='1.0' encoding='cp1251'?>";
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
                if ($i > 0) {
                    $extraSQL .= " OR ";
                }
                $extraSQL .= " `id`=" . $_POST['get_status_selected'][$i];
            }
            try {
                $res = $dbh->prepare("UPDATE `iwater_orders` SET `status`=?" . $extraSQL);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($_POST['status']));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
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
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }
        }
        if (isset($_POST['order_excel_file'])) {
            $from = trim(filter_input(INPUT_POST, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
            $to = trim(filter_input(INPUT_POST, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
            $order_excel_file = trim(filter_input(INPUT_POST, 'order_excel_file', FILTER_SANITIZE_SPECIAL_CHARS));
            createOrderExcelFile($from, $to, $order_excel_file);
            setActionLog("order", "������������ Excel �����", "iwater_order", "�� ����: " . $from . " " . $to);
        }
        if (isset($_POST['order_app_excel_file'])) {
            $from = trim(filter_input(INPUT_POST, 'from', FILTER_SANITIZE_SPECIAL_CHARS));
            $to = trim(filter_input(INPUT_POST, 'to', FILTER_SANITIZE_SPECIAL_CHARS));
            $order_excel_file = trim(filter_input(INPUT_POST, 'order_app_excel_file', FILTER_SANITIZE_SPECIAL_CHARS));
            createOrderAppExcelFile($from, $to, $order_excel_file);
            setActionLog("order", "������������ Excel �����", "iwater_order_app", "�� ����: " . $from . " " . $to);
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
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }

            setActionLog("map", "��������� ��������", "iwater_orders", "������: " . $orders_id);

            echo 1;
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

        if (isset($_POST['driver_map_info'])) {
            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $list_id = trim(filter_input(INPUT_POST, 'list', FILTER_SANITIZE_SPECIAL_CHARS));
            $driver_id = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));

            $water_array = array();

            try {
                $res = $dbh->query("SELECT * FROM `iwater_units` WHERE `shname` IS NOT NULL");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                array_push($water_array, $r['id']);
            }

            $extraSQL = "";
            $list_id = explode('?', $list_id);
            $list_id = $list_id[0];
            $extraSQL .=  $list_id;
            // if ($driver_id != "") {
            //     $extraSQL .= " AND `driver` =" . $driver_id;
            // }
            if (isset($_POST['exception_driver'])) {
                $exc = $_POST['exception_driver'];
                for ($i=0;$i<count($exc);$i++) {
                    $extraSQL.=" AND `driver` !=" . $exc[$i];
                }
            }

            try {
                $res = $dbh->query("SELECT *, o.address, a.coords AS cor_p, o.coords AS cor_new, o.id AS order_id, u.name as driver_name  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id) LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND `list` =" . $list_id . " AND o.status=0");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }

            $extraSQL = "";
            $list_id = explode('?', $list_id);
            $list_id = $list_id[0];
            $extraSQL .= $list_id;
            if ($driver_id != "" && $driver_id != "all") {
                $extraSQL .= " AND `driver` =" . $driver_id;
            }

            try {
                $res = $dbh->query("SELECT u.id as driver_id, u.name as driver_name, SUM(o.water_total) AS total, `water_equip`,
                (SELECT COUNT(DISTINCT o.id) FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id)
                  LEFT JOIN `iwater_addresses` as a ON (o.address = a.address)
                  LEFT JOIN `iwater_users` as u ON (o.driver = u.id)
                  WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0)
                  AND `list` =" . $extraSQL . " AND o.status=0 AND u.name = driver_name
                  ORDER BY map_num, o.id DESC) AS count1
                  FROM `iwater_orders` AS o LEFT JOIN `iwater_clients` AS c ON (o.client_id=c.client_id)
                  LEFT JOIN `iwater_addresses` as a ON (o.address = a.address)
                  LEFT JOIN `iwater_users` as u ON (o.driver = u.id)
                  WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0)
                  AND `list` =" . $extraSQL . " AND o.status=0
                  GROUP BY driver_name ORDER BY map_num, o.id DESC");

                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $array = array();
            $i = 0;
            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $array[$i] = array();
                $total = 0;

                array_push($array[$i], iconv("cp1251", "utf-8", $r['driver_name']));
                $wt = $dbh->query("SELECT DISTINCT o.id, `water_equip` FROM `iwater_orders` AS o LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) LEFT JOIN `iwater_users` as u ON (o.driver = u.id) WHERE ((o.address = a.address AND o.client_id = a.client_id) OR o.client_id = 0) AND `driver` = '" . $r['driver_id'] . "' AND `list` =" . $extraSQL . " AND o.status=0");

                while ($w = $wt->fetch(PDO::FETCH_ASSOC)) {
                  $water = unserialize($w['water_equip']);
                  foreach ($water as $key => $value) {
                      if (in_array($key, $water_array)) {
                          $total += $value;
                      }
                  }
                }
                array_push($array[$i], $total);
                array_push($array[$i], $r['count1']);
                array_push($array[$i], $r['driver_id']);
                $i++;
            }
            print_r(json_encode($array));
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
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $map = trim(filter_input(INPUT_POST, 'map', FILTER_SANITIZE_SPECIAL_CHARS));
            $map = explode("/", $map);
            $map = $map[5];

            try {
                $res = $dbh->query(" SELECT `file` FROM `iwater_lists` AS l WHERE l.map_num='$map';");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
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
                echo '����������� �� �������: ' . $e->getMessage() . '</br>';
            }
            setActionLog("settings", "��������� e-mail", "iwater_settings", "��������� �������� �-�����".$arr_mail);
            header('Location: /iwaterTest/admin/settings/');
        }

        if (isset($_GET['list_units'])) {
            //������ ��������

            $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
            $usersess = $res->fetch();
            $company = $usersess['company_id'];

            $sidx = $_POST['sidx'];
            $sord = $_POST['sord'];

            try {
                $res = $dbh->query("SELECT u.id, u.name FROM `iwater_category` AS c LEFT JOIN `iwater_units` AS u ON (c.category_id = u.category) WHERE c.company_id = '$company' ORDER BY u.id");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            $s = "<?xml version='1.0' encoding='cp1251'?>";
            $s .= "<rows>";

            while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
                $s .= "<row>";

                $s .= "<cell id='id'><![CDATA[" . $r['id'] . "]]></cell>";
                $s .= "<cell id='name'><![CDATA[" . $r['name'] . "]]></cell>";
                $s .= "</row>";
            }
            $s .= "</rows>";
            header("Content-type: text/xml;charset=cp1251");
            echo $s;
        }
    }

    if (isset($_GET['company_id'])) {
        $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
        $usersess = $res->fetch();
        $company = $usersess['company_id'];

        $unit = $dbh->query("SELECT u.id, u.name FROM `iwater_category` AS c LEFT JOIN `iwater_units` AS u ON (c.category_id = u.category) WHERE c.company_id = " . $company);
        $units = $unit->fetchAll(PDO::FETCH_ASSOC);

        foreach ($units as $key => $val) {
            $units[$key]['name'] = iconv("cp1251", "UTF-8", $val['name']);
        }
        print json_encode($units);
    }

    if ($_GET) {
        if (isset($_GET['ban'])) {
            //��� ������������
            $id=trim(filter_input(INPUT_GET, 'ban', FILTER_SANITIZE_SPECIAL_CHARS));
            try {
                $res=$dbh->prepare("UPDATE `iwater_users` SET `ban`='1' WHERE `id`=?");
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $res->execute(array($id));
            } catch (Exception $e) {
                echo '����������� �� �������: ' . $e->getMessage();
            }
            setActionLog("user", "���������� ������������", "iwater_users", "������ c id: ".$id." ������� ");


            header('Location: /iwaterTest/admin/list_users/');
        }
        if (isset($_GET['logout'])) {
            session_destroy();
            header('Location: /iwaterTest');
        }
    }
    //header('Location: /iwaterTest/');

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
            echo '����������� �� �������: ' . $e->getMessage();
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
        echo '����������� �� �������: ' . $e->getMessage().'</br>';
    }
    return $session['id'];
}

function createExcelFile($date, $driver = "", $driver_name = "")
{ //��������
    $dbh = connect_db();
    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    $extraName = "";
    $extraSQL = "";

    if ($driver != "") {
        $extraName = '(driver)' . $driver_name;
        $extraSQL = " AND u.id = " . $driver;
    }

    $file = date('j.m.Y', $date) . '(' . $company . ')' . $extraName . '.xlsx';

    $filename = '/iwaterTest/files/' . $file;
    if (@fopen($_SERVER['DOCUMENT_ROOT'] . $filename, "r")) {
        echo $file;
        return 1;
    }

    $objPHPExcel = PHPExcel_IOFactory::load("files/order_blank2.xlsx");
    $objPHPExcel->getActiveSheet()->setCellValue('A3', date('j.m.Y', $date));
    $dbh = connect_db();

    try {
        $res=$dbh->prepare(" SELECT *, o.address AS o_address, o.contact AS o_contact, o.name as client_name, u.name AS driver_n, c.type AS type, (SELECT COUNT(o2.client_id) FROM `iwater_orders` AS o2 WHERE  o.client_id = o2.client_id GROUP BY o2.client_id) AS count_orders FROM `iwater_orders` AS o JOIN `iwater_users` AS u ON (driver = u.id) LEFT JOIN `iwater_clients` AS c ON o.client_id = c.client_id  WHERE `date`=?" . $extraSQL . " AND o.status = 0 AND o.company_id = ? ORDER BY map_num, o.id DESC");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $res->execute(array($date, $company));
    } catch (Exception $e) {
        echo '����������� �� �������: ' . $e->getMessage();
    }

    /** ���������� ����� ���������
    */

    //������
    if ($driver_name != "") {
        $objPHPExcel->getActiveSheet()->setCellValue("E2", $driver_name);
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue("E2", iconv('windows-1251', 'utf-8', '�����'));
    }

    //������ ������
    //$objPHPExcel->getActiveSheet()->setCellValue("G3", iconv('windows-1251', 'utf-8', '������ � ������'));
    $objPHPExcel->getActiveSheet()->setCellValue("I3", iconv('windows-1251', 'utf-8', '������� � �����'));

    //�������� ������
    $objPHPExcel->getActiveSheet()->setCellValue("G4", iconv('windows-1251', 'utf-8', '����� ��������:'));

    //����� ������(���������, ������ ��� ����� ����� ������)
    $start_loop = 8;
    $water_array = array(); //������ � id ����

    //�������� ����
    try {
        $wat = $dbh->query("SELECT u.id AS id, `shname` FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) LEFT JOIN `iwater_users` AS a ON (a.company_id = c.company_id) WHERE a.session = '" . $_SESSION['fggafdfc'] . "' AND `shname` IS NOT NULL");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo '����������� �� �������: ' . $e->getMessage();
    }

    $columns = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ'); //�������

    while ($w = $wat->fetch(PDO::FETCH_ASSOC)) {
          $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "4", iconv('windows-1251', 'utf-8', '=SUM(' . $columns[$start_loop] . '6:' . $columns[$start_loop] . '1000)'));
          $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "5", iconv('windows-1251', 'utf-8', $w['shname']));
          array_push($water_array, $w['id']);
          $start_loop++;
    }

    $objPHPExcel->getActiveSheet()->getStyle($columns[8] . "6:" . $columns[$start_loop] . "200")->getFont()->setSize(14);
    $objPHPExcel->getActiveSheet()->getStyle($columns[8] . "6:" . $columns[$start_loop] . "200")->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->mergeCells("I3:" . $columns[$start_loop] . "3");
    $objPHPExcel->getActiveSheet()->setCellValue("I3", iconv('windows-1251', 'utf-8', '������ � ������'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "4", iconv('windows-1251', 'utf-8', '=SUM(' . $columns[$start_loop] . '6:' . $columns[$start_loop] . '1000)'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . "5", iconv('windows-1251', 'utf-8', '�����'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . '5', iconv('windows-1251', 'utf-8', '������ ��� �����'));
    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop] . '5')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . '5', iconv('windows-1251', 'utf-8', '�������'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . '4', iconv('windows-1251', 'utf-8', '=SUM(' . $columns[$start_loop + 2] . "6:" . $columns[$start_loop + 2] . '399)'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . '3', iconv('windows-1251', 'utf-8', '������� � �����:'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . '1', iconv('windows-1251', 'utf-8', '���. ������������:'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . '3', iconv('windows-1251', 'utf-8', '=' . $columns[$start_loop + 2] . '4-' . $columns[$start_loop + 4] . '4'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . '5', iconv('windows-1251', 'utf-8', '������'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . '4', iconv('windows-1251', 'utf-8', '=SUM(' . $columns[$start_loop + 3] . "6:" . $columns[$start_loop + 3] . '399)'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 4] . '5', iconv('windows-1251', 'utf-8', '������ �� ����'));
    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 4] . '4', iconv('windows-1251', 'utf-8', '=SUM(' . $columns[$start_loop + 4] . "6:" . $columns[$start_loop + 4] . '399)'));

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

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 5] . '5', iconv('windows-1251', 'utf-8', '������������'));
    $objPHPExcel->getActiveSheet()->getColumnDimension($columns[$start_loop + 5])->setWidth(35);

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 6] . '5', iconv('windows-1251', 'utf-8', '����������'));
    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 6] . '5')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension($columns[$start_loop + 6])->setWidth(45);

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 7] . '5', iconv('windows-1251', 'utf-8', '������� ������'));
    $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 7] . '5')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension($columns[$start_loop + 7])->setWidth(15);

    /** ����� �������������
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

    $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . '3', '������� � �����:');

    for ($colorIndex = 0; $colorIndex <= $start_loop + 7; $colorIndex++) {
        $objPHPExcel->getActiveSheet()->getStyle($columns[$colorIndex] . '5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('595959');
    } //�������� ����� ������

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
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $x, iconv('windows-1251', 'utf-8', ($r['client_name'])));
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $x, $r['client_id']);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

        $styleArray_forNew = array(
            'fill' => array(
                'color' => array('rgb' => '000000')
            ),
            'font'  => array(
                'color' => array('rgb' => 'FFFFFF')
            ));

        if ($r['count_orders'] == 1) { $objPHPExcel->getActiveSheet()->getStyle('C' . $x)->applyFromArray($styleArray_forNew);  }

        $objPHPExcel->getActiveSheet()->setCellValue('D' . $x, iconv('windows-1251', 'utf-8', $r['o_address']));
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $x, iconv('windows-1251', 'utf-8', $r['o_contact']));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $x, iconv('windows-1251', 'utf-8', $r['time']));
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $x, iconv('windows-1251', 'utf-8', $r['driver_n']));

        $order_array = unserialize($r['water_equip']);

        $tool_string = ''; // ��������
        $tools_array = array(); //������ � id ������������

        for ($l = 8; $l < $start_loop; $l++) {
            foreach ($order_array as $key => $value) {
                $objPHPExcel->getActiveSheet()->getStyle($columns[$l] . $x)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($columns[$l] . $x)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($water_array[$l - 8] == $key) {
                    $objPHPExcel->getActiveSheet()->setCellValue($columns[$l] . $x, $value);
                } else if (!in_array($key, $water_array) && $l == 8) {
                    array_push($tools_array, array('key' => $key, 'count' => $value));
                }
            }
        }

        for ($i = 6; $i <= $x; $i++) {
            $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop] . "6:" . $columns[$start_loop] . "200")->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop] . $i, iconv('windows-1251', 'utf-8', '=SUM(' . $columns[8] . $i . ':' . $columns[$start_loop - 1] . $i . ')'));
        }
        //������������ ������ ������������
        try {
            $tool = $dbh->query("SELECT `id`, `name` FROM `iwater_units` AS u LEFT JOIN `iwater_category` AS c ON (u.category = c.category_id) WHERE `company_id` = '$company'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }

        while ($t = $tool->fetch(PDO::FETCH_ASSOC)) {
            foreach ($tools_array as $key => $value) {
                if ($value['key'] == $t['id']) {
                    $tool_string .= $value['count'] . ' - ' . $t['name'] . '
';
                }
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle("A6:A200")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("A6:A200")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . "1", iconv('windows-1251', 'utf-8', "���. ������������:"));
        $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 1] . "6:" . $columns[$start_loop + 1] . "200")->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 2] . "6:" . $columns[$start_loop + 4] . "200")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 1] . $x, iconv('windows-1251', 'utf-8', $r['dep']));
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 2] . $x, $r['cash']);
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 3] . $x, $r['cash_b']);
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 4] . $x, $r['on_floor']);

        $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 5] . "6:" . $columns[$start_loop + 7] . "200")->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 5] . $x, iconv('windows-1251', 'utf-8', $tool_string));
        // $objPHPExcel->getActiveSheet()->getStyle($columns[$start_loop + 5] . $x)->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 6] . $x, iconv('windows-1251', 'utf-8', $r['notice']));
        $objPHPExcel->getActiveSheet()->setCellValue($columns[$start_loop + 7] . $x, $r['number_visit']);

        // ������������
        for ($ch = 5; $ch < 100; $ch++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($ch)->setRowHeight(70);
        }

        /** ����� ���� ����������� ����
        */
        $objPHPExcel->getActiveSheet()->setCellValue('H4', iconv('windows-1251', 'utf-8', '=SUM(H6:H200)'));

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
    $objWriter->save('files/' . $file);

    echo iconv('utf8', 'cp1251', $file);
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
        echo '����������� �� �������: ' . $e->getMessage();
    }

    $x=4;
    $newMapNum = 0;

    $waterFilt1 = array('{"id":"', '","count":"', '"}', '[', ']');
    $waterFilt2 = array('id ', ' - ', '', '', '');

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $notice = (iconv('windows-1251', 'utf-8', $r['notice']) != '' ? iconv('windows-1251', 'utf-8', $r['notice']) : '�����');

        $date = date('j.m.Y', $r['date']);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $date);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$x, iconv('windows-1251', 'utf-8', $r['name']));
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$x, iconv('windows-1251', 'utf-8', $r['client_id']));
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$x, iconv('windows-1251', 'utf-8', $r['address']));
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$x, iconv('windows-1251', 'utf-8', $r['phone']));
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$x, iconv('windows-1251', 'utf-8', $r['period']));
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$x, iconv('windows-1251', 'utf-8', str_replace($waterFilt1, $waterFilt2, $r['water_equip'])));
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
        echo '����������� �� �������: ' . $e->getMessage();
    }

    $x=4;
    $newMapNum = 0;

    $waterFilt1 = array('{"', '}', '":', ',"');
    $waterFilt2 = array('id ', '', ' - ', ',id ');



    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $date = date('j.m.Y', $r['date']);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$x, $date);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$x, iconv('windows-1251', 'utf-8', ($r['client_name'])));
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$x, $r['client_id']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$x, iconv('windows-1251', 'utf-8', $r['address']));
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$x, iconv('windows-1251', 'utf-8', ($r['contact'])));
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$x, iconv('windows-1251', 'utf-8', ($r['time'])));
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$x, iconv('windows-1251', 'utf-8', $r['driver_n']));
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$x, $r['tank_empty_now']);
        $objPHPExcel->getActiveSheet()->setCellValue('I'.$x, str_replace($waterFilt1, $waterFilt2, json_encode(unserialize($r['water_equip']))));
        $objPHPExcel->getActiveSheet()->setCellValue('J'.$x, $r['water_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('K'.$x, iconv('windows-1251', 'utf-8', $r['dep']));
        $objPHPExcel->getActiveSheet()->setCellValue('L'.$x, $r['cash']);
        $objPHPExcel->getActiveSheet()->setCellValue('M'.$x, $r['cash_b']);
        $objPHPExcel->getActiveSheet()->setCellValue('N'.$x, $r['on_floor']);
        $objPHPExcel->getActiveSheet()->setCellValue('O'.$x, iconv('windows-1251', 'utf-8', $r['equip']));
        $objPHPExcel->getActiveSheet()->setCellValue('P'.$x, iconv('windows-1251', 'utf-8', $r['notice']));
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
        echo '����������� �� �������: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $contact .= $r['contact'];
    }
    return $contact;
}

function generateSearchStringFromObj($filters)
{
    $where = '';

    // ��������� ������� ������ ��������
    if (count($filters)) {
        foreach ($filters->rules as $index => $rule) {
            $rule->data = addslashes($rule->data);

            $where .= "".preg_replace('/-|\'|\"/', '', $rule->field)."";
            switch ($rule->op) { // � ������� ����� ������ ��������� ��� ���� ��������� ������� jqGrid
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

            // �������� ������ ����������, ���� ��� �� ��������� �������
            if (count($filters->rules) != ($index + 1)) {
                $where .= " ".addslashes($filters->groupOp)." ";
            }
        }
    }

    // ��������� ������� ��������� ��������
    $isSubGroup = false;
    if (isset($filters->groups)) {
        foreach ($filters->groups as $groupFilters) {
            $groupWhere = self::generateSearchStringFromObj($groupFilters);
            // ���� ��������� �������� �������� �������, �� �������� ��
            if ($groupWhere) {
                // �������� ������ ����������, ���� ������� ��������� �������� ����������� ����� ������� �������� ���� ������
                // ��� ����� ������� ������ �������� ��������
                if (count($filters->rules) or $isSubGroup) {
                    $where .= " ".addslashes($filters->groupOp)." ";
                }
                $where .= $groupWhere;
                $isSubGroup = true; // ����, ������������, ��� ���� ���� ���� ������� �������� ��������
            }
        }
    }

    if ($where) {
        return $where;
    }

    return ''; // ������� ���
}

function notf($id, $title, $body)
{
    $registrationIds = array( $id );

    $notification = array(
            'title'      => 'IWater', //��������� ����������
            'body'       => $body, //���� ����������
            'icon'       => 'ic_notifications', //�������
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

function changeStatus($id)
{
    $dbh=connect_db();

    $results = array('0' => '���������', '1' => '������', '2' => '����������', '3' => '���������');
    $notf = '';
    $status = '';

    try {
        $dbh->query("SET CHARACTER SET 'utf8'");
        $res = $dbh->query("SELECT notification, o.status FROM `iwater_clients_app` AS c LEFT JOIN `iwater_orders_app` AS o ON c.id = o.client_id WHERE o.id = " . $id);
    } catch (Exception $e) {
        echo '����������� �� �������: ' . $e->getMessage();
    }

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $notf =  $r['notification'];
        $status = $r['status'];
    }

    $result = notf($notf, 'IWater', "��� ����� ��� " . $results[$status] . ".");

    if ($result) {
        setActionLog("order_app", "�����������", "iwater_orders_app", "����������� �� ������ �" . $id . " ����������");

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
        echo '����������� �� �������: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        return $r['name'];
    }
}

function scaleImage($imagePath, $name, $max)
{
    $old = imageCreateFromJpeg($imagePath);
    // ������� ������ ��������
    $old_w = imageSX($old);
    $old_h = imageSY($old);

    /** ����������� ��� �������� ���������� �����������
    */
    $k = $old_h / $old_w; //y �����������
        $t = 1; //x �����������

        if ($k > 1) {
            $t = $old_w / $old_h;
            $k = 1;
        }

    /** HDPI
    */
    $new = imageCreateTrueColor($max * $t, $max * $k);
    imageCopyResampled($new, $old, 0, 0, 0, 0, $max * $t, $max * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, '../iwater_api/nusoap/images/product/hdpi/' . $name . '.jpg', 90);
    imagedestroy($new);

    /** xHDPI
    */
    $new = imageCreateTrueColor($max * 4 / 3 * $t, $max * 4 / 3 * $k);
    imageCopyResampled($new, $old, 0, 0, 0, 0, $max * 4 / 3 * $t, $max * 4 / 3 * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, '../iwater_api/nusoap/images/product/xhdpi/' . $name . '.jpg', 90);
    imagedestroy($new);

    /** xxHDPI
    */
    $new = imageCreateTrueColor($max * 2 * $t, $max * 2 * $k);
    imageCopyResampled($new, $old, 0, 0, 0, 0, $max * 2 * $t, $max * 2 * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, '../iwater_api/nusoap/images/product/xxhdpi/' . $name . '.jpg', 90);
    imagedestroy($new);

    /** xxxHDPI
    */
    $new = imageCreateTrueColor($max * 8 / 3 * $t, $max * 8 / 3 * $k);
    imageCopyResampled($new, $old, 0, 0, 0, 0, $max * 8 / 3 * $t, $max * 8 / 3 * $k, $old_w, $old_h);
    header('Content-type: image/jpeg');
    imagejpeg($new, '../iwater_api/nusoap/images/product/xxxhdpi/' . $name . '.jpg', 90);
    imagedestroy($new);
}
?>
