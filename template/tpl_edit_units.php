<?php
$id = trim(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS));

$order = get_order_by_id($id);
?>

<div class="main">
    <div class=" main_form">
        <form id="add_order_form" method="post" action="/iwaterTest/backend.php">
            <input type="hidden" name="db_id" id="db_id" value="<?php echo $order['o_id'] ?>">
            <label for="name">Наименование</label><input id="name" name="name" value="<?php echo $order['client_id'] ?>" placeholder="Наименование" style="    background-color: #f4f4f4;" readonly>
            <input type="hidden" id="name-id">

            <label for="about">Описание</label><input id="about" name="about" value="<?php echo $order['about'] ?>" placeholder="Описание">
            <input type="hidden" id="about-id">

            <label for="price">Цена</label> <input id="price" name="price" placeholder="Цена" value="<?php echo $order['price'] ?>">
            <input type="button" class="classic" title="Если введённый адрес не совпадает с сохранёнными, нажмите кнопку для формирования кординат по новому адресу"  style="position:absolute; margin-left: 10px" value="Адрес разовой доставки" onclick="if($('#hidemap').css('display')!='none'){$('#hidemap').hide();}else{$('#hidemap').fadeIn().css( 'display', 'inline-block');check_point();}"></br>
            <?php if(isset($order['a_coords'])){ ?>
                <label for="cords">Координаты</label><input id="cords" style=" background-color: #f4f4f4" name="cords" placeholder="Координаты" value="<?php echo $order['a_coords'] ?>"  readonly>
            <?php }else{ ?>
                <label for="cords" hidden>Координаты</label><input id="cords" style=" background-color: #f4f4f4" name="cords" placeholder="Координаты" hidden  readonly>
            <?php } ?>

            <!--            <label for="address_new"></label><input class="classic" id="address_new" name="address_new"type="checkbox"> Новый адрес-->
            <br>
            <input type="hidden" id="address-id">

            <label for="hidden" hidden></label> <input id="contact" name="contact" type="text" placeholder="Контакт" value="<?php echo $order['contact'] ?>">
            <label for="contact">Контакт</label> <label for="date">Дата</label><input id="date" value="<?php if(isset($order['date'])){echo date("d/m/Y", $order['date']); }?>" name="date" type="text" style="    background-color: #f4f4f4;" readonly>
            </br>
            <label for="hidden" ></label> <input class="classic" id="no_date" name="no_date" type="checkbox"  onclick="return false" value="<?php echo $order['no_date'] ?>" readonly <?php if($order['no_date'] == "1"){echo "checked" ;}?>> Без даты
            <label for="name" hidden></label>   <input id="time" name="time" type="text" value="<?php echo $order['time'] ?>" placeholder="Время">
            <label for="time" >Время</label>   <label for="time_d">Период</label> <select id="time_d" name="time_d" value="<?php echo $order['period'] ?>">
                <?php echo get_priod_selected(get_number_period($order['period'])) ?>
            </select>
            <label for="notice">Примечание</label> <textarea id="notice" name="notice" placeholder="Примечание" ><?php echo $order['notice'] ?></textarea>
            <label for="water_ag">Серебрянная капелька</label> <input id="water_ag" name="water_ag" type="text" placeholder="Ag" value="<?php echo $order['water_ag'] ?>">
            <label for="water_dp">Диплома</label> <input id="water_dp" name="water_dp" type="text" placeholder="Dp" value="<?php echo $order['water_dp'] ?>">
            <label for="water_e">Ё-Water</label>  <input id="water_e" name="water_e" type="text" placeholder="Ё" value="<?php echo $order['water_e'] ?>">
            <label for="water_pl">Плеска</label>  <input id="water_pl" name="water_pl" type="text" placeholder="PL" value="<?php echo $order['water_pl'] ?>">
            <label for="water_other">Другое</label>  <input id="water_other" name="water_other" type="text" placeholder="Other" value="<?php echo $order['water_other'] ?>">
            <label for="equip">Оборудование</label>  <textarea id="equip" name="equip" placeholder="Оборудование" ><?php echo $order['equip'] ?></textarea>
            <label for="dep">Зачет\залог</label>  <input id="dep" name="dep" type="text" placeholder="Зачет или залог тары" value="<?php echo $order['dep'] ?>">
            <label for="cash">Наличка</label>  <input id="cash" name="cash" type="text" placeholder="Наличка" value="<?php if($order['cash_formula'] == ""){ echo $order['cash'];}else{echo $order['cash_formula'];} ?>">
                <div class="total_c" id="total_cash">3333</div>
            <label for="cash_b">Безнал</label>  <input id="cash_b" name="cash_b" type="text" placeholder="Безнал" value="<?php if($order['cash_b_formula'] == ""){ echo $order['cash_b'];}else{echo $order['cash_b_formula'];} ?>">
                 <div class="total_c" id="total_cash_b">4444</div>
            <label for="on_floor">Подъем на этаж(руб)</label> <input id="on_floor" name="on_floor" type="text" placeholder="Подъем на этаж" value="<?php echo $order['on_floor'] ?>">
            <label for="tank_bb">Тара к возврату</label> <input id="tank_bb" name="tank_b" type="text" placeholder="Тара к возврату" value="<?php echo $order['tank_b'] ?>">
            <label for="tank_empty_now">Сданная тара</label> <input id="tank_empty_now" name="tank_empty_now" type="text" placeholder="Сданная тара" value="<?php echo $order['tank_empty_now'] ?>">

            </br>
            <label for="driver">Водитель</label> <select id="driver" name="driver"">
                <?php echo select_driver($order['driver']); ?>
            </select>
            </br>
            <label for="status">Статус</label>  <select id="status" name="status" value="<?php echo $order['status'] ?>">
                <?php echo get_status_selected($order['status']) ?>
            </select>
            <div id="reason_d">

            </div>
            </br>
            <input name="edit_order" type="hidden">
            <input class="classic" id="submit" name="submit" type="submit" value="Сохранить">

        </form>
    </div>
    <div id="hidemap">
        <div id="map" style="margin-top: -133px;"></div>
    </div>
    <div id="order_info_by_driver">
        <table class="main_table">
            <?php
                $info = get_info_by_driver($id);
                if(count($info)>0){
                    ?>

                    <tr>
                        <td>Выполнен</td>
                        <td>Причина</td>
                        <td>Комментарий</td>
                        <td>Согасованность с оператором</td>
                        <td>Серверное время</td>
                        <td>Координаты водителя</td>
                        <td>Расстояние до цели (м)</td>
                    </tr>

                    <?php
                }
                for($i=0;$i<count($info);$i++){
                    $coords = json_decode($info[$i]['driver_coords'],true)
                    ?>

                    <tr>
                        <td><?php echo bool_to_russian($info[$i]['done']) ?></td>
                        <td><?php echo $info[$i]['reason'] ?></td>
                        <td><?php echo $info[$i]['comment'] ?></td>
                        <td><?php echo bool_to_russian($info[$i]['agreed']) ?></td>
                        <td><?php echo date("H:i:s d/m/Y",$info[$i]['server_time']) ?></td>
                        <td><?php echo  iconv("utf-8","cp1251",$coords[0])."  ". iconv("utf-8","cp1251",$coords[1]) ?></td>
                        <td><?php echo distance_driver_and_order($info[$i]['driver_coords'],$order['a_coords']) ?></td>
                    </tr>

                    <?php
                }
            ?>

        </table>

    </div>
    <br>
    <br>
    </div>

        <script>
            $(function () {
//                $("#date").datepicker();
//                $("#date").datepicker("option", "dateFormat", "dd/mm/yy");
//                $("#date").val("<?php //if (date('N', time() + 86400) == 7) {
//                    echo date('d/m/Y', time() + 86400 * 2);
//                } else {
//                    echo date('d/m/Y', time() + 86400);
//                } ?>//");
                $("#status").change(function () {
                    if (this.value == 'Отмена') {
                        var text = document.createElement('textarea');
                        text.id = "reason";
                        text.className = "reason";
                        text.name = "reason";
                        text.placeholder = "Причина отмены";
                        $("#reason_d").append(text);
                    } else {
                        $("#reason").remove();
                    }
                });
                update_total();
                update_total_b();
                $("#cash,#cash_b").on('propertychange input', function (e) {
                    update_total();
                    update_total_b();
                });
                $("#client_num").autocomplete({
                    minLength: 0,
                    source: function (request, response) {
                        $.ajax({
                            url: "/iwaterTest/backend.php",
                            type: "POST",
                            dataType: "json",
                            data: {
                                client_num_l: request.term
                            },
                            success: function (data) {
//										var client = JSON.parse(data);
                                response(data);
                            }
                        });
                    },
                    focus: function (event, ui) {
                        $("#client_num").val(ui.item.label);
                        return false;
                    },
                    select: function (event, ui) {
                        $("#client_num").val(ui.item.label);
                        $("#client_num-id").val(ui.item.value);
                        var val = document.getElementById("client_num").value;
                        var address = ui.item.desc.split(' | ')[1];
                        $.ajax({
                            type: "POST",
                            data: {
                                client_num_s: val,
                                address:address
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (data) {
                                insert_data(data, 'list1');
                            }
                        });
                    }
                })
                    .autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "<br>" + item.desc + "</div>")
                        .appendTo(ul);
                };

                $("#name").autocomplete({
                    minLength: 0,
                    source: function (request, response) {
                        $.ajax({
                            url: "/iwaterTest/backend.php",
                            type: "POST",
                            dataType: "json",
                            data: {
                                name_l: request.term
                            },
                            success: function (data) {
//										var client = JSON.parse(data);
                                response(data);
                            }
                        });
                    },
                    focus: function (event, ui) {
                        $("#name").val(ui.item.label);
                        return false;
                    },
                    select: function (event, ui) {
                        $("#name").val(ui.item.label);
                        $("#name-id").val(ui.item.value);
                        var val = document.getElementById("name").value;
                        var address = ui.item.desc.split(' | ')[1];
                        $.ajax({
                            type: "POST",
                            data: {
                                name_s: val,
                                address:address
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (data) {
                                insert_data(data, 'list2');
                            }
                        });
                    }
                })
                    .autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "<br>" + item.desc + "</div>")
                        .appendTo(ul);
                };

                $("#address").autocomplete({
                    minLength: 0,
                    source: function (request, response) {
                        $.ajax({
                            url: "/iwaterTest/backend.php",
                            type: "POST",
                            dataType: "json",
                            data: {
                                address_l: request.term
                            },
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    focus: function (event, ui) {
                        $("#address").val(ui.item.label);
                        return false;
                    },
                    select: function (event, ui) {
                        $("#address").val(ui.item.label);
                        $("#address-id").val(ui.item.value);
                        var val = document.getElementById("address").value;
                        $.ajax({
                            type: "POST",
                            data: {address_s: val},
                            url: "/iwaterTest/backend.php",
                            success: function (data) {
                                insert_data(data, 'list3');
                            }
                        });
                    }
                })
                    .autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "<br>" + item.desc + "</div>")
                        .appendTo(ul);
                };

                function insert_data(data, list_id) {
                    var i = 0;
                    var rows = data.getElementsByTagName('rows')[0];
                    l = rows.getElementsByTagName('row').length;
                    while (i < l) {
                        var request = rows.getElementsByTagName('row')[i];
                        document.getElementById("client_num").value = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
                        document.getElementById("name").value = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
                        document.getElementById("address").value = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
                        i++;
                    }
                };
            });

            $(document).ready(function () {
                update_distance();


                jQuery.validator.addMethod("dollarsscents", function (value, element) {
                    return this.optional(element) || /^\-?\d{0,6}(\.\d{0,2})?$/i.test(value);
                }, "Введите число с 2 знаками после точки");
                $("#add_order_form").validate({
                    rules: {
                        client_num: {
                            required: true
                        },
                        tank_b: {
                            digits: true
                        },
                        tank_empty_now: {
                            digits: true
                        },
                        water_ag: {
                            digits: true
                        },
                        water_dp: {
                            digits: true
                        },
                        water_e: {
                            digits: true
                        },
                        water_pl: {
                            digits: true
                        },
                        water_other: {
                            digits: true
                        },
                        cash: {
//                            dollarsscents: true
                        },
                        cash_b: {
//                            dollarsscents: true
                        },
                        on_floor: {
                            dollarsscents: true
                        }
                    },
                    messages: {
                        client_num: {
                            required: "Заполните поле"
                        },
                        tank_b: {
                            digits: "Введите целое число"
                        },
                        tank_empty_now: {
                            digits: "Введите целое число"
                        },
                        water_ag: {
                            digits: "Введите целое число"
                        },
                        water_dp: {
                            digits: "Введите целое число"
                        },
                        water_e: {
                            digits: "Введите целое число"
                        },
                        water_pl: {
                            digits: "Введите целое число"
                        },
                        water_other: {
                            digits: "Введите целое число"
                        },
                        errorPlacement: function(error, element) {
                            error.insertAfter(element);
                        }
                    }
                });

            });
            function check_point() {
                var address = $("#address");

//                myCollection.removeAll();
                var myGeocoder = ymaps.geocode(address.val());

                myGeocoder.then(
                    function (res) {
                        coords = res.geoObjects.get(0).geometry._coordinates;

                        myPlacemark = new ymaps.Placemark(
                            [coords[0], coords[1]],
                            {
                                iconContent: 0,
                                hintContent: "Адресс №",
                                address_num: 1
                            },
                            {draggable: true});
                        myMap.geoObjects.add(myPlacemark);
                        $('#cords')[0].value = coords[0] + ',' + coords[1];
                        $("label[for='cords']").show();
                        $('#cords').show();

                        myPlacemark.events.add("dragend", function (e) {
                            cords = e.get('target').geometry.getCoordinates();
                            var address_num = e.get('target').properties._data.address_num;
                            $('#cords').val(cords);
                        }, myPlacemark);

                    },
                    function (err) {
                        //console.log('Ошибка');
                    }
                );

            }
            function update_total(){
                var cash = $("#cash").val();
                $.ajax({
                    type: "POST",
                    data: {
                        cash: cash,
                        update_total: ""
                    },
                    url: "/iwaterTest/backend.php",
                    success: function (data) {
                        $("#total_cash").text(data)
                    }
                });
            }
            function update_total_b(){
                var cash_b = $("#cash_b").val();
                $.ajax({
                    type: "POST",
                    data: {
                        cash_b: cash_b,
                        update_total: ""
                    },
                    url: "/iwaterTest/backend.php",
                    success: function (data) {
                        $("#total_cash_b").text(data)
                    }
                });
            }

        </script>
