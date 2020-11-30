<?php

$company = get_company_coords();
$array = explode(',', $company['coords']);

$path = path();
$date = get_date_list($path[3]);
$driver_info = get_user_info($driver_id);
$file_name = date('j.m.Y', $date);
if (isset($driver_info['name'])) {
    $file_name .= '(driver)' . $driver_info['name'];
}
$file_name .= ".xlsx"; ?>
<div class="main">
    <form method="post" id="lists_on_map" action="/iwaterTest/backend.php">
        <input name="date" type="hidden" value=<?php echo date('j/m/Y', $date) ?>>
        <input name="add_list" type="hidden">
        <input name="extra_list_exist" type="hidden">
    </form>
    <script>
        $(function() {
            $('#back_to_list').click(function(event) {
                event.preventDefault();
                $('#lists_on_map').submit();
            });
        });
    </script>
    <div class="map_top_menu">
        <a id="back_to_list" href="/iwaterTest/admin/add_list/" style="margin-right: 15px;">К списку путевых листов </a>
        <a id="xlsx_map_link" style="cursor: pointer;" onclick="downloadList(<?php echo array_pop(path()); ?>, '', '', '', '');">Путевой лист на <?php echo date('j.m.Y', array_pop(path())); ?> </a>
    </div>

    <img src="../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;" />
    <div id="clients_list_table" class="clients_list_table">
        <div id="hidemap" style="display: inline-block">
        <!--    <div class="tank" hidden>
                <div class="label_tank">Кол-во тары на выделенных объектах: </div>
                <div class="num_tank">0</div>
            </div>
        -->
            <div id="map" style="width: 560px; height: 535px;"></div>
        </div>
        <div id="map_info">
            <table class="period_table">
                <tbody>
                    <tr>
                        <td>
                            <img src="../css/image/blue.png" style="max-height: 45px;">
                        </td>
                        <td>
                            Отображает метку закзаза. Иконка отображает статичный адрес для этого клиента
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img src="../css/image/changed.png">
                        </td>
                        <td>
                            Адрес заказа, водитель которого был изменён в меню карты.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img src="../css/image/time.png">
                        </td>
                        <td>
                            Заказ, адрес которого был выставлен вручную. Имеет статус "временного" адреса.
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <img src="../css/image/time_changed.png">
                        </td>
                        <td>
                            Адрес заказа с "врменным адресом", водитель которого был изменён.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img src="../css/image/black.png" style="max-height: 45px;">
                        </td>
                        <td>
                            Иконка выделенного заказа. Функционал выделения позволяет отслеживать кол-во тары для объектов и менять водителя. Для выделения заказа зажмите клавишу <b>Ctrl</b> и кликните по иконке заказаю
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <img src="../css/image/white.png" style="max-height: 45px;">
                        </td>
                        <td>
                            Ошибка формирования заказа. Некорректный временной период.
                        </td>
                    </tr>
            </table>
        </div>
        <div class="change_driver_info change_driver_info_2 inline-block" style="width: 980px;">
            <div class="change_driver_infoheader" onmousedown="dragMouseDown(event);" style="width: 32px; height: 32px; background-image: url('/test/css/image/move_icon.png'); float: right; margin-bottom: 7px;"></div>
            <table style="width: 980px;">
                <tr>
                    <td>
                        <div class="label_selected inline-block">Выделено точек:
                            <div class="num_selected">0</div>
                        </div>
                        <div class="inline-block" style="min-width: 260px;">
                            <div style="margin-left: 15px;" class="inline-block label_tank">Тары на выделенных объектах: </div>
                            <div style="font-size: 13px !important;" class="num_tank">0</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="6"> </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <div class="inline-block">
                            <label for="driver" style="font-size: 17px;">&nbsp;Создать путевой лист</label>
                        </div>
                        <div class="inline-block" style="float:right;">
                            <input class="classic" id="create_list_driver" name="create_list_driver" type="button" value="Создать" onclick="createListDriver();">
                        </div>
                        <div class="inline-block" style="float:right;">
                            <input type="button" id="route" value="Маршрут">
                        </div>
                        <div class="inline-block" style="float:right;">
                            <input type="button" id="change_lists_state" onclick="changeListState();" value="Переключить точки">
                        </div>
                        <div class="inline-block" style="float:right;">
                            <input type="button" id="change_lists_state" onclick="unselectAllPoints();" value="Снять выделение">
                        </div>
                        <div class="inline-block" style="float:right;">
                            <input type="button" id="change_lists_state" onclick="selectAllPoints();" value="Выделить все точки">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <hr color="#e3e9f1" size="1px" width="100%">
                    </td>
                </tr>
                <tr class="header">
                    <td style="font-size: 10px;">Показать на карте</td>
                    <td style="font-size: 10px;">№ путевого листа</td>
                    <td style="font-size: 18px;">Водитель</td>
                    <td>Всего бутылей</td>
                    <td>Кол-во точек</td>
                    <td>Порядковый номер</td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div>

<script type="text/javascript">
    var driver_id2 = '<?php echo $driver_id != "" ? $driver_id : ""; ?>';
    var periods_string = $(".period_table").html();

    var myMap, myMarks = [], infowindow = [], infowindowlast, myCollection, onStartDrag; // Карта
    var periods, colors;

    var color_drawed = false; // Когда перерисовывается карта, нужны чтобы подсказки справа оставались нетронутыми

    var count_bubles = 0, count_tanks = 0;
    var selectedMarks = [];

    function initMap() {
        // init();
        update_driver_info()
    }

    function init() {
        myMap = new google.maps.Map(document.getElementById('map'), {
            center: {lat: <?php echo $array[0]; ?>, lng: <?php echo $array[1]; ?>},
            zoom: 10
        });

        // Загрузка цветов
        $.ajax({
            type: 'POST',
            data: { select_period: "" },
            response: "json",
            url: '/iwaterTest/backend.php',
            success: function(req) {
                var list = JSON.parse(req);
                colors = list['color'];
                colors = JSON.parse(colors);

                // Загрузка периодов
                $.ajax({
                    type: "GET",
                    data: { period_list: "" },
                    url: "/iwaterTest/backend.php",
                    success: function(req) {
                        periods = JSON.parse(req);

                        if (!color_drawed) {
                            for (var i = 0; i < periods.length; i++)
                                periods_string += '<tr><td><img src="../css/image/' + colors[i]['unit'] + '.png" style="max-height: 45px;"></td><td>' + periods[i] + '</td></tr>';

                            periods_string += '</tbody>';
                            $(".period_table").html(periods_string);

                            color_drawed = true;
                        }
                    
                        $('#hidemap').fadeIn();

                        var checked = $('.change_driver_info tr td:first-child input:checkbox:not(:checked)');

                        var checked_class = [];
                        for (var i = 0; i < checked.length; i++) {
                            checked_class.push(checked[i].className);
                        }
                        $.ajax({
                            type: "POST",
                            data: {
                                list_p: '<?php echo $path[3]; ?>',
                                driver_id: '<?php echo $driver_id2; ?>',
                                exception_list: checked_class
                            },
                            url: "/iwaterTest/backend.php",
                            success: function(req) {
                                if (myMarks.length == 0)
                                {
                                    console.log('Ноль', req) 
                                    request(req);
                                }
                                else 
                                {
                                    console.log('More', req);
                                    myMarks = [];
                                    request(req);
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    function request(req) {
        count_tanks = 0, count_bubles = 0;
        var rows = req.getElementsByTagName('rows')[0];
        l = rows.getElementsByTagName('row').length;
        for (var i = 0; i < rows.children.length; i++) {
            if (typeof(rows.getElementsByTagName('row')[i].id) != "undefined")
                if (rows.getElementsByTagName('row')[i].id == "file_name")
                    break;
        
            var request = rows.getElementsByTagName('row')[i];
            var coords = (request.getElementsByTagName("cell")[0].childNodes[0].nodeValue).split(',');
            var time = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
            var tank_b = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
            var client_id = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;
            var period = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
            var order_id = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;
            var driver_name = request.getElementsByTagName("cell")[6].childNodes[0].nodeValue;
            var changed_driver = request.getElementsByTagName("cell")[7].childNodes[0].nodeValue;
            var preset = changed_driver == 0 ? ['islands#greenDotIconWithCaption', 'islands#blueIcon'] : ['islands#blueCircleDotIconWithCaption', 'islands#blueCircleIcon'];

            var color = colors[periods.indexOf(period)]['unit'];
            var gooCoord = new google.maps.LatLng(coords[0], coords[1])
            var image = {
                url: '../css/image/' + color + '.png',
                scaledSize: new google.maps.Size(40, 40),
                labelOrigin: new google.maps.Point(20, 16)
            };
            var label = {
                text: i + 1 + "",
                color: "#505050",
                fontSize: "13px",
                fontFamily: "Arial",
                fontWeight: "bold"
            };

            if (request.getElementsByTagName("cell")[0].className == "temp") {
                myPlacemark = new google.maps.Marker({
                    position: gooCoord,
                    map: myMap,
                    icon: image,
                    icon_b: image, // чтобы после выделения не потерять изначальный цвет
                    label: label,
                    data_id: client_id,
                    tank: tank_b,
                    changedDriver: changed_driver,
                    selected: 0,
                    order_id: order_id,
                    title: 'Временный адрес. Время: ' + time + ' Период: ' + period + ' Тары: ' + tank_b + ' Клиент: ' + client_id + ' Водитель: ' + driver_name,
                    draggable: true,
                    optimized: false
                });
                myMarks.push(myPlacemark);

                if (typeof infowindow[order_id] === 'undefined') 
                    infowindow[order_id] = new google.maps.InfoWindow({
                        content: 'Временный адрес<br>Время: ' + time + '<br> Период: ' + period + '<br> Кол-во бутылок(тары): ' + tank_b + '<br> Номер клиента: ' + client_id + '<br> Водитель: ' + driver_name
                    });
            } else {
                myPlacemark = new google.maps.Marker({
                    position: gooCoord,
                    map: myMap,
                    icon: image,
                    icon_b: image, // чтобы после выделения не потерять изначальный цвет
                    label: label,
                    data_id: client_id,
                    tank: tank_b,
                    changedDriver: changed_driver,
                    selected: 0,
                    order_id: order_id,
                    title: 'Время: ' + time + ' Период: ' + period + ' Тары: ' + tank_b + ' Клиент: ' + client_id + ' Водитель: ' + driver_name,
                    draggable: true,
                    optimized: false
                });
                myMarks.push(myPlacemark);

                if (typeof infowindow[order_id] === 'undefined') 
                    infowindow[order_id] = new google.maps.InfoWindow({
                        content: 'Время: ' + time + '<br> Период: ' + period + '<br> Кол-во бутылок(тары): ' + tank_b + '<br> Номер клиента: ' + client_id + '<br> Водитель: ' + driver_name
                    });
            }

            myPlacemark.addListener('click', function(e) {
                var idMark = this.order_id;
                var selected_image = {
                    url: '../css/image/black.png',
                    scaledSize: new google.maps.Size(40, 40),
                    labelOrigin: new google.maps.Point(20, 16)
                };
                
                if (infowindowlast)
                    infowindowlast.close();
                infowindowlast = infowindow[this.order_id];

                var e_tag_name = Object.keys(e);
                e_tag_name.splice(e_tag_name.indexOf("pixel"), 1);
                e_tag_name.splice(e_tag_name.indexOf("latLng"), 1);

                if (typeof e_tag_name[1].ctrlKey === "undefined")
                    e_tag_name = e_tag_name[0];
                else
                    e_tag_name = e_tag_name[1];

                console.log("Finded key = ", e_tag_name);

                if (e[e_tag_name].ctrlKey || e[e_tag_name].shiftKey) {
                    if (selectedMarks.indexOf(idMark) >= 0) {
                        delete selectedMarks[selectedMarks.indexOf(idMark)];
                        this.setIcon(this.icon_b);

                        $('.num_selected').html(count_bubles -= 1);
                        $('.num_tank').html(count_tanks -= (this.tank - 1 + 1));
                    } else {
                        selectedMarks.push(idMark);
                        this.setIcon(selected_image);

                        $('.num_selected').html(count_bubles += 1);
                        $('.num_tank').html(count_tanks += (this.tank - 1 + 1));
                    }
                } else {
                    infowindow[this.order_id].open(myMap, this);
                }

            });

            myPlacemark.addListener('dragend', function() {
                var result = confirm("Отметка на карте была передвинута, вы хотите сохранить новые координаты для этого заказа?");
                if (result) {
                    var cords = [this.getPosition().lat(), this.getPosition().lng()];
                    var id = this.data_id;

                    var order_id = this.order_id;
                    $.ajax({
                        type: "POST",
                        data: {
                            client_id: id,
                            order_id: order_id,
                            change_coords_in_list: cords
                        },
                        url: "/iwaterTest/backend.php",
                        success: function(req) {
                        }
                    });
                } else {
                    var time_result = confirm("Переместить отметку на временную позицию?");
                    if (!time_result) {
                        var oldCoord = new google.maps.LatLng(onStartDrag[0], onStartDrag[1])
                        this.setPosition(oldCoord);
                    }
                }
            });

            myPlacemark.addListener("dragstart", function() {
                onStartDrag = [this.getPosition().lat(), this.getPosition().lng()];
            });
        }
    }

    // Обновление данных в таблице под картой
    function update_driver_info() {
        var driver_id = "<?php echo $driver_id ?>";
        var get = <?php echo json_encode($_GET); ?>;
        var cancelDrivers = [];

        if (driver_id == "") driver_id = "all";

        $.each(get, function(key, value) {
            if (key != "driver_id") {
                cancelDrivers.push(value);
            }
        });

        // Получение информации о путевом листе
        $.ajax({
            url: "/iwaterTest/backend.php",
            type: "POST",
            dataType: "json",
            data: {
                driver_map_info: "",
                date: document.location.pathname.split('/')[3],
                driver: driver_id
            },
            success: function(data) {
                var html = "";
                var cancel = false;
                var driver_list = [];
                for (var i = 0; i < data.length; i++) {
                    html += "<tr>";
                    var driver_num = data[i][6];
                    if (driver_num.includes('(driver)')) {
                        driver_num = data[i][6].split('.')[2];
                        driver_num = driver_num.slice(-1);
                    } else {
                        driver_num = 1;
                    }

                    for (var j = 0; j < cancelDrivers.length; j++) {
                        if (parseInt(cancelDrivers[j]) == parseInt(data[i][4])) {
                            cancel = true;
                        }
                    }
                    if (cancel == false) {
                        html += '<td onclick="init()" class="list_checkbox" style="text-align: center;padding-top: 3px;"><input id="' + data[i][4] + '" class="' + data[i][0] + '" type="checkbox" checked></td>';
                    } else {
                        html += '<td onclick="init()" class="list_checkbox" style="text-align: center;padding-top: 3px;"><input id="' + data[i][4] + '" class="' + data[i][0] + '" type="checkbox"></td>';
                    }
                    html += "<td>" + data[i][0] + "</td>";
                    html += '<td><select id="driver" name="driver" onchange="updateListDriver( this );"><?php echo select_driver(); ?>' + data[i][5] + '</select></td>';
                    html += "<td>" + data[i][2] + "</td>";
                    html += "<td>" + data[i][3] + "</td>";
                    html += '<td><input type="edit" name="list_number_' + i + '" id="num" value="' + driver_num + '" style="width:110px;" onchange="updateListDriver( this );"></input></td>';
                    html += "</tr>";
                    cancel = false;
                    $("#driver").val(80).change();

                }
                $(html).insertAfter($(".change_driver_info  table tr:last"));
                init();

            }
        });
    }

    // Выделение всех точек
    function selectAllPoints() {
        var selected_image = {
            url: '../css/image/black.png',
            scaledSize: new google.maps.Size(40, 40),
            labelOrigin: new google.maps.Point(20, 16)
        };

        selectedMarks = [];
        $('.num_selected').html('0');
        $('.num_tank').html('0');
        count_bubles = 0;
        count_tanks = 0;

        for (var i = 0; i < myMarks.length; i++) {
            selectedMarks.push(myMarks[i].order_id);
            myMarks[i].setIcon(selected_image);

            $('.num_selected').html(count_bubles += 1);
            $('.num_tank').html(count_tanks += (myMarks[i].tank - 1 + 1));
        }
    }

    // Снять выделение всех точек
    function unselectAllPoints() {
        for (var i = 0; i < myMarks.length; i++) {
            myMarks[i].setIcon(myMarks[i].icon_b);
        }

        selectedMarks = [];
        count_bubles = 0;
        count_tanks = 0;
        $('.num_selected').html('0');
        $('.num_tank').html('0');
    }

    // Обновление информации на карте
    function update_map(el) {
        var checked = $('.change_driver_info tr td:first-child input:checkbox:not(:checked)');
        var checked_ids = [];
        for (var i = 0; i < checked.length; i++) {
            checked_ids.push(checked[i].id);
        }
        $.ajax({
            type: "POST",
            data: {
                list_p: '<?php echo $path[3]; ?>',
                driver_id: '<?php if ($driver_id != "") {
                                echo $driver_id;
                            } ?>',
                exception_driver: checked_ids
            },
            url: "/iwaterTest/backend.php",
            success: function(req) {
                request(req);
            }
        });
    }

    // Запись нового путевого листа
    function change_driver() {
        var count = myCollection.toArray().length;
        var orders_to_change = [];
        for (var i = 0; i < count; i++) {
            if (myCollection.toArray()[i].properties._data.selected == 1) {
                myCollection.toArray()[i].properties._data.changedDriver = 1;
                orders_to_change.push(myCollection.toArray()[i].properties._data.order_id);
            }
        }
        $.ajax({
            url: "/iwaterTest/backend.php",
            type: "POST",
            dataType: "json",
            data: {
                change_driver_in_map: "",
                orders: orders_to_change
            },
            success: function(data) {
                var x = window.location.toString();
                if (x.indexOf('?cancel') + 1 == 0 && x.indexOf('?driver_id') + 1 == 0) {
                    x += "?cancel" + $("#driver").val() + "=" + $("#driver").val();
                } else {
                    x += "&cancel" + $("#driver").val() + "=" + $("#driver").val();
                }
                window.location = x;
            }
        });
    }

    // Создание путевого листа без водителя
    function createListDriver(ev) {
         $.ajax({
            url: "/iwaterTest/backend.php",
            type: "POST",
            dataType: "json",
            data: {
                create_no_man_list: selectedMarks.join(),
                date: document.location.pathname.split('/')[3]
            },
            success: function(data) {
                location.reload();
            }
        });
    }

    // Присвоение путевого листа водителю
    function updateListDriver(ev) {
        var list_id = ev.parentNode.parentNode.childNodes[1].innerHTML;
        var driver = ev.parentNode.parentNode.childNodes[2].childNodes[0].value;
        var number = ev.parentNode.parentNode.childNodes[5].getElementsByTagName('input')[0].value;

        $.ajax({
            url: "/iwaterTest/backend.php",
            type: "POST",
            dataType: "json",
            data: {
                update_list_driver: list_id,
                driver: driver,
                date: document.location.pathname.split('/')[3],
                number: number
            },
            success: function(data) {
                location.reload();
            }
        });
    }

    // Генерация и выгрузка путевого листа в Excel
    function downloadList(date, driver_id, driver_n, list, file_name) {
        $('.loading').show();

        $.ajax({
            type: "POST",
            data: {
                createExcell: "",
                date: date,
                driver_id: driver_id,
                driver_n: driver_n,
                list: list,
                file_name: file_name
            },
            url: "/iwaterTest/backend.php",
            success: function(req) {
                $('.loading').hide();
                location.href = '/iwaterTest/files/' + req;
            }
        });
    }

    // Сбросить все чекбоксы в списке
    function changeListState() {
        firstState = $('.list_checkbox:first > input:checkbox').is(':checked');

        $('.list_checkbox > input:checkbox').prop('checked', !firstState);

        init()
    }

    // Ручное перемещение таблицы с водителями
    var pos3 = 0,
        pos4 = 0;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();

        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();

        // Стартовые позиции блока
        var startPositionX = $('.change_driver_info').offset().top;
        var startPositionY = $('.change_driver_info').offset().left;

        // Перемещение блока
        $('.change_driver_info').offset({
            top: startPositionX - (pos4 - e.clientY),
            left: startPositionY - (pos3 - e.clientX)
        });

        pos3 = e.clientX;
        pos4 = e.clientY;
    }

    closeDragElement = () => { document.onmouseup = null; document.onmousemove = null; }
</script>

    <style media="screen">
        .label_selected {
            margin: 0px 0px 6px 15px;
            font-size: 14px;
        }

        .map_top_menu {
            display: flex;
            padding: 5px;
        }

        .map_top_menu a {
            font-size: 12px;
            color: #000;
            text-decoration: none;
        }

        .period_table {
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
        }

        .period_table tr {
            min-width: 500px;
        }

        .change_driver_info_2 td {
            padding: 2px 35px;
            border: 0px;
        }

        .change_driver_info_2 table {
            border: 0px;
            background-color: #fff;
            padding: 5px;
            border-radius: 10px;
        }

        #create_list_driver {
            width: 115px;
            height: 25px;
            color: #fff;
            border-radius: 5px;
            background-color: #015aaa;
            border-color: #e3e9f1;
            border-style: solid;
        }

        #change_lists_state {
            width: 160px;
            height: 25px;
            color: #fff;
            border-radius: 5px;
            background-color: #015aaa;
            border-color: #e3e9f1;
            border-style: solid;
        }

        #route {
            width: 115px;
            height: 25px;
            color: #fff;
            border-radius: 5px;
            background-color: #015aaa;
            border-color: #e3e9f1;
            border-style: solid;
        }

        #hidemap {
            width: 569px;
        }
    </style>