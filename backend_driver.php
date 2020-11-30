<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/Classes/PHPExcel.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/iwaterTest/inc/Classes/PHPExcel/IOFactory.php');
$dbh=connect_db();

if($_POST) {
    if (isset($_POST['driver_list'])) {

        $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
        $usersess = $res->fetch();
        $company = $usersess['company_id'];

        $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
        $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
        $date = explode('/', $date);
        $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

        try {
            $res = $dbh->prepare("SELECT *,o.address AS o_address, o.id as o_id, o.contact AS o_contact, o.name as client_name ,u.name AS driver_n, c.type AS type,
                                      (SELECT COUNT(o2.client_id)
                                      FROM `iwater_orders` AS o2
                                      WHERE  o.client_id = o2.client_id
                                      GROUP BY o2.client_id) AS count_orders
                                  FROM `iwater_orders` AS o
                                  JOIN `iwater_users` AS u ON(driver=u.id)
                                  LEFT JOIN `iwater_clients` AS c ON o.client_id = c.client_id
                                  WHERE o.company_id = '" . $company . "' AND `date`=" . $date . " AND u.id = " . $driver . "
                                  ORDER BY number_visit, map_num, o.id DESC");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute();
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
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
            $out .= '<cell><![CDATA[' . $r['client_id'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['client_name'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['o_address'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['time'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['water_ag'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['water_dp'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['water_e'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['water_pl'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['water_other'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['tank_empty_now'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['equip'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['number_visit'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['o_id'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['status'] . ']]></cell>';
            $out .= '<cell><![CDATA[' . $r['o_contact'] . ']]></cell>';

            $out .= '</row>';
        }
        $out .= '</rows>';
        header("Content-type: text/xml;charset=cp1251");
        echo $out;
    }
    if (isset($_POST['driver_done'])) {
        $order_id = trim(filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_SPECIAL_CHARS));
        $tank = trim(filter_input(INPUT_POST, 'tank', FILTER_SANITIZE_SPECIAL_CHARS));
        $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS));

        $coords_longitude = trim(filter_input(INPUT_POST, 'coords_longitude', FILTER_SANITIZE_SPECIAL_CHARS));
        $coords_latitude = trim(filter_input(INPUT_POST, 'coords_latitude', FILTER_SANITIZE_SPECIAL_CHARS));

        try {
            $res = $dbh->query("UPDATE `iwater_orders` SET `status`= 2 WHERE `id`='$order_id'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
        setActionLog("driver", "����� ��������", "iwater_order", "�����: " . $order_id . " ���-�� �������: " . $tank . " �����������: " .$comment);

        try {
            $res = $dbh->prepare("INSERT INTO `iwater_moved_orders` (`order_id`, `reason`,`comment`, `agreed`, `server_time`, `driver_coords`) VALUES (?, ?, ?, ?, ?, ? )");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array( $order_id, "", iconv( "utf-8", "cp1251", $comment ), "", time(), json_encode(array($coords_longitude,$coords_latitude))));
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage() . '</br>';
        }

    }
    if (isset($_POST['driver_cancel'])) {
        $order_id = trim(filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_SPECIAL_CHARS));
        $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS));
        $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS));
        $agreed = trim(filter_input(INPUT_POST, 'agreed', FILTER_SANITIZE_SPECIAL_CHARS));

        $coords_longitude = trim(filter_input(INPUT_POST, 'coords_longitude', FILTER_SANITIZE_SPECIAL_CHARS));
        $coords_latitude = trim(filter_input(INPUT_POST, 'coords_latitude', FILTER_SANITIZE_SPECIAL_CHARS));
        try {
            $res = $dbh->query("SELECT * FROM `iwater_orders` WHERE `id`='$order_id' ");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
        $order = array();
        $notice = "";
        while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            setActionLog("driver", "������� ���������", "iwater_order", "������: " . $r['client_id'] . " �������: " . $reason . " ������ ����: " . date('j.m.Y', $r['date']));
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

        $old_notice = $notice . " �������� � " . date('j.m.Y', $old_date) . ". ����� ������ ������: " . ($last_number + 1);
        try {
            $res = $dbh->prepare("UPDATE `iwater_orders` SET `reason`='$reason',`history`='$old_notice', `status`=3 WHERE `id`=?");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array($order_id));
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
        $new_notice = " �������� � " . date('j.m.Y', $old_date) . ". ����� c������ ������: " . $order_id;

        try {
            $res = $dbh->prepare("INSERT INTO `iwater_orders` (`client_id`, `name`, `address`, `contact`, `no_date`, `time`, `period`, `notice`, `water_ag`, `water_dp`, `water_e`, `water_pl`,`water_other`, `water_total`, `equip`, `dep`, `cash`, `cash_b`, `on_floor`, `tank_b`, `tank_empty_now`, `driver`, `status`, `reason`, `region`, `history`) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?, ?,?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?,?)");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array($order['client_id'], $order['name'], $order['address'], $order['contact'],  1, $order['time'], $order['period'], $order['notice'], $order['water_ag'], $order['water_dp'], $order['water_e'], $order['water_pl'], $order['water_other'], $order['water_total'], $order['equip'], $order['dep'], $order['cash'], $order['cash_b'], $order['on_floor'], $order['tank_b'], $order['tank_empty_now'], $order['driver'], $order['status'], $order['reason'], $order['region'], $notice . $new_notice));
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage() . '</br>';
        }

        try {
            $res = $dbh->prepare("INSERT INTO `iwater_moved_orders` (`order_id`, `reason`,`comment`, `agreed`, `server_time`, `driver_coords`) VALUES (?, ?, ?, ?, ?, ? )");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute(array( $order_id, iconv( "utf-8", "cp1251", $reason), iconv( "utf-8", "cp1251", $comment ), $agreed, time(), json_encode(array($coords_longitude,$coords_latitude))));
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage() . '</br>';
        }

        $settings = get_settings();
        $e_mail = $settings[0];
        $e_mail = $e_mail['data'];
        $e_mail = json_decode($e_mail);
        $e_mail_value = "";

        $mail_text = "";
        $mail_text .= "\n  ������ �� ������: \n";
        foreach ($order as $key => $value){
            $mail_text .= "\n ".$key. "   -  ".$value;
        }
        $mail_text .= "\n  ������� ��������:".  iconv("utf-8","cp1251",$reason);
        if($comment != "none") {
            $mail_text .= "\n  ����������� ��������: " .  iconv("utf-8","cp1251",$comment);
        }
        if($agreed == 0) {
            $agreed = "���";
        } else {
            $agreed = "��";
        }
        $mail_text.= "\n C���������� � ����������:". $agreed;
        $mail_text.= "\n ���������� ��������� �������� ��� ������: ".  iconv("utf-8","cp1251",$coords_longitude). " : ".  iconv("utf-8","cp1251",$coords_latitude);
        $header="Content-type:text/plain;charset=windows-1251\r\n";
        for ($i=0;$i<count($e_mail);$i++){
           mail($e_mail[$i], "������� ������ ���������",$mail_text, $header);
        }

//        header('Location: /iwaterTest/admin/list_orders/');

    }

    //��������� �������� �������� ��������
    if (isset($_POST['periods_del'])) {
        $unit = $dbh->query("SELECT `period` FROM `iwater_company` AS c LEFT JOIN `iwater_users` AS u ON (c.id = u.company_id) WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
        $units = $unit->fetch(PDO::FETCH_ASSOC);

        echo iconv("cp1251", "utf-8", $units['period']);
    }

    if (isset($_POST['sord_coords'])) {
        //�������� ��������� �������, ��� ����������� ����������
        $driver = trim(filter_input(INPUT_POST, 'sord_coords', FILTER_SANITIZE_SPECIAL_CHARS));
        $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));

        $date = explode('/', $date);
        $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

        try {
            $res = $dbh->query("SELECT o.coords AS coords, a.coords AS coords_a, o.period FROM `iwater_orders` AS o LEFT JOIN `iwater_addresses` AS a ON (a.address = o.address) WHERE o.driver = $driver AND o.date = '$date'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }

        $s = "<?xml version='1.0' encoding='cp1251'?>";
        $s .= "<rows>";

        while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            $s .= "<row>";
            if (strlen($r['coords_a'] > 4)) {
                $s .= "<cell id='coord'><![CDATA[" . $r['coords_a'] . "]]></cell>";
            } else {
                $s .= "<cell id='coord' class='temp'><![CDATA[" . $r['coords'] . "]]></cell>";
            }
            $s .= "<cell id='period'><![CDATA[" . $r['period'] . "]]></cell>";
            $s .= "</row>";
        }

        $s .= "</rows>";
        header("Content-type: text/xml;charset=cp1251");
        echo $s;
        //echo json_encode($res->fetchAll());
    }
    if (isset($_POST['sord_coords_period'])) {
        $driver = trim(filter_input(INPUT_POST, 'sord_coords_period', FILTER_SANITIZE_SPECIAL_CHARS));
        $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
        $period = trim(filter_input(INPUT_POST, 'period', FILTER_SANITIZE_SPECIAL_CHARS));

        $date = explode('/', $date);
        $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

        try {
            $res = $dbh->query("SELECT DISTINCT o.coords FROM `iwater_orders` AS o LEFT JOIN `iwater_addresses` AS a  ON  (a.address = o.address) WHERE o.driver = '$driver' AND o.date = '$date' AND o.period = '$period'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo json_encode($res->fetchAll());
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
    }
    if (isset($_POST['coords_data'])) {
        $coords = trim(filter_input(INPUT_POST, 'coords_data', FILTER_SANITIZE_SPECIAL_CHARS));
        $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
        $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
        $index = trim(filter_input(INPUT_POST, 'index', FILTER_SANITIZE_SPECIAL_CHARS));

        $date = explode('/', $date);
        $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

        try {
            $up = $dbh->query("UPDATE `iwater_orders` SET `number_visit` = '$index' WHERE `coords` = '$coords'");
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }

        try {
            $res = $dbh->query("SELECT *, o.coords AS cord, o.id AS order_id, a.coords AS acord FROM `iwater_orders` AS o LEFT JOIN `iwater_addresses` AS a ON (o.address = a.address) WHERE o.driver = $driver AND o.date = $date AND (o.coords = '$coords' OR a.coords = '$coords')");
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }

        $s = "<?xml version='1.0' encoding='cp1251'?>";
        $s .= "<rows>";

        while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            $s .= "<row>";
            if ($r['cord'] != '') {
                $s .= "<cell id='cord' class='temp'><![CDATA[" . $r['cord'] . "]]></cell>";
            } else {
                $s .= "<cell id='cord'><![CDATA[" . $r['acord'] . "]]></cell>";
            }
            $s .= "<cell id='time'><![CDATA[" . $r['time'] . "]]></cell>";
            $s .= "<cell id='tank_b'><![CDATA[" . $r['water_total'] . "]]></cell>";
            $s .= "<cell id='client_id'><![CDATA[" . $r['client_id'] . "]]></cell>";
            $s .= "<cell id='period'><![CDATA[" . $r['period'] . "]]></cell>";
            $s .= "<cell id='id'><![CDATA[" . $r['order_id'] . "]]></cell>";
            $s .= "</row>";
        }
        $s .= "</rows>";
        header("Content-type: text/xml;charset=cp1251");
        echo $s;
    }
    if (isset($_POST['driver_map_order'])) {
        //���������� �������
        $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
        $coords = trim(filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_SPECIAL_CHARS));
        $date = explode('/', $date);
        $date = mktime(0, 0, 0, $date[1], $date[0], $date[2]);

        try {
            $res = $dbh->query("SELECT * FROM `iwater_orders` AS o LEFT JOIN `iwater_addresses` as a ON (o.address = a.address) WHERE a.coords = '$coords' AND o.date = '$date'");
           $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
        $s = "<?xml version='1.0' encoding='cp1251'?>";
        $s .= "<rows>";

        while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            $s .= "<row>";
            $s .= "<cell id='cord' class='temp'><![CDATA[" . $r['coords'] . "]]></cell>";
            $s .= "<cell id='time'><![CDATA[" . $r['time'] . "]]></cell>";
            $s .= "<cell id='tank_b'><![CDATA[" . $r['water_total'] . "]]></cell>";
            $s .= "<cell id='client_id'><![CDATA[" . $r['client_id'] . "]]></cell>";
            $s .= "<cell id='period'><![CDATA[" . $r['period'] . "]]></cell>";
            $s .= "<cell id='id'><![CDATA[" . $r['order_id'] . "]]></cell>";
            $s .= "</row>";
        }
        $s .= "</rows>";
        header("Content-type: text/xml;charset=cp1251");
        echo $s;
    }
    if (isset($_POST['driver_notice'])) {
        //���������� �������
        $driver = trim(filter_input(INPUT_POST, 'driver', FILTER_SANITIZE_SPECIAL_CHARS));
        try {
            $res = $dbh->query("SELECT * FROM `iwater_notice` WHERE `dest_id`='$driver' ORDER BY `id` DESC");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
        $s = "<?xml version='1.0' encoding='cp1251'?>";
        $s .= "<rows>";

        while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            $s .= '<row id="row_'.$r['id'].'">';
            $s .= "<cell id='date'><![CDATA[" . date("d/m/Y h:i",$r['date']) . "]]></cell>";
            $s .= "<cell id='title'><![CDATA[" . $r['title'] . "]]></cell>";
            $s .= "<cell id='message'><![CDATA[" . $r['message'] . "]]></cell>";
            $s .= "<cell id='noticed'><![CDATA[" . $r['noticed'] . "]]></cell>";
            $s .= "<cell id='read'><![CDATA[" . $r['read'] . "]]></cell>";
            $s .= "<cell id='read'><![CDATA[" . $r['id'] . "]]></cell>";
            $s .= "</row>";
        }
        $s .= "</rows>";
        header("Content-type: text/xml;charset=cp1251");
        echo $s;
    }
    if (isset($_POST['driver_notice_to_read'])) {
        //���������� �������
        $notice = trim(filter_input(INPUT_POST, 'notice', FILTER_SANITIZE_SPECIAL_CHARS));
        $notice = explode("notice_",$notice);
        try {
            $res = $dbh->prepare("UPDATE `iwater_notice` SET `read`=1 WHERE `id`='$notice[1]'");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $res->execute();
        } catch (Exception $e) {
            echo '����������� �� �������: ' . $e->getMessage();
        }
        echo 1;
    }
}

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
            echo '����������� �� �������: ' . $e->getMessage();
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
        echo '����������� �� �������: ' . $e->getMessage().'</br>';
    }
    return $session['id'];
}
