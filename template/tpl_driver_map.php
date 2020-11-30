<?php
$session = get_session();

?>
<div class="main">
    <div class="main_form">
        <img src="../../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>
        <div class="category_label">
          Карта водителя
       </div>
        <div style="font-size: 15px; margin: 7px 0;">
        <form id="driver_list" method="post" action="/iwaterTest/backend_driver.php">
           <label for="driver">Водитель: </label>
            <?php if($session['role'] != 3) { ?>
                <select  class="classic" id="driver" name="driver" style="width: 150px; height: 22px; margin: 0 0 0 17px;">
                    <?php echo select_driver(); ?>
                </select>
            <?php }else{ ?>
                <input type="hidden" id="driver" name="driver" value="<?php echo $session['id']  ?>">
            <?php } ?>
            <input type="hidden" name="driver_list">
            <input class="classic" id="submit_driver_map" onclick="mapInit()" name="submit" type="button" value="Отобразить" style="width: 100px; height: 23px; border-radius: 15px; background-color: #015aaa; color: #fff;">
            <br>
        </form>
     </div>
    </div>
    <div id="">
        <table id="driver_list_table" class="main_table">

        </table>
    </div>
<?php if($session['role'] != 3) { ?>
    <script>
        $( function() {
            $("#submit_driver_map").show();
        });

    </script>
<?php }else{ ?>
    <script>
        $( function() {
            $("#submit_driver_map").hide();
            $("#submit_driver_map").click();
        });

    </script>
<?php } ?>

<!--</div>-->


<div id="clients_list_table" class="clients_list_table">
    <div id="hidemap" style="display: inline-block">
        <div class="tank" hidden>
            <div class="label_tank">Кол-во тары на выделенных объектах: </div><div class="num_tank">0</div>
        </div>
        <div id="map"></div>
    </div>
    <div id="map_info">
        <table>
            <tbody>
            <tr>
                <td>
                    <img src="/iwaterTest/css/image/blue.png">
                </td>
                <td>
                    <img src="/iwaterTest/css/image/changed.png">
                </td>
                <td>
                    <img src="/iwaterTest/css/image/time.png">
                </td>
                <td>
                    <img src="/iwaterTest/css/image/time_changed.png">
                </td>
                <td>
                    <img src="/iwaterTest/css/image/black.png">
                </td>
            </tr>
            <tr>
                <td>
                    Отображает метку закзаза. Иконка отображает статичный адрес для этого клиента
                </td>
                <td>
                    Адрес заказа, водитель которого был изменён в меню карты.
                </td>
                <td>
                    Заказ, адрес которого был выставлен вручную. Имеет статус "временного" адреса.
                </td>
                <td>
                    Адрес заказа с "врменным адресом", водитель которого был изменён.
                </td>
                <td>
                    Иконка выделенного заказа. Функционал выделения позволяет отслеживать кол-во тары для объектов и менять водителя. Для выделения заказа зажмите клавишу <b>Ctrl</b> и кликните по иконке заказаю
                </td>
            </tr>
            <tr>
               <td>
                   <img src="/iwaterTest/css/image/red.png">
               </td>
               <td>
                   <img src="/iwaterTest/css/image/yellow.png">
               </td>
               <td>
                   <img src="/iwaterTest/css/image/green.png">
               </td>
               <td>
                   <img src="/iwaterTest/css/image/violet.png">
               </td>
               <td>
                   <img src="/iwaterTest/css/image/darkblue.png">
               </td>
               <td>
                   <img src="/iwaterTest/css/image/white.png">
               </td>
            </tr>
            <tr>
               <td>
                   Утро
               </td>
               <td>
                   Первая половина дня
               </td>
               <td>
                   Середина дня
               </td>
               <td>
                   Вторая половина рабочего дня
               </td>
               <td>
                   Вечер
               </td>
               <td>
                   Ошибка формирования заказа. Некорректный временной период.
               </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

   <style media="screen">
      .category_label {
         width: 100%;
         float: left;
         font-size: 21px;
         padding: 20px 0 20px 25px;
         border-bottom: 1px solid #e3e9f1;
      }
      #map_info {
         width: 1024px;
         height: auto;
         border-radius: 7px;
         background-color: #fff;
         padding: 10px;
      }
   </style>


    <!-- НАЧАЛО КОДА -->
    <script type="text/javascript">
        function mapInit() {

            var driver_id2 = $("#driver").val();

            ymaps.ready(init);
            var myMap;
            var myCollection;
            var group = false;
            var selectBubles = [];
            var count_tanks = 0;
            var count_bubles = 0;
            var routes = [];
            var periods =[];
            var adder = 0;

            var map_flag = false;
            var route_copmplete = false;

            $.ajax({
                type:'POST',
                url:'/iwaterTest/backend_driver.php',
                data:{
                    'periods_del':'d'},
                response:'text',
                success:function (data) {
                    unit_list = JSON.parse(data);
                    parseProductFromDB(unit_list);
                }
            });

            function parseProductFromDB(unit_list) {
                for (var i = 0; i < unit_list.length; i++) {
                    periods.push(JSON.parse(JSON.stringify(unit_list[i]))['unit']);
                }
            }

            function init() {
                myMap = new ymaps.Map("map", {
                    center: [57.805278, 28.345717],
                    zoom: 12
                });

                if (driver_id2 != "") {

                }
                if (map_flag == true) {
                    myMap.container.enterFullscreen()
                }

                myCollection = new ymaps.GeoObjectCollection();

                myMap.events.add('sizechange', function () {
                    if (map_flag == true) {
                        $(".change_driver_info").css({"position": "inherit", "color": "#807e7e"});
                        $(".tank").css({"position": "inherit", "color": "#807e7e"});
                    } else {
                        $(".change_driver_info").css({
                            "position": "absolute",
                            "color": "black",
                            "top": "49%",
                            "left": "70%",
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
                var checked_ids = [];
                var coords_list = [];
                var arr = []; //массив для сортировки координат
                var period = []; //массив для работы с периодами
                for (var i = 0; i < checked.length; i++) {
                    checked_ids.push(checked[i].id);
                }

                /** Решение задачи с поиском кратчайшего маршрута и вывод в виде массива координат
                */

                $.ajax({
                    type: "POST",
                    url: "/iwaterTest/backend_driver.php",
                    data: {
                        sord_coords: driver_id2,
                        date: "<?php echo date("d/m/Y") ?>"
                    },
                    success: function (coords) {

                        console.log('FROM DB');
                        console.log(coords);

                        var i = 0;
                        var rows = coords.getElementsByTagName('rows')[0];
                        var l = rows.getElementsByTagName('row').length;

                        console.log('Count from db = ' + l);

                        while (i < l) {
                            var request = rows.getElementsByTagName('row')[i];
                            var temp = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
                            var perepm = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
                            arr.push(temp.replace(/"/g, '').split(','));
                            period.push(perepm.replace(/"/g, '').split(','));
                            i++;
                        }

                        console.log('After sort = ' + arr.length);

                        var last_item = 0;
                        for (var k = 0; k < periods.length; k++) {
                            var temp_arr = [];
                            for (var m = 0; m < arr.length; m++) {
                                if (period[m] == periods[k]) {
                                    temp_arr.push(arr[m]);
                                }
                            }

                            console.log('++++++++++++++++++++++++++++++COME');
                            console.log(temp_arr);

                            temp_arr = sortCoords(temp_arr);

                            console.log('++++++++++++++++++++++++++++++EXIT');
                            console.log(temp_arr);

                            if (false) {
                                var glueCoordArray = glueCoords(last_item, temp_arr);
                                for (var z = 0; z < glueCoordArray.length; z++) {
                                    if (typeof glueCoordArray[z] != 'undefined'){
                                        requestMap(glueCoordArray[z]);
                                    }
                                }
                            } else {
                                for (var z = 0; z < temp_arr.length; z++) {
                                    if (typeof temp_arr[z] != 'undefined') {
                                        requestMap(temp_arr[z]);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            var counter = 0;

            function requestMap(arr) {
                /** Прогон координат для отображения на карте
                */

                counter++;
                    $.ajax({
                        type: "POST",
                        data: {
                            coords_data: "" + arr[0] + "," + arr[1],
                            date: "<?php echo date("d/m/Y") ?>",
                            driver: driver_id2,
                            index: counter
                        },
                        url: "/iwaterTest/backend_driver.php",
                        success: function (req) {
                            var rows = req.getElementsByTagName('rows')[0];
                            var request = rows.getElementsByTagName('row')[0];
                            var coords = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
                            var time = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
                            var tank_b = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
                            var client_id = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;
                            var period = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
                            var order_id = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;
                            var preset = [];

                            var color;

                            for (var n = 0; n < periods.length; n++) {
                                if (periods[n] == period) {
                                    color = getColorByPeriod(n);
                                }
                            }

                            coords = coords.replace(/"/g, '').split(',');
                            if (request.getElementsByTagName("cell")[0].className == "temp") {
                                myPlacemark = new ymaps.Placemark([coords[0], coords[1]],
                                    {
                                        iconCaption: adder + 1,
                                        iconContent: adder + 1,
                                        excelNum: adder + 1,
                                        hintContent: 'Временный адрес<br> Время: ' + time + '<br> Период: ' + period + '<br> Тары: ' + tank_b + '<br> №: ' + order_id + 'Временный адрес<br>Время: ' + time + '<br> Период: ' + period + '<br> Кол-во бутылок(тары): ' + tank_b + '<br> Номер клиента: ' + client_id,
                                        data_id: client_id,
                                        tank: tank_b,
                                        selected: 0,
                                        defaultColor: color,
                                        order_id: order_id,
                                    },
                                    {
                                        preset: preset[0],
                                        draggable: true,
                                        iconColor: color
                                    }
                                );
                                adder++;
                            } else {
                                myPlacemark = new ymaps.Placemark([coords[0], coords[1]],
                                    {
                                        iconContent: adder + 1,
                                        excelNum: adder + 1,
                                        hintContent: 'Время: ' + time + '<br> Период: ' + period + '<br> Тары: ' + tank_b + '<br> №: ' + order_id,
                                        balloonContent: 'Время: ' + time + '<br> Период: ' + period + '<br> Кол-во бутылок(тары): ' + tank_b + '<br> Номер клиента: ' + client_id,
                                        data_id: client_id,
                                        tank: tank_b,
                                        selected: 0,
                                        defaultColor: color,
                                        order_id: order_id
                                    },
                                    {
                                        preset: preset[1],
                                        draggable: true,
                                        iconColor: color
                                    }
                                );
                                adder++;
                            }
                            myCollection.add(myPlacemark);
                            myMap.geoObjects.add(myCollection);

                            myPlacemark.events.add("dragend", function (e) {
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
                                            success: function (req) {
                                                //request(req);
                                            }
                                        });
                                    }
                                }, myPlacemark);

                                myPlacemark.events.add("click", function (e) {
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
                        create_route_driver(myMap,myCollection, routes);
                    }
                });
            }

            function getColorByPeriod(period) {
                var colors = ['red', 'orange', 'green', 'darkgreen', 'violet', 'blue', 'darkblue', 'pink']

                return colors[period];
            }

            $(function () {
                $('#route_div #text_route').hide();
                $('#route_div #open').click(function () {
                    $(this).next().slideToggle();
                });
                $("#xlsx_map_link").click(function (event) {
                    event.preventDefault();
                    $('.loading').show();
                    $.ajax({
                        type: "POST",
                        data: {
                            createExcell: "",
                            date: <?php echo date(mktime(0, 0, 0, date('m'), date('d'), date('Y'))); ?>,
                            driver_id: "<?php echo $driver_info['id'] ?>",
                            driver_n: "<?php echo $driver_info['name'] ?>"

                        },
                        url: "/iwaterTest/backend.php",
                        success: function (req) {
                            $('.loading').hide();
//                            location.href = '/iwaterTest/files/' + req;
                            window.downloadFile('/iwaterTest/files/' + req);
                        }
                    });
                });

                $(this).keydown(function (e) {
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
                $(this).keyup(function (e) {
                    if (e.keyCode == 17) {
                        {
                            group = false;
                            myMap.geoObjects.options.set({
                                openBalloonOnClick: true
                            })
                        }
                    }
                });

            });
            function pick_out_all() {
                window.count_tanks = 0;
                window.count_bubles = 0;
                $(".tank").show();
                myCollection.each(function (el) {
                    el.properties._data.selected = 1;
                    el.options.set('iconColor', 'black');
                    window.count_tanks += parseInt(el.properties._data.tank);
                    window.count_bubles++;
                });
                $(".num_tank").text(count_tanks);
                $(".num_selected").text(count_bubles);
            }

            function cancel_pick_out_all() {
                $(".tank").hide();
                window.count_tanks = 0;
                window.count_bubles = 0;
                myCollection.each(function (el) {
                    el.properties._data.selected = 0;
                    el.options.set('iconColor', el.properties._data.defaultColor);

                });
                window.count_tanks = 0;
                window.count_bubles = 0;
                $(".num_tank").text(window.count_tanks);
                $(".num_selected").text(window.count_bubles);
            }

            function update_driver_info() {
                var driver_id = "<?php echo $driver_id?>";
                if (driver_id == "") driver_id = "all";
                var get = <?php echo json_encode($_GET); ?>;
                var cancelDrivers = [];
                $.each(get, function (key, value) {
                    if (key != "driver_id") {
                        cancelDrivers.push(value);
                    }
                });

                $.ajax({
                    url: "/iwaterTest/backend.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        driver_map_info: "",
                        list: '<?php $path = path(); echo $path[3]; ?>',
                        driver: driver_id
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(xhr.status);
                        alert(thrownError);
                    },
                    success: function (data) {
                        var html = "";
                        var cancel = false;
                        for (var i = 0; i < data.length; i++) {
                            html += "<tr>";
                            for (var j = 0; j < cancelDrivers.length; j++) {
                                if (parseInt(cancelDrivers[j]) == parseInt(data[i][3])) {
                                    cancel = true;
                                }
                            }
                            if (cancel == false) {
                                html += '<td onclick="myMap.destroy();init()" style="text-align: center;padding-top: 3px;"><input id="' + data[i][3] + '" type="checkbox" checked></td>';
                            } else {
                                html += '<td onclick="myMap.destroy();init()" style="text-align: center;padding-top: 3px;"><input id="' + data[i][3] + '" type="checkbox"></td>';
                            }
                            html += "<td>" + data[i][0] + "</td>";
                            html += "<td>" + data[i][1] + "</td>";
                            html += "<td>" + data[i][2] + "</td>";
                            html += "</tr>";
                            cancel = false;

                        }
                        $(html).insertAfter($(".change_driver_info  table tr:last"));

                    }
                });
            }

            function update_map(el) {
                var checked = $('.change_driver_info tr td:first-child input:checkbox:not(:checked)');
                var checked_ids = [];
                for (var i = 0; i < checked.length; i++) {
                    checked_ids.push(checked[i].id);
                }
                $.ajax({
                    type: "POST",
                    data: {
                        list_p: '<?php $path = path(); echo $path[3]; ?>',
                        driver_id: '<?php if ($driver_id != "") {
                            echo $driver_id;
                        } ?>',
                        exception_driver: checked_ids
                    },
                    url: "/iwaterTest/backend.php",
                    success: function (req) {
                        requestMap(req);
                    }
                });
            }

            function callPrint() {
//                $("#hidemap .ymaps-2-1-44-button__icon,.ymaps-2-1-44-button__icon_icon_expand").eq(5);
                if (myMap.container.isFullscreen() == false) {
                    myMap.container.enterFullscreen()
                } else {
                    print();
                }
            }

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
                        change_driver_in_map: $("#driver").val(),
                        orders: orders_to_change
                    },
                    success: function (data) {
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

            function change_numeration(state) {
                var html;
                if (state) { // true - кнопка в ненажатом состоянии
                    myCollection.each(function (el) {
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
                        myCollection.each(function (el) {
                            el.properties._data.iconContent = el.properties._data.mapRouteNum;
                            el.properties._data.iconCaption = el.properties._data.mapRouteNum;
                            html = "<tr>";
                            html += "<td>" + el.properties._data.excelNum + "</td>";
                            html += "<td>" + el.properties._data.mapRouteNum + "</td>";
                            html += "</tr>";
                            $(html).insertAfter($(".compare_num_order  table tr:last"));
                        });
                    }

                }
                ;
            }
            function create_route_driver(map,myCollection, routes){

                var points = [];

                var i = 0;

                myCollection.each(function (obj) {
                    points[i] = {type: 'wayPoint', point: obj.geometry._coordinates};
                    i++;
                });

                ymaps.route(points, {
                    mapStateAutoApply: true,
                    avoidTrafficJams: true
                }).then(function (route) {
                    route.getPaths().options.set({
                        strokeColor: "#ff0000",
                        strokeWidth: 5,
                        opacity: 0.8
                    });
                    // добавляем маршрут на карту
                    map.geoObjects.add(route.getPaths());
                });
            }

        }
    </script>
