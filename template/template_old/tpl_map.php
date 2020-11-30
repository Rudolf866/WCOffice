<?php
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
        <a id="back_to_list" href="/iwaterTest/admin/add_list/" style="margin-right: 15px;">К списку путевых листов</a>
        <a id="xlsx_map_link" style="cursor: pointer;" onclick="downloadList(<?php echo array_pop(path()); ?>, '', '', '', '');">Путевой лист на <?php echo date('j.m.Y', array_pop(path())); ?> </a>
    </div>

    <img src="../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;" />
    <div id="clients_list_table" class="clients_list_table">
        <div id="hidemap" style="display: inline-block">
            <div class="tank" hidden>
                <div class="label_tank">Кол-во тары на выделенных объектах: </div>
                <div class="num_tank">0</div>
            </div>
            <div id="map" style="width: 560px; height: 535px;"></div>
        </div>
        <div id="map_info">
            <table class="period_table">
                <tbody>
                    <tr>
                        <td>
                            <img src="../css/image/blue.png">
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
                            <img src="../css/image/black.png">
                        </td>
                        <td>
                            Иконка выделенного заказа. Функционал выделения позволяет отслеживать кол-во тары для объектов и менять водителя. Для выделения заказа зажмите клавишу <b>Ctrl</b> и кликните по иконке заказаю
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <img src="../css/image/white.png">
                        </td>
                        <td>
                            Ошибка формирования заказа. Некорректный временной период.
                        </td>
                    </tr>
            </table>
        </div>
        <div class="change_driver_info change_driver_info_2 inline-block" style="width: 980px;">
            <div class="change_driver_infoheader" onmousedown="dragMouseDown(event);" style="width: 32px; height: 32px; background-image: url('/test/css/image/move_icon.png'); float: right; margin-bottom: 7px;"></div>
            <div class="label_selected inline-block">Выделено точек: </div>
            <div class="num_selected inline">0</div>
            <table style="width: 980px;">
                <tr>
                    <td style="display: none">
                        <div class="label_selected inline-block">Выделено точек:
                            <div class="num_selected">0</div>
                        </div>
                        <div class="inline-block">
                            <div style="margin-left: 20px;" class="inline-block label_tank">Тары на выделенных объектах: </div>
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
                            <label for="driver" style="font-size: 17px;">&nbsp;Создать новый путевой лист</label>
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
        <!-- <div class="compare_num_order inline-block">
            <table>
                <tr class="header">
                    <td>№ в путевом листе</td>
                    <td>№ по-порядку</td>
                </tr>
            </table>
        </div>
        <div id="route_div" class="">
            <div id="open" style="cursor: pointer">&#9660; Текстовый маршрут</div>
            <div id="text_route">
            </div>
        </div> -->
    </div>
</div>
<div>

    <script src="/iwaterTest/inc/js/route.js" type="text/javascript"></script>
    <script type="text/javascript">
        var driver_id2 = '<?php if ($driver_id != "") {
                                echo $driver_id;
                            } else {
                                echo "";
                            } ?>';
        var periods_string = $(".period_table").html();

        var myMap;
        var myCollection;
        var onStartDrag; //хранит позицию метки до начала перемещения
        var periods, colors;

        var selectBubles = [];
        var routes = [];

        var group = false;
        var map_flag = false;
        var route_copmplete = false;
        var color_drawed = false; // Когда перерисовывается карта, нужны чтобы подсказки справа оставались нетронутыми

        var count_bubles = 0;
        var count_list = 0;
        var count_tanks = 0;

        function init() {
            console.log('Start draw map!');

            if (myMap) {
                myMap.destroy();
                console.log('Old map destroyed!');
            }

            /**
             * Подгрузка цветов из базы
             * Меняет @colors в соотсветствие с данными
             */
            $.ajax({
                type: 'POST',
                data: {
                    select_period: ""
                },
                response: "json",
                url: '/iwaterTest/backend.php',
                success: function(req) {
                    var list = JSON.parse(req);
                    colors = list['color'];
                    colors = JSON.parse(colors);

                    /**
                     * Подгрузка периоды из базы
                     * Меняет @period в соотсветствие с данными
                     */
                    $.ajax({
                        type: "GET",
                        data: {
                            period_list: ""
                        },
                        url: "/iwaterTest/backend.php",
                        success: function(req) {
                            periods = JSON.parse(req);

                            if (!color_drawed) {
                                for (var i = 0; i < periods.length; i++) {
                                    periods_string += '<tr><td><img src="../css/image/' + colors[i]['unit'] + '.png"></td><td>' + periods[i] + '</td></tr>';
                                }

                                periods_string += '</tbody>';
                                $(".period_table").html(periods_string);

                                color_drawed = true;
                            }
                        }
                    });

                    myMap = new ymaps.Map("map", {
                        center: [59.93, 30.31],
                        zoom: 10
                    });
                    pickout_button = new ymaps.control.Button({
                        data: {
                            content: 'Выделить всё'
                        },
                        options: {
                            selectOnClick: false
                        }
                    });
                    cancel_pick_button = new ymaps.control.Button({
                        data: {
                            content: 'Отменить',
                            title: 'Сбросить выделенные объекты'
                        },
                        options: {
                            selectOnClick: false
                        }
                    });

                    print_button = new ymaps.control.Button({
                        data: {
                            content: 'Печать'
                        }
                    });
                    if (driver_id2 != "") {
                        numeric_button = new ymaps.control.Button({
                            data: {
                                content: "№ по порядку"
                            }
                        });
                        numeric_button.events.add('mouseup', function(e) {
                            change_numeration(numeric_button.state.get('selected'));
                        });
                        myMap.controls.add(numeric_button, {
                            float: 'none',
                            position: {
                                bottom: 80,
                                right: 10
                            },
                            maxWidth: 180
                        });
                    }
                    if (map_flag == true) {
                        myMap.container.enterFullscreen()
                    }

                    if (typeof(print_button.state.get('selected')) == "undefined") {
                        print_button.state.set('selected', false);
                    }
                    myCollection = new ymaps.GeoObjectCollection();

                    print_button.events.add('mouseup', function(e) {
                        callPrint();
                    });
                    pickout_button.events.add('mouseup', function(e) {
                        pick_out_all();
                    });
                    cancel_pick_button.events.add('mouseup', function(e) {
                        cancel_pick_out_all();
                    });
                    myMap.controls.add(print_button, {
                        float: 'right',
                        maxWidth: 100
                    });
                    myMap.controls.add(pickout_button, {
                        float: 'right',
                        maxWidth: 120
                    });
                    myMap.controls.add(cancel_pick_button, {
                        float: 'right',
                        maxWidth: 120
                    });

                    myMap.events.add('sizechange', function() {
                        if (map_flag == true) {
                            $(".change_driver_info").css({
                                "position": "inherit",
                                "color": "#807e7e"
                            });
                            $(".tank").css({
                                "position": "inherit",
                                "color": "#807e7e"
                            });
                        } else {
                            $(".change_driver_info").css({
                                "position": "absolute",
                                "color": "black",
                                "right": "10px",
                                "z-index": "100001",
                                "background-color": "whitesmoke",
                                "padding": "10px",
                                "border-radius": "3px",
                                "min-width": "400px"
                            });
                            $(".tank").css({
                                "position": "absolute",
                                "color": "black",
                                "top": "2%",
                                "z-index": "100001",
                                "left": "28%",
                                "font-weight": "bolder"
                            });
                        }
                        map_flag ^= true;
                    });

                    $('#hidemap').fadeIn();

                    var checked = $('.change_driver_info tr td:first-child input:checkbox:not(:checked)');

                    var checked_class = [];
                    for (var i = 0; i < checked.length; i++) {
                        checked_class.push(checked[i].className);
                    }
                    $.ajax({
                        type: "POST",
                        data: {
                            list_p: '<?php $path = path();
                                        echo $path[3]; ?>',
                            driver_id: '<?php if ($driver_id != "") {
                                            echo $driver_id;
                                        } ?>',
                            exception_list: checked_class
                        },
                        url: "/iwaterTest/backend.php",
                        success: function(req) {
                            request(req);
                        }
                    });
                }
            });
        }

        function request(req) {
            var count_tanks = 0;
            var count_bubles = 0;
            var rows = req.getElementsByTagName('rows')[0];
            l = rows.getElementsByTagName('row').length;
            for (var i = 0; i < rows.children.length; i++) {
                if (typeof(rows.getElementsByTagName('row')[i].id) != "undefined") {
                    if (rows.getElementsByTagName('row')[i].id == "file_name") {
                        break;
                    }
                }
                var request = rows.getElementsByTagName('row')[i];
                var coords = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
                var time = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
                var tank_b = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
                var client_id = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;
                var period = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
                var order_id = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;
                var driver_name = request.getElementsByTagName("cell")[6].childNodes[0].nodeValue;
                var changed_driver = request.getElementsByTagName("cell")[7].childNodes[0].nodeValue;
                var preset = [];
                if (changed_driver == 0) {
                    preset[0] = 'islands#greenDotIconWithCaption';
                    preset[1] = 'islands#blueIcon';
                } else {
                    preset[0] = 'islands#blueCircleDotIconWithCaption';
                    preset[1] = 'islands#blueCircleIcon';
                }

                var color = colors[periods.indexOf(period)]['unit'];
                coords = coords.split(',');

                if (request.getElementsByTagName("cell")[0].className == "temp") {
                    myPlacemark = new ymaps.Placemark([coords[0], coords[1]], {
                        iconCaption: (i + 1),
                        iconContent: i + 1,
                        excelNum: i + 1,
                        hintContent: 'Временный адрес<br> Время: ' + time + '<br> Период: ' + period + '<br> Тары: ' + tank_b + '<br> №: ' + client_id + '<br> Водитель: ' + driver_name,
                        balloonContent: 'Временный адрес<br>Время: ' + time + '<br> Период: ' + period + '<br> Кол-во бутылок(тары): ' + tank_b + '<br> Номер клиента: ' + client_id + '<br> Водитель: ' + driver_name,
                        data_id: client_id,
                        tank: tank_b,
                        selected: 0,
                        changedDriver: changed_driver,
                        defaultColor: color,
                        order_id: order_id,
                    }, {
                        preset: preset[0],
                        draggable: true,
                        iconColor: color
                    });
                } else {
                    myPlacemark = new ymaps.Placemark([coords[0], coords[1]], {
                        iconContent: i + 1,
                        excelNum: i + 1,
                        hintContent: 'Время: ' + time + '<br> Период: ' + period + '<br> Тары: ' + tank_b + '<br> №: ' + client_id + '<br> Водитель: ' + driver_name,
                        balloonContent: 'Время: ' + time + '<br> Период: ' + period + '<br> Кол-во бутылок(тары): ' + tank_b + '<br> Номер клиента: ' + client_id + '<br> Водитель: ' + driver_name,
                        data_id: client_id,
                        tank: tank_b,
                        selected: 0,
                        changedDriver: changed_driver,
                        defaultColor: color,
                        order_id: order_id
                    }, {
                        preset: preset[1],
                        draggable: true,
                        iconColor: color
                    });
                }
                myCollection.add(myPlacemark);
                myMap.geoObjects.add(myCollection);

                myPlacemark.events.add("dragstart", function(e) {
                    onStartDrag = e.get('target').geometry.getCoordinates();
                });

                myPlacemark.events.add("dragend", function(e) {
                    var result = confirm("Отметка на карте была передвинута, вы хотите сохранить новые координаты для этого заказа?");
                    if (result) {
                        var cords = e.get('target').geometry.getCoordinates();
                        var id = e.get('target').properties._data.data_id;

                        var order_id = e.get('target').properties._data.order_id;
                        $.ajax({
                            type: "POST",
                            data: {
                                client_id: id,
                                order_id: order_id,
                                change_coords_in_list: cords
                            },
                            url: "/iwaterTest/backend.php",
                            success: function(req) {
                                //request(req);
                            }
                        });
                    } else {
                        var time_result = confirm("Переместить отметку на временную позицию?");
                        if (!time_result) {
                            this.geometry.setCoordinates(onStartDrag);
                        }
                    }
                }, myPlacemark);

                myPlacemark.events.add("click", function(e) {
                    if (group == true) {
                        var target = e.get('target');
                        if (target.properties._data.selected == 0) {
                            target.properties._data.selected = 1;
                            target.options.set('iconColor', 'black');
                            window.count_tanks += parseInt(target.properties._data.tank);
                            window.count_bubles++;
                        } else {
                            target.properties._data.selected = 0;
                            target.options.set('iconColor', target.properties._data.defaultColor);
                            window.count_tanks -= parseInt(target.properties._data.tank);
                            window.count_bubles--;
                        }
                        $(".num_tank").text(window.count_tanks);
                        $(".num_selected").text(window.count_bubles);
                    }
                }, myPlacemark);
            }

            $("#route").click(function() {
                create_route(myMap, myCollection, routes);
                route_copmplete = true;
            })
        }

        $(function() {
            $('#route_div #text_route').hide();
            $('#route_div #open').click(function() {
                $(this).next().slideToggle();
            });
            $("#xlsx_map_link").click(function(event) {
                event.preventDefault();
                $('.loading').show();
                $.ajax({
                    type: "POST",
                    data: {
                        createExcell: "",
                        date: document.location.pathname.split('/')[3],
                        driver_id: "<?php echo $driver_info['id'] ?>",
                        driver_n: "<?php echo $driver_info['name'] ?>"

                    },
                    url: "/iwaterTest/backend.php",
                    success: function(req) {
                        $('.loading').hide();
                        window.downloadFile('/iwaterTest/files/' + req);
                    }
                });
            });

            $(this).keydown(function(e) {
                if (e.keyCode == 17) {
                    {
                        group = true;
                        myMap.geoObjects.options.set({
                            openBalloonOnClick: false
                        });
                        $(".tank").show();
                    }
                }
            });
            $(this).keyup(function(e) {
                if (e.keyCode == 17) {
                    {
                        group = false;
                        myMap.geoObjects.options.set({
                            openBalloonOnClick: true
                        })
                    }
                }
            });

            update_driver_info();


        });

        /**
         * Полноэкранный режим карты
         */
        function pick_out_all() {
            window.count_tanks = 0;
            window.count_bubles = 0;
            $(".tank").show();
            myCollection.each(function(el) {
                el.properties._data.selected = 1;
                el.options.set('iconColor', 'black');
                window.count_tanks += parseInt(el.properties._data.tank);
                window.count_bubles++;
            });
            $(".num_tank").text(count_tanks);
            $(".num_selected").text(count_bubles);
        }

        /**
         * Убрать полноэкранный режим
         */
        function cancel_pick_out_all() {
            $(".tank").hide();
            window.count_tanks = 0;
            window.count_bubles = 0;
            myCollection.each(function(el) {
                el.properties._data.selected = 0;
                el.options.set('iconColor', el.properties._data.defaultColor);

            });
            window.count_tanks = 0;
            window.count_bubles = 0;
            $(".num_tank").text(window.count_tanks);
            $(".num_selected").text(window.count_bubles);
        }

        /**
         * Метод обновление данных в таблице под картой
         */
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

            /**
             * Получение и вывод информации о путевом листе в таблице
             */
            $.ajax({
                url: "/iwaterTest/backend.php",
                type: "POST",
                dataType: "json",
                data: {
                    driver_map_info: "",
                    date: document.location.pathname.split('/')[3], // НЕОБХОДИМО ЗАМЕНИТЬ НА РАБОЧИЙ ВАРИАНТ
                    driver: driver_id
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status);
                    alert(thrownError);
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

                        //if (driver_list[data[i][4]] == 1) { driver_num = 2; } else { driver_list[data[i][4]] = 1; }
                        console.log('data[i][4]: ' + driver_list[data[i][4]]);

                        for (var j = 0; j < cancelDrivers.length; j++) {
                            if (parseInt(cancelDrivers[j]) == parseInt(data[i][4])) {
                                cancel = true;
                            }
                        }
                        if (cancel == false) {
                            html += '<td onclick="myMap.destroy();init()" class="list_checkbox" style="text-align: center;padding-top: 3px;"><input id="' + data[i][4] + '" class="' + data[i][0] + '" type="checkbox" checked></td>';
                        } else {
                            html += '<td onclick="myMap.destroy();init()" class="list_checkbox" style="text-align: center;padding-top: 3px;"><input id="' + data[i][4] + '" class="' + data[i][0] + '" type="checkbox"></td>';
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
                    ymaps.ready(init);

                }
            });
        }

        /**
         * Обновление информации на карте
         */
        function update_map(el) {
            var checked = $('.change_driver_info tr td:first-child input:checkbox:not(:checked)');
            var checked_ids = [];
            for (var i = 0; i < checked.length; i++) {
                checked_ids.push(checked[i].id);
            }
            $.ajax({
                type: "POST",
                data: {
                    list_p: '<?php $path = path();
                                echo $path[3]; ?>',
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

        function callPrint() {
            if (myMap.container.isFullscreen() == false) {
                myMap.container.enterFullscreen()
            } else {
                print();
            }
        }

        /**
         * Запись нового путевого листа
         */
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

        /**
         * Создание путевого листа без водителя
         */
        function createListDriver(ev) {
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
                    create_no_man_list: orders_to_change.join(),
                    date: document.location.pathname.split('/')[3]
                },
                success: function(data) {
                    location.reload();
                }
            });
        }

        /**
         * Присвоение путевого листа водителю
         */

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

        /**
         * Генерация и выгрузка путевого листа в Excel
         */
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

        /**
         * Нумерация водителей на карте
         */
        function change_numeration(state) {
            var html;
            if (state) { // true - кнопка в ненажатом состоянии
                myCollection.each(function(el) {
                    el.properties._data.iconContent = el.properties._data.excelNum;
                    el.properties._data.iconCaption = el.properties._data.excelNum;
                });
            } else { // false || undefined - кнопка в нажатом состоянии
                if (!route_copmplete) {
                    if (confirm("Для отображения номеров заказов в порядке составленного маршрута, данный маршрут необходимо сформировать. Сформироать его?")) {
                        create_route(myMap, myCollection, routes);
                        route_copmplete = true;
                    } else {
                        return 0;
                    }
                    numeric_button.state.set('selected', true);
                } else {
                    myCollection.each(function(el) {
                        el.properties._data.iconContent = el.properties._data.mapRouteNum;
                        el.properties._data.iconCaption = el.properties._data.mapRouteNum;
                        html = "<tr>";
                        html += "<td>" + el.properties._data.excelNum + "</td>";
                        html += "<td>" + el.properties._data.mapRouteNum + "</td>";
                        html += "</tr>";
                        $(html).insertAfter($(".compare_num_order table tr:last"));
                    });
                }

            };
        }

        /**
         * Сбросить все чекбоксы в списке
         */

        function changeListState() {
            // Проверяем состояние первого чекбокса в списке
            // И меняем все состояния на противоположные ему

            firstState = $('.list_checkbox:first > input:checkbox').is(':checked');

            $('.list_checkbox > input:checkbox').prop('checked', !firstState);

            myMap.destroy();
            init()
        }

        /**
         * Ручное перемещение таблицы с водителями
         */
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

            /**
             * Стартовые позиции блока
             */
            var startPositionX = $('.change_driver_info').offset().top;
            var startPositionY = $('.change_driver_info').offset().left;

            /**
             * Перемещение блока
             */
            $('.change_driver_info').offset({
                top: startPositionX - (pos4 - e.clientY),
                left: startPositionY - (pos3 - e.clientX)
            });

            pos3 = e.clientX;
            pos4 = e.clientY;
        }

        function closeDragElement() {
            /**
             * Завершение перемещения
             */
            document.onmouseup = null;
            document.onmousemove = null;
        }
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