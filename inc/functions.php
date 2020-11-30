<?php

ini_set('session.gc_maxlifetime', 18000);
session_set_cookie_params(18000);
session_start();
require($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/inc/Classes/DBConnect.php');

function path()
{

    return explode('/', $_SERVER['REQUEST_URI']);

}

function select_db()
{

    $arr = func_get_args();
    $dbh = $arr[0];
    $cols = $arr[1];
    $table = $arr[2];
    if (array_key_exists(3, $arr)) { $args = $arr[3]; }
    if (array_key_exists(4, $arr)) { $cond = $arr[4]; }
    if (array_key_exists(5, $arr)) { $order = $arr[5]; }
    if (array_key_exists(6, $arr)) { $limit = $arr[6]; }

    if (array_key_exists(3, $arr) && !is_array($args)) { $args = array($args); }

    $s_cols = implode(", ", $cols);
    if (array_key_exists(4, $arr) && $cond != NULL) { $s_cond = " WHERE " . implode(" = ? AND ", $cond) . " = ?"; } else { $s_cond = ""; }

    try {
        $res = $dbh->prepare("SELECT $s_cols FROM `$table` " . $s_cond);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (array_key_exists(4, $arr) && $cond != NULL) {
            $res->execute($args);
        } else {
            $res->execute();
        }
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    return $res;

}

function template($func = NULL)
{
    if (isset($_GET)) {
        $func = explode("?", $func);
        $func = $func[0];
        // Костыль, чтобы проходили гет запросы
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/template/tpl_start.php');
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/iwaterTest/template/layout/header.html");
    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/template/tpl_page.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/iwaterTest/template/tpl_finish.php');

}

function admin($path)
{
    if (check_perms($path[3])) {
        template('tpl_' . $path[3]);
    } else {
        template('tpl_insuf');
    }
}

function map($path)
{
    if ($path[3]) {
        template('tpl_map');
    }
}

function lists($path)
{
    if ($path[2]) {
        template('tpl_lists');
    }
}

function get_title()
{

    $path = path();
    $titles = array('list_users' => 'Список пользователей', 'iwaterTest' => 'Главная', 'admin' => 'Панель управления', 'add_company' => 'Добавить компанию', 'periods' => 'Изменить периоды', 'add_user' => 'Добавить пользователя', 'add_role' => 'Добавить роль', 'add_client' => 'Добавить клиента', 'list_clients' => 'Список клиентов', 'add_order' => 'Создать заказ', 'list_orders' => 'Список заказов', 'list_orders_app' => 'Список заказов Android/iOS', 'list_unit' => 'Список товаров', 'migrate_order' => 'Оформить заказ','add_list' => 'Добавить путевой лист', 'list_cut' => 'Разделить путевой лист', 'list_lists' => 'Список путевых листов', 'driver_position' => 'Координаты водителя', 'driver_stat' => 'Статистика водителей', 'analytics' => 'Предиктивная аналитика', 'logs' => 'Логи', 'delete_clients' => 'Корзина', 'settings' => 'Настройки', 'driver_list' => 'Путевой лист водителя', 'map' => 'Карта', 'driver_notice' => 'Уведомления');
    $title = 'Не опредлено';
    if (array_key_exists(3, $path)) {
        $title = $titles[$path[3]];
    } elseif ($path[2]) {
        $title = $titles[$path[2]];
    } elseif ($path[1]) {
        $title = $titles[$path[1]];
    }

    return $title;

}


function header_menu()
{
    require_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/iwaterTest/template/layout/header.html");
    return "";
//		$menu = '<ul><li><a href="/iwaterTest/">Главная</a></li><li><a href="/iwaterTest/admin/add_user/">Добавить пользователя</a></li><li><a href="/iwaterTest/admin/add_role/">Добавить роль</a></li><li><a href="/iwaterTest/admin/list_users/">Список пользователей</a></li><li><a href="/iwaterTest/admin/add_client/">Добавить клиента</a></li><li><a href="/iwaterTest/admin/list_clients/">Список клиентов</a></li><li><a href="/iwaterTest/admin/add_order/">Создать заказ</a></li><li><a href="/iwaterTest/admin/list_orders/">Список заказов</a></li><li><a href="/iwaterTest/admin/add_list/">Добавить путевой лист</a></li><li><a href="/iwaterTest/admin/logs/">Логи</a></li></ul>';
//		return $menu;

}

function side_menu()
{
    require(realpath($_SERVER["DOCUMENT_ROOT"]) . "/iwaterTest/template/layout/side_menu.php");
    return "";
}

function select_role()
{
    $out = "";
    $arr = func_get_args();
    $role = $arr[0];
    $dbh = connect_db();
    $cols = array('*');
    $table = 'iwater_roles';
    $res = select_db($dbh, $cols, $table);
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if ($r['id'] == $role) {
            $out .= '<option value="' . $r['id'] . '" selected>' . $r['name'] . '</option>';
        } else {
            $out .= '<option value="' . $r['id'] . '">' . $r['name'] . '</option>';
        }
    }

    return $out;

}

function select_driver($id = null)
{
    $dbh=connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    if($id!=null){
        $id = intval($id);
    }

//    $dbh = connect_db();
//    $cols = array('*');
//    $table = 'iwater_users';
//    $args = array(3);
//    $cond = array('role');
    //$res = select_db($dbh, $cols, $table, $args, $cond);
    $drNul = $dbh->query("SELECT d.id,u.name  FROM  `iwater_driver` as d LEFT JOIN `iwater_users` as u ON (d.id=u.id) WHERE d.company='$company' AND u.login ='nul'  ORDER BY u.name ASC");
    $res = $dbh->query("SELECT d.id,u.name  FROM  `iwater_driver` as d LEFT JOIN `iwater_users` as u ON (d.id=u.id) WHERE d.company='$company' ORDER BY u.name ASC");
    $d = $drNul->fetch(PDO::FETCH_ASSOC);
    $select = false;
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
            if ((intval($r['id'])) == $id) {
                $out .= '<option value="' . $d['id'] . '" selected>' . $d['name'] . '</option>';
                $out .= '<option value="' . $r['id'] . '" >' . $r['name'] . '</option>';
                $select = true;
            } else {
                $out .= '<option value="' . $r['id'] . '">' . $r['name'] . '</option>';
            }

    }
//    if($select == false){
//        $out .='<option value="99999" selected></option>';
//    }else{
//        $out .='<option value="99999"></option>';
//    }

    return $out;
}

function select_regions($id = null)
{
    $dbh=connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    $res = $dbh->query("SELECT *  FROM  `company_regions`  WHERE `company_id`='$company'");
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if((intval($r['id'])) == $id){
            $out .= '<option value="' . $r['name'] . '" selected>' . $r['name'] . '</option>';
            $select = true;
        }else{
            $out .= '<option value="' . $r['name'] . '">' . $r['name'] . '</option>';
        }
    }
    return $out;
}

function select_period()
{
    $dbh = connect_db();

    // Получение номера компании
    $res = select_db($dbh, array('company_id'), 'iwater_users', array($_SESSION['fggafdfc']), array('session'));
    $r = $res->fetch(PDO::FETCH_ASSOC);
    $company_id = $r['company_id'];

    $cols = array('period');
    $table = 'iwater_company';
    $args = array($company_id);
    $cond = array('id');
    $res = select_db($dbh, $cols, $table, $args, $cond);

    $r = $res->fetch(PDO::FETCH_ASSOC);

    // Чтение json объекта
    $periods = json_decode($r['period']);
    foreach ($periods as $key => $value) {
        if ($key == 0) {
            $out .= '<option value="' . $value->unit . '">' . $value->unit . '</option>';
        } else {
            $out .= '<option value="' . $value->unit . '">' . $value->unit . '</option>';
        }

    }

    return $out;
}

function checkbox_perms()
{
    $out = "";
    $dbh = connect_db();
    $cols = array('*');
    $table = 'iwater_perms';
    $res = select_db($dbh, $cols, $table);
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $out .= '<label class="checkbox-conteiner"><input type="checkbox" name="perms[]" value="' . $r['id'] . '" id="' . $r['id'] . '" class="checkbox" ><span class="checkbox-visual"></span><span class="checkbox-text">' . $r['desc'] . '</span></label>';
    }

    return $out;
}

function check_auth()
{

    $ok = false;
    $dbh = connect_db();
    $cols = array('count(`session`)', 'ban');
    $table = 'iwater_users';

    $session = "";
    if (isset($_SESSION['fggafdfc'])) { $session = $_SESSION['fggafdfc']; }

    $args = array($session);
    $cond = array('session');
    $res = select_db($dbh, $cols, $table, $args, $cond);
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if ($r['count(`session`)'] == 1 && $r['ban'] == 0) {
            $ok = true;
        }
    }
    return $ok;

}
function get_session_name(){
    $dbh = connect_db();
    $args = array($_SESSION['fggafdfc']);
    try {
        $res = $dbh->query(" SELECT `login`, `name` FROM `iwater_users` WHERE `session`='$args[0]'");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $name[0] =$r['login'];
        $name[1] = $r['name'];
    }
    return $name[0];

}

function get_session(){
    $dbh = connect_db();
    $args = array($_SESSION['fggafdfc']);
    try {
        $res = $dbh->query(" SELECT * FROM `iwater_users` WHERE `session`='$args[0]'");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $session = $r;
    }
    return $session;

}


function get_login()
{

    $dbh = connect_db();
    $cols = array('login');
    $table = 'iwater_users';
    $args = array($_SESSION['fggafdfc']);
    $cond = array('session');
    $res = select_db($dbh, $cols, $table, $args, $cond);
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $login = $r['login'];
    }
    return $login;

}

function check_perms($perm)
{

    $login = get_login();
    $dbh = connect_db();
    $extraSQL = '';

    try {
        $res = $dbh->query("SELECT `perms` FROM `iwater_users` AS u JOIN `iwater_roles` AS r ON (u.role=r.id) WHERE `login`='$login' LIMIT 1");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $r = $res->fetch(PDO::FETCH_ASSOC);
    $perms = json_decode($r['perms']);

    if (isset($_GET)) {
        $perm = explode("?", $perm);
        $perm = $perm[0];
        // Костыль, чтобы проходили гет запросы
    }

    try {
        $res = $dbh->query("SELECT * FROM `iwater_links` WHERE `desc` = '$perm' LIMIT 1");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $r = $res->fetch(PDO::FETCH_ASSOC);
    $level = $r['level']; // Уровень доступа необходимый для доступа (тавтология)
    $section = $r['section'] - 1; // Номер объекта, к которому прописаны доступы

    if ( $perms[$section] < $level ) {
        $ok = false;
    } else {
        $ok = $perms[$section];
    }
    return $ok;
}

function get_perms(){
    $login = get_login();
    $dbh = connect_db();
    $query = '';

    try {
        $res = $dbh->query("SELECT * FROM `iwater_users` AS u JOIN `iwater_roles` AS r ON(u.role=r.id) WHERE `login`='$login'");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $perms = json_decode($r['perms'], true);
    }
    foreach ($perms as $key => $value) {
        if ($key > 0) {
            $query .= ' OR (`section` = ' . $key . ' AND `level` <= ' . $value . ') ';
        } else {
            $query .= '`section` = ' . $key . ' AND `level` <= ' . $value;
        }
    }

    try {
        $res = $dbh->query("SELECT * FROM `iwater_links` WHERE " . $query);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $p_names[$r['desc']] = $r['desc'];
    }

    return $p_names;
}

function user_list_table()
{

    $dbh = connect_db();
    $out = "";

    //id компании, чтобы не видеть лишнего о других компаниях
    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE session = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    try {
        $res = $dbh->query("SELECT u.name, `phone`, `login`, r.id, u.id AS uid FROM `iwater_users` AS u JOIN `iwater_roles` AS r ON(u.role=r.id) WHERE `company_id` = '$company'");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $out .= '<tr class="head_table"><th>Имя</th><th>Телефон</th><th>Логин</th><th>Новый пароль</th><th>Роль</th><th></th><th></th></tr>';

    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if (check_perms('ban_users')) {
            $s_b = "<a href=\"/iwaterTest/backend.php?ban=$r[uid]\">Блокировать</a>";
        }

        $out .= '<tr><td><input name="names[]" type="text" value="' . $r['name'] . '"></td><td><input name="phones[]" type="text"  value="' . $r['phone'] . '"></td><td>' . $r['login'] . '</td><td><input name="passwords[]" type="password" value=""><input name="ids[]" type="hidden" value="' . $r['uid'] . '"></td><td><select name="roles[]">' . select_role($r['id']) . '</select></td><td>' . $s_b . '</td><td><a href="/iwaterTest/backend.php?delete_user=' . $r['uid'] . '">Удалить</a></td></tr>';
    }

    return $out;

}

function get_user_info($user_id)
{
    if ($user_id == "") {
        return null;
    }
    $dbh = connect_db();
    $info = array();
    try {
        $res = $dbh->query("SELECT id, name FROM `iwater_users` WHERE id='$user_id'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $info = $r;
    }
    return $info;
}

function get_date_list($list_id)
{
    $dbh = connect_db();
    $list_id = explode("?",$list_id);
    try {
        $res = $dbh->query("SELECT * FROM `iwater_lists` AS u WHERE map_num='$list_id[0]'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $date = $r['date'];
    }
    return $date;
}

function get_date()
{
    $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
    return date('j.m.Y', $date);
}

function echo_xlsx_list()
{
    $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
    $list_id = trim(filter_input(INPUT_POST, 'list_id', FILTER_SANITIZE_SPECIAL_CHARS));
    $drivers = trim(filter_input(INPUT_POST, 'count', FILTER_SANITIZE_SPECIAL_CHARS));
    $drivers = intval($drivers);
    for ($i = 0; $i < $drivers; $i++) {
        ?>
        <br>
        <div style="display: inline-block">Путевой лист водителя: <?php echo $_POST['driver_name_' . $i] ?> </div>
        <div style="display: inline-block">
            <a class="xlsx"
               href="/iwaterTest/files/<?php echo date('j.m.Y', $date) .'(driver)'. $_POST['driver_name_' . $i] . '.xlsx' ?>">
                <div style="display: none">
                    <div id="date"><?php echo $date ?></div>
                    <div id="driver_id"><?php echo $_POST['driver_id_' . $i] ?></div>
                    <div id="driver_name"><?php echo $_POST['driver_name_' . $i] ?></div>
                </div>
                XLSX</a>
            <a href="/iwaterTest/map/<?php echo $list_id ?>?driver_id=<?php echo $_POST['driver_id_' . $i] ?>"> Карта</a>
        </div>

    <?php }
}

function get_list_id()
{
    $list_id = trim(filter_input(INPUT_POST, 'list_id', FILTER_SANITIZE_SPECIAL_CHARS));
    return $list_id;
}

function get_log_attr()
{
    $attr = "logs";
    if (isset($_GET['logs'])) {
        if ($_GET['logs'] == "order") {
            $attr = "order";
        }
    }
    return $attr;
}

function get_date_in_map()
{
    $path = path();
    $date = get_date_list($path[3]);
    return date('j.m.Y', $date);
}

function get_filename()
{
    $driver_id = trim(filter_input(INPUT_GET, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
    $driver_info = get_user_info($driver_id);
    $file_name = get_date_in_map();
    if (isset($driver_info['name'])) {
        $file_name .= '(driver)'.$driver_info['name'];
    }
    $file_name .= ".xlsx";
    return $file_name;
}

function get_driver_info()
{
    $driver_id = trim(filter_input(INPUT_GET, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
    $driver_info = get_user_info($driver_id);
    return $driver_info;
}

function get_mkdate()
{
    $path = path();
    $date = get_date_list($path[3]);
    return $date;
}
function get_driver_id()
{
    $driver_id = trim(filter_input(INPUT_GET, 'driver_id', FILTER_SANITIZE_SPECIAL_CHARS));
    return  $driver_id;
}
function get_count_unchecked_order() {
    $dbh = connect_db();
    try {
        $res = $dbh->query("SELECT COUNT(o.id) AS count FROM `iwater_orders_app` AS o JOIN `iwater_users` AS u ON (o.company_id = u.company_id) WHERE `checked` = 0 AND `session` = '" . $_SESSION['fggafdfc'] . "'");
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }

    $r = $res->fetch(PDO::FETCH_ASSOC);
    return $r['count'];
}
function get_order_by_id($id){
    $dbh = connect_db();
    try {
        $res = $dbh->query("SELECT *, a.coords AS a_coords, o.id as o_id, o.client_id as o_client FROM `iwater_orders` as o
                            LEFT JOIN `iwater_addresses` as a ON o.address = a.address
                            WHERE  o.id='$id'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $response = $r;
    }
    if($response['o_client'] == "0"){
        return $response;
    }
    try {
        $res = $dbh->query("SELECT *, a.coords AS a_coords, o.id as o_id FROM `iwater_orders` as o
                            LEFT JOIN `iwater_addresses` as a ON o.address = a.address
                            WHERE o.client_id = a.client_id and o.id='$id'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $response = $r;
    }
    return $response;
}

function get_provider_by_id($id){
    $dbh = connect_db();
    try {
        $res = $dbh->query("SELECT * FROM `iwater_providers` WHERE `id` = '$id'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    $response = array();
    $i=0;
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $response[$i] = $r;
        $i++;
    }
    return $response;
}

function get_client_by_id($id){
    $dbh = connect_db();

    // Получение номера компании
    $res = select_db($dbh, array('company_id'), 'iwater_users', array($_SESSION['fggafdfc']), array('session'));
    $r = $res->fetch(PDO::FETCH_ASSOC);
    $company_id = $r['company_id'];
    try {
        $res = $dbh->query("SELECT c.id as c_id, a.id as a_id, c.type, c.name,c.client_id as client_id,c.id, a.region, a.address, a.coords, a.contact, c.contact AS phone  FROM `iwater_clients` AS c LEFT JOIN `iwater_addresses` AS a ON c.client_id=a.client_id WHERE c.id='$id' AND c.company_id='$company_id'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    $response = array();
    $i=0;
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $response[$i] = $r;
        $i++;
    }
    return $response;
}

function get_number_region($region)
{
    $regions = array('Санкт-Петербург', 'Колпино', 'Пушкин', 'Красное Село', 'Металлострой', 'Павловск', 'Шушары', 'Горелово', 'Коммунар', 'Стрельна', 'Петергоф', 'Ломоносов', 'Кронштадт', 'Ленинградская область');
    for($i=0;$i<count($regions);$i++){
        if($regions[$i]==$region){
            return $i;
            break;
        }
    }
    return null;
}
function get_regions_selected($id){
    $regions = array('Санкт-Петербург', 'Колпино', 'Пушкин', 'Красное Село', 'Металлострой', 'Павловск', 'Шушары', 'Горелово', 'Коммунар', 'Стрельна', 'Петергоф', 'Ломоносов', 'Кронштадт', 'Ленинградская область');
    $string = array();
    for($i=0;$i<count($regions);$i++){
        if($i!=$id) {
            $string .= '<option value="' . $regions[$i] . '">' . $regions[$i] . '</option>';
        }else{
            $string .= '<option value="' . $regions[$i] . '" selected>' . $regions[$i] . '</option>';
        }
    }
    return $string;
}

/** Сделай выгрузку из базы!!!
 */

function get_number_period($region)
{
    $regions = array('Утро', 'Первая половина дня','Середина дня', 'Рабочий день', 'Вторая половина рабочего дня', 'Вечер', 'Поздний вечер');
    for($i=0;$i<count($regions);$i++){
        if($regions[$i]==$region){
            return $i;
            break;
        }
    }
    return null;
}
function get_priod_selected($id){
    $regions = array('Утро', 'Первая половина дня','Середина дня', 'Рабочий день', 'Вторая половина рабочего дня', 'Вечер', 'Поздний вечер');
    $string = array();
    for($i=0;$i<count($regions);$i++){
        if($i!=$id) {
            $string .= '<option value="' . $regions[$i] . '">' . $regions[$i] . '</option>';
        }else{
            $string .= '<option value="' . $regions[$i] . '" selected>' . $regions[$i] . '</option>';
        }
    }
    return $string;
}

function get_status_selected($id){
    $id = intval($id);
    $regions = array('В работе','Отмена', 'Доставлен','Перенос');
    $string = array();
    for($i=0;$i<count($regions);$i++){
        if($i!=$id) {
            $string .= '<option value="' . $i . '">' . $regions[$i] . '</option>';
        }else{
            $string .= '<option value="' . $i . '" selected>' . $regions[$i] . '</option>';
        }
    }
    return $string;
}

class Field_calculate {
    const PATTERN = '/(?:\-?\d+(?:\.?\d+)?[\+\-\*\/])+\-?\d+(?:\.?\d+)?/';

    const PARENTHESIS_DEPTH = 10;

    public function calculate($input){
        if(strpos($input, '+') != null || strpos($input, '-') != null || strpos($input, '/') != null || strpos($input, '*') != null){
            //  Remove white spaces and invalid math chars
            $input = str_replace(',', '.', $input);
            $input = preg_replace('[^0-9\.\+\-\*\/\(\)]', '', $input);

            //  Calculate each of the parenthesis from the top
            $i = 0;
            while(strpos($input, '(') || strpos($input, ')')){
                $input = preg_replace_callback('/\(([^\(\)]+)\)/', 'self::callback', $input);

                $i++;
                if($i > self::PARENTHESIS_DEPTH){
                    break;
                }
            }

            //  Calculate the result
            if(preg_match(self::PATTERN, $input, $match)){
                return $this->compute($match[0]);
            }

            return 0;
        }

        return $input;
    }

    private function compute($input){
        $compute = create_function('', 'return '.$input.';');

        return 0 + $compute();
    }

    private function callback($input){
        if(is_numeric($input[1])){
            return $input[1];
        }
        elseif(preg_match(self::PATTERN, $input[1], $match)){
            return $this->compute($match[0]);
        }

        return 0;
    }
}

function get_settings()
{
    $dbh = connect_db();
    try {
        $res = $dbh->query("SELECT * FROM `iwater_settings`");
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    $i=0;
    $response = array();
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $response[$i] = $r;
        $i++;
    }
    return $response;
}

function get_info_by_driver($id){
    $dbh = connect_db();
    try {
        $res = $dbh->query("SELECT * FROM `iwater_moved_orders` WHERE `order_id`='$id'");

    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    $i=0;
    $response = array();
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $response[$i] = $r;
        $i++;
    }
    return $response;
}

function bool_to_russian($val){
    if($val == 0) return "Нет";
    else return "ДА";
}

function distance_driver_and_order($driver, $order){
    $driver = json_decode($driver);
    $order = explode(",",$order);

    $meters = true;
    $lat1 =$driver[1];
    $lng1 =$driver[0];
    $lat2 =$order[0];
    $lng2 =$order[1];


    $pi80 = M_PI / 180;
    $lat1 *= $pi80;
    $lng1 *= $pi80;
    $lat2 *= $pi80;
    $lng2 *= $pi80;

    $r = 6372.797; // mean radius of Earth in km
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;

    $response = ($meters ? ($km * 1000) : $km);
    if($response > 1000){
        return '<div title="Более 1 км до точки доставки" style="color: red">'.$response.'</div>';
    }else{
        return $response;
    }
}

function get_num_notice(){
    $dbh = connect_db();
    $session = get_session();
    try {
        $res = $dbh->query("SELECT COUNT(*) as count_notice
                                FROM `iwater_notice`
                                WHERE `dest_id`=".$session['id']."
                                AND `read` = 0");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        return "+".$r['count_notice'];
    }

}

function get_dimension($id = null){

    $dbh = connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    $res = $dbh->query("SELECT *  FROM  `iwater_dimension`  WHERE `id_company`='$company'");
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        if((intval($r['id'])) == $id){
            $out .= '<option value="' . $r['name'] . '" selected>' . $r['name'] . '</option>';

        }else{
            $out .= '<option value="' . $r['name'] . '">' . $r['name'] . '</option>';
        }
    }
    return $out;
}

function get_company_coords()
{
    $dbh = connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    try {
        $res = $dbh->query("SELECT * FROM `iwater_company` WHERE `id`='$company'");
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        return $r;
    }
}
function get_company()
{
    $dbh = connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    try {
        $res = $dbh->query("SELECT * FROM `iwater_company` WHERE `id`='$company'");
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
    while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        return $r['region'];
    }
}

function get_insert_id()
{
    $dbh = connect_db();

    $res = $dbh->query("SELECT * FROM `iwater_users` WHERE `session` = '" . $_SESSION['fggafdfc'] . "'");
    $usersess = $res->fetch();
    $company = $usersess['company_id'];

    try {
        $res = $dbh->query("SELECT `id` FROM `iwater_clients` ORDER BY id DESC LIMIT 1 ");
        $usersess = $res->fetch();
    } catch (Exception $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
    }
   // $insertId = $dbh->lastInsertId();
   // $r = $usersess['id'];

    return $usersess;

}