<div class="main">

   <div class="info_about_order" style="float:right; width:300px; padding-top:31px;">
      <table id="order_info" style="text-align:center; border: 3px;">
         <caption>Информация о заказе</caption>
         <!-- Информация о необработанном заказе -->
         <tr><td>Имя заказчика</td><td><div id="info_name">пусто</div></td></tr>
         <tr><td>Регион</td><td><div id="info_region">пусто</div></td></tr>
         <tr><td>Адрес</td><td><div id="info_address">пусто</div></td></tr>
         <tr><td>Контакт</td><td><div id="info_contact">пусто</div></td></tr>
         <tr><td>Дата</td><td><div id="info_date">пусто</div></td></tr>
         <tr><td>Период</td><td><div id="info_time">пусто</div></td></tr>
         <tr><td>Примечание</td><td><div id="info_notice">пусто</div></td></tr>
         <tr><td>Товары:</td><td><div id="info_order">пусто</div></td></tr>
      </table>
   </div>

    <div class=" main_form">
        <form id="add_order_form" method="post" action="/iwaterTest/backend.php">
            <label for="client_num">Номер клиента</label><input id="client_num" name="client_num" placeholder="Номер клиента">
            <input type="button" class="classic" style="position:absolute; margin-left: 10px" value="<?php echo $res = ( isset($GET['once']) && $_GET['once'] == "once") ? "Постоянный клиент" :  "Разовый клиент" ?>" id="once_order">
            <div id="repeat_order_id" class="error" hidden>У данного клиента нет заказов</div>
            <input type="hidden" id="client_num-id">

            <label for="name">Название</label><input id="name" name="name" placeholder="Название"><input type="button" class="classic" style="position:absolute; margin-left: 10px" value="Повторить прошлый заказ" onclick="repeat_order()">
            <input type="hidden" id="name-id">

            <label for="region">Регион</label> <select id="region" name="region">
                <option value="default" selected>По-умолчанию</option>
            </select>

            <label for="address">Адрес</label> <input id="address" name="address" placeholder="Адрес"><input type="button" class="classic" title="Если введённый адрес не совпадает с сохранёнными, нажмите кнопку для формирования кординат по новому адресу"  style="position:absolute; margin-left: 10px" value="Адрес разовой доставки" onclick="if($('#hidemap').css('display')!='none'){$('#hidemap').hide();}else{$('#hidemap').fadeIn().css( 'display', 'inline-block');check_point();}">
            </br>
            <label for="cords" hidden>Координаты</label><input id="cords" style=" background-color: #f4f4f4" name="cords" placeholder="Координаты" hidden readonly>
            <input type="hidden" id="name-id">

            <!--            <label for="address_new"></label><input class="classic" id="address_new" name="address_new"type="checkbox"> Новый адрес-->
            <br>
            <input type="hidden" id="address-id">

            <label for="hidden" hidden></label> <input id="contact" class="contact" name="contact" type="text" placeholder="Контакт" oninput="helper();"> <div class="contact_help">Введите номер/номера телефонов через ;</div>
            <label for="contact">Контакт</label> <label for="date">Дата</label><input id="date" name="date" type="text">
            </br>
            <label for="hidden" ></label> <input class="classic" id="no_date" name="no_date" type="checkbox"> Без даты
            <label for="name" hidden></label>   <input id="time" name="time" type="text" placeholder="Время">
            <label for="time" >Время</label>   <label for="time_d">Период</label> <select id="time_d" name="time_d">
                <option value="" selected>По-умолчанию</option>
                <?php echo select_period(); ?>
            </select>
            <label for="notice">Примечание</label> <textarea id="notice" name="notice" placeholder="Примечание"></textarea>

			<label for="units" >Товары</label>
			<style>
      .contact_help{
          position: absolute;
          width: auto;
          margin-left: 490px;
          border: 1px solid;
          border-radius: 1px 5px 5px 5px;
          padding: 3px;
          background-color: #d5d5d5;
          color: #898989;
          opacity: 0;
          text-align: center;
          transition: all 0.6s;
        }
				.select_units {
					padding-left: 180px;
				}
				#count {
					min-width: 55px;
					width: 80px;
				}
        #price {
          min-width: 95px;
          width: 115px;
        }
				#wrapper {
					min-width: 310px;
					max-width: 310px;
				}
				#add_select {
					min-width: 23px;
				}
        #remove_select {
          min-width: 23px;
       }
        #order_info td {
          padding: 8px;
          font-size: 3;
        }
        }
			</style>
			<div class = "select_units">
				<div id = "div_units_0">
					<select id = "wrapper"></select>
          <input id = "count" placeholder = "Кол-во" autocomplete = false oninput="culcPrice();">
          <input id = "price" placeholder = "Цена" autocomplete = false oninput="culcPrice();">
          <input id = "add_select" value = "+" type = "button" onclick="addSelect();">
          <input id = "remove_select" value = "-" type = "button" onclick="removeSelect(this.parentNode.id); culcPrice();">

				</div>
			</div>

            <label for="dep">Зачет\залог</label>  <input id="dep" name="dep" type="text" placeholder="Зачет или залог тары">
            <label>Выбор оплаты</label>  <div style="padding-left: 180px; display:inherit;"> <input type="radio" id="it_nal" name="selec_pr" style="min-width: 25px;" checked onchange="changeCost();"> Наличный <input type="radio" id="it_bez" name="selec_pr" style="min-width: 25px;" onchange="changeCost();"> Безналичный </div> <br>
            <label for="cash">Наличка</label>  <input id="cash" name="cash" type="text" placeholder="Наличка">
                <div class="total_c" id="total_cash"></div>
            <label for="cash_b">Безнал</label>  <input id="cash_b" name="cash_b" type="text" placeholder="Безнал" disabled>
                <div class="total_c" id="total_cash_b"></div>
            <label for="on_floor">Подъем на этаж(руб)</label> <input id="on_floor" name="on_floor" type="text" placeholder="Подъем на этаж">
            <label for="tank_bb">Тара к возврату</label> <input id="tank_bb" name="tank_b" type="text" placeholder="Тара к возврату">
            <label for="tank_empty_now">Сданная тара</label> <input id="tank_empty_now" name="tank_empty_now" type="text" placeholder="Сданная тара">
            </br>
            <label for="driver">Водитель</label> <select id="driver" name="driver">
                <?php echo select_driver(); ?>
            </select>
            </br>
            <label for="status">Статус</label>  <select id="status" name="status">
                <option value="0" selected>В работе</option>
                <option value="1">Отмена</option>
                <option value="2">Доставлен</option>
                <option value="3">Перенос</option>
            </select>
            <div id="reason_d">

            </div>
            </br>
            <input name="add_order" type="hidden">
            <input id="cash_formula" name="cash_formula" type="text" value="" style="display: none;">
            <input class="classic" id="submit" name="submit" type="submit" value="Добавить">

		 <label for="hidden" hidden></label><input id = "water_equip" name="water_equip" value = 0 hidden readonly>
		</br></br>

        </form>
		</div>
			<div id="hidemap">
				<div id="map" style="margin-top: -133px;"></div>
			</div>
		<div>

    <script>
      var myMap;
      var myCollection;
      var get = "<?php echo "get_".$_GET['once'] ?>";
      var products = [];
      var count_order = 1;
      var units = '';

      function helper() {
          if ($('.contact').val() == '') {
              $('.contact_help').css("opacity", 0);
          } else {
              $('.contact_help').css("opacity", 1);
          }
      }

      // Получение списка продуктов из базы
			$.ajax({
				type:'get',
				url:'/iwaterTest/backend.php',
				data:{'company_id':'1'},
				response:'text',
				success:function (data) {
					unit_list = JSON.parse(data);
					parseProductFromDB(unit_list);
				}
			});

         $.ajax({
           type: "POST",
           url: "/iwaterTest/backend.php",
           data: {
            migrate_ord: <?php echo $_GET['id']; ?>
           },
           datatype: "json",
           success: function (data) {
            cat = JSON.parse(JSON.stringify(data).toString());

            var dt = new Date(cat['date'] * 1000);

            $('#cash_formula').val(cat['cash_formula']);
            $('#info_region').html(cat['region']);
            $('#info_name').html(cat['name']);
            $('#info_address').html(cat['region'] + ", " + cat['address']);
            if (cat['phone'] != null) {
               $('#info_contact').html("+7" + cat['phone']);
            } else {
               $('#info_contact').html("не указан");
            }
            $('#info_date').html(("0" + dt.getDate()).slice(-2) + "/" + ("0" + (dt.getMonth() + 1)).slice(-2) + "/" + dt.getFullYear());
            $('#info_time').html(cat['period']);
            document.getElementById('time_d').text = cat['period'];
            $('#info_notice').html(cat['notice']);

            var priceArray = [];
            var array = cat['cash_formula'].split("+");

            for (i = 0; i < array.length; i++) {
                 var unit = array[i].split("*");
                 priceArray.push(Math.floor(parseInt(unit[1]).toFixed(2)));
            }

            //Тут происходит рассчёт количества товаров в заказе и выведение их в классическом виде
            var water_list = "";
            water = JSON.parse(cat['water_equip']);
            for (i = 0; i < water.length; i++) {
                 water_list += products[water[i]['id']] + " - " + water[i]['count'] + "</br>";
                 console.log(water_list);
            }

            $('#info_order').html(water_list);
         }
         });

      // Расчёт стоимость из цен и количества
      function culcPrice() {
        full_cost = 0;
        formula = '';

        for (id = 0; id < count_order; id++) {
          full_cost += $('#div_units_' + id).children('#count').val() * $('#div_units_' + id).children('#price').val();
          formula += $('#div_units_' + id).children('#price').val() + '*' + $('#div_units_' + id).children('#count').val() + '+';
        }

        formula = formula.substring(0, formula.length -1);
        $('#cash_formula').val(formula);

        if ($('#it_nal').is(':checked')) {
          $('#cash').val(full_cost.toFixed(2));
          $('#cash_b').val('');
        } else {
          $('#cash_b').val(full_cost.toFixed(2));
          $('#cash').val('');
        }
      }

      // Вывод списка продуктов в виде списка
      function parseProductFromDB(unit_list) {
         for (var i = 0; i < unit_list.length; i++) {
            $('<option>', { id: i, value: unit_list[i]['id'], text: unit_list[i]['name']}).appendTo('#wrapper');
                 products[unit_list[i]['id']] = unit_list[i]['name'];
         }
      }

      // Заполнение регионов доставки
      $.ajax({
          type: "POST",
          data: {
              region: "s"
          },
          url: "/iwaterTest/backend.php",
          response: "text",
          success: function (data) {
            var array = data.split(",");

            for (i = 0; i < array.length; i++) {
              old = $('#region').html();
              $('#region').html(old + '<option value="' + array[i] + '">' + array[i] + '</option>');
            }
          }
        });

      // Обработка удаления товара
      $("#remove_select").click(function() {
        if (count_order > 1) {
          $("#div_units_" + (count_order - 1)).remove();
          count_order--;
        }
      });


			$("#submit").click(function(){
				var text = "a:" + count_order + ":{";
				var water_equip = [];
				for (var k = 0; k < count_order; k++) {
					div_cur = document.getElementById('div_units_' + k);

					id = div_cur.querySelector('#wrapper');
					value = id.options[id.selectedIndex].value;

					count_ord = div_cur.querySelector('#count');
					selected = count_ord.value;

					text += "i:" + value + ";i:" + selected + ";";
					water_equip[value] = selected;
				}
				text += "}";
				document.getElementById("water_equip").value = text;
			});
            $(function () {
                $("#date").datepicker();
                $("#date").datepicker("option", "dateFormat", "dd/mm/yy");
                $("#date").val("<?php if (date('N', time() + 86400) == 7) {
                    echo date('d/m/Y', time() + 86400 * 2);
                } else {
                    echo date('d/m/Y', time() + 86400);
                } ?>");
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
                if(get == "get_once") {
                    $("label[for='cords']").show();
                    $("#cords").show();
                    var client_num = $("#client_num");
                    client_num.val(" -- ");
                    client_num.css("background-color", "#f4f4f4");
                    client_num.attr('readonly', true);
                    $("#region").val('Санкт-Петербург');
                }else {

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
                                    address: address
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
                                    address: address
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
                }

                $("#once_order").click(function() {
                    if(get=="get_once"){
                        window.location = "/iwaterTest/admin/add_order/";
                    }else {
                        window.location = "/iwaterTest/admin/add_order?once=once";
                    }
                });

                function insert_data(data, list_id) {
                    var i = 0;
                    var rows = data.getElementsByTagName('rows')[0];
                    l = rows.getElementsByTagName('row').length;
                    while (i < l) {
                        var request = rows.getElementsByTagName('row')[i];
                        document.getElementById("client_num").value = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
                        document.getElementById("name").value = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
                        document.getElementById("address").value = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
                        document.getElementById("region").value = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;

                        document.getElementById("contact").value = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
                        i++;
                    }
                }
            });

            $(document).ready(function () {
                ymaps.ready(init);

                function init() {
                    myMap = new ymaps.Map("map", {
                        center: [59.93, 30.31],
                        zoom: 7,

                    });
                    myCollection = new ymaps.GeoObjectCollection();
                }

                jQuery.validator.addMethod("dollarsscents", function (value, element) {
                    return this.optional(element) || /^\-?\d{0,6}(\.\d{0,2})?$/i.test(value);
                }, "Введите число с 2 знаками после точки");
                $("#add_order_form").validate({
                    rules: {
                        client_num: {
                            required: true
                        },
                        name: {
                            required: true
                        },
                        time_d: {
                            required: true
                        },
                        address: {
                            required: true
                        },
                        cords: {
                            required: true
                        },
                        tank_b: {
                            digits: true
                        },
                        tank_empty_now: {
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
                        name: {
                            required: "Заполните поле"
                        },
                        time_d: {
                           required: "Выберите значение"
                        },
                        address: {
                            required: "Заполните поле"
                        },
                        client_num: {
                            required: "Заполните поле"
                        },
                        cords: {
                            required: "Заполните поле"
                        },
                        tank_b: {
                            digits: "Введите целое число"
                        },
                        tank_empty_now: {
                            digits: "Введите целое число"
                        },
                        errorPlacement: function(error, element) {
                            error.insertBefore(element);
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
            function repeat_order(){
               var client_id =  $("#client_num").val();
               var address = $("#address").val();
                $.ajax({
                    type: "POST",
                    data: {
                        client_id: client_id,
                        address: address,
                        check_order: ""
                    },
                    url: "/iwaterTest/backend.php",
                    success: function (data) {
                        if(data == "null"){
                            $("#repeat_order_id").show();
                            return 1;
                        }else{
                            $("#repeat_order_id").hide();
                            $.ajax({
                                type: "POST",
                                data: {
                                    id: data,
                                    fill_order: ""
                                },
                                url: "/iwaterTest/backend.php",
                                success: function (data) {
                                    data_fill_order(data);
                                }
                            });
                        }
                    }
                });
            }

      function data_fill_order(data) {
        var last = data.getElementsByTagName('row')[0];
        console.log(last.getElementsByTagName('cell')[4].childNodes[0].nodeValue);

        $('#time').val(last.getElementsByTagName('cell')[1].childNodes[0].nodeValue);
        $('#time_d').val(last.getElementsByTagName('cell')[2].childNodes[0].nodeValue);
        $('#notice').val(last.getElementsByTagName('cell')[3].childNodes[0].nodeValue);

        var priceArray = [];
        var array = [];

        if (last.getElementsByTagName('cell')[7].childNodes[0].nodeValue != "") {
          $('#it_nal').attr('checked', true);
          array = last.getElementsByTagName('cell')[7].childNodes[0].nodeValue.split("+");
        } else {
          $('#it_bez').attr('checked', true);
          $('#it_nal').removeAttr('checked');
          array = last.getElementsByTagName('cell')[8].childNodes[0].nodeValue.split("+");
         }
         changeCost();

        for (i = 0; i < array.length; i++) {
            var unit = array[i].split("*");
            priceArray.push(Math.floor(parseInt(unit[0]).toFixed(2)));
        }

        var water = [];
        water = JSON.parse(last.getElementsByTagName('cell')[4].childNodes[0].nodeValue);
        $('#water_equip').val(water);
        for (i = 0; i < water.length; i++) {
            if (i == 0) {
                 $('#div_units_0').children('#wrapper').val(water[0]['id']);
                 $('#div_units_0').children('#count').val(water[0]['count']);
                 $('#div_units_0').children('#price').val(priceArray[0]);
            } else {
                $("#div_units_0").clone().attr('id', 'div_units_' + count_order).appendTo(".select_units");
                 $('#div_units_' + count_order).children('#wrapper').val(water[i]['id']);
                 $('#div_units_' + count_order).children('#count').val(water[i]['count']);
                 $('#div_units_' + count_order).children('#price').val(priceArray[i]);
                 count_order++;
            }
        }

        culcPrice();

        $('#dep').val(last.getElementsByTagName('cell')[6].childNodes[0].nodeValue);
        $('#on_floor').val(last.getElementsByTagName('cell')[9].childNodes[0].nodeValue);

        console.log(last);
      }

      function changeCost() {
        svap = '';

        if ($('#it_nal').is(':checked')) {
          svap = $('#cash_b').val();

          $('#cash').removeAttr('disabled');
          $('#cash').val(svap);

          $('#cash_b').attr('disabled', 'disabled');
          $('#cash_b').val('');
        } else {
          svap = $('#cash').val();

          $('#cash').attr('disabled', 'disabled');
          $('#cash').val('');

          $('#cash_b').removeAttr('disabled');
          $('#cash_b').val(svap);
        }
      }

      // Добавить товар в заказ
      function addSelect() {
          $("#div_units_0").clone(true).attr('id', 'div_units_' + count_order).appendTo(".select_units").find('input:text').val('');
          count_order++;
      }

      // Удалить товар из заказа
      function removeSelect(el) {
          var counter = 1;
          var selected = $('#' + el);
          var selectedId = selected.attr("id");
          var id = selectedId.replace('div_units_', '')
          if (count_order > 0) {
            selected.remove();
            if (count_order > 1) {
              for (var i = id; i < count_order; i++) {
                $('#div_units_' + i).attr('id', 'div_units_' + (i - 1));
              }
            }
            counter++;
            count_order--;
            console.log(count_order);
          }
      }
    </script>
