<div class="main">
   <div class="main_form">
      <form id="add_order_form" method="post" action="/iwaterTest/backend.php">
         <div class="name_title">
            <div class="name_position">Добавить заказ</div>
         </div>
         <div class="content_add" style="width: 650px;">
            <div style="width: 50%; display: inline-block;">
               <label style="width: 150px;">
                  <label class="checkbox-conteiner"><input style="width: auto" class="checkbox" name="type" type="radio" value="0" id="period_client" onchange="swapClientNumber()" <?php if (!isset($_GET['once'])) { echo 'checked'; } ?>><span class="checkbox-visual"></span><span class="checkbox-text" style="padding: 0px; margin: 2px 0 0 0;">Номер клиента</span></label>
               </label>
               <label style="width: 150px;">
                  <label class="checkbox-conteiner"><input style="width: auto" class="checkbox" name="type" type="radio" value="1" id="temp_client" onchange="swapClientNumber()" style="margin-left: 20px;" <?php if (isset($_GET['once'])) { echo 'checked'; } ?>><span class="checkbox-visual"></span><span class="checkbox-text" style="padding: 0px; margin: 2px 0 0 0;">Разовый клиент</span></label>
               </label><br>

               <label for="client_num">№ клиента</label>
               <input id="client_num" name="client_num" type="text"><br>

               <label for="name" style="vertical-align: middle;">Название</label>
               <textarea id="name" name="name" style="vertical-align: middle;"></textarea><br>

               <input type="button" class="classic btn_link" value="Повторить прошлый заказ" onclick="repeat_order()" style="margin-left: 110px; margin-top: 0px; color: #015aaa; text-decoration: underline; font-size: 12px;"><br>

               <label for="region">Регион</label> <select id="region" name="region" style="margin-bottom: 20px; min-width: 204px;">
                  <option value="default" selected>По-умолчанию</option>
               </select><br>

               <label for="address">Адрес</label>
               <input id="address" name="address" style="margin-bottom: 0px;"><br>
               <input type="button" class="classic btn_link" title="Если введённый адрес не совпадает с сохранёнными, нажмите кнопку для формирования кординат по новому адресу" value="Адрес разовой доставки" onclick="if($('#hidemap').css('display')!='none'){$('#hidemap').hide();}else{$('#hidemap').fadeIn().css( 'display', 'inline-block');check_point();}"
                  style="margin-left: 110px; margin-top: 0px; color: #015aaa; text-decoration: underline; font-size: 12px;">

               <label for="contact">Контакт</label><label for="hidden" hidden style="width: auto;"></label> <input id="contact" class="contact" name="contact" type="text" oninput="helper();">
               <div class="contact_help">Введите номер/номера телефонов через ;</div>
               <label for="date">Дата</label><input id="date" name="date" type="text" style="width: 123px; margin-left: 4px;"><label for="hidden" style="width: auto;"></label> <label class="checkbox-conteiner" style="display: contents; "><input class="checkbox" id="no_date" name="no_date" type="checkbox" style="width: auto; margin: 0px; height: 10px;"><span class="checkbox-visual" style="margin: 8px 6px 0 7px;"></span><span class="checkbox-text" style="padding: 0px; margin: 2px 0 0 0;">Без даты</span></label>

               <label for="time">Время</label> <input id="time" name="time" type="text" oninput="maskedTime();">

               <label for="time_d">Период</label> <select id="time_d" name="time_d" style="min-width: 204px;">
                  <option value="" selected>Выбрать период</option>
                  <?php echo select_period(); ?>
               </select>
            </div>
            <div style="float: right; margin-top: 225px; max-width: 300px;">
               <label for="notice">Примечание</label> <textarea id="notice" name="notice" style="vertical-align: top; height: 80px;"></textarea>
            </div>


            <label for="cords" style="display: none;">Координаты</label><input id="cords" name="cords" hidden readonly style="display: none;">
            <input type="hidden" id="name-id">

            <!--            <label for="address_new"></label><input class="classic" id="address_new" name="address_new"type="checkbox"> Новый адрес-->
            <br>
            <input type="hidden" id="address-id">
            <label for="units">Товары</label>
            <div class="select_units">
               <div id="div_units_0">
                  <select id="wrapper" onchange="updateWaterEquip();"></select>
                  <input id="count" placeholder="Кол-во" autocomplete=false oninput="culcPrice(); updateWaterEquip();" style="width: 50px;">
                  <input id="price" placeholder="Цена" autocomplete=false oninput="culcPrice(); updateWaterEquip();" style="width: 50px;">
                  <input id="add_select" value="+" type="button" onclick="addSelect(); updateWaterEquip();" class="btn_link" style="width: 10px; color: #015aaa; font-size: 18px;font-weight: 700;">
                  <input id="remove_select" value="-" type="button" onclick="removeSelect(this.parentNode.id); culcPrice(); updateWaterEquip();" class="btn_link" style="width: 10px; color: #015aaa; font-size: 25px;font-weight: 700;">

               </div>
            </div>
            <label for="dep">Зачёт/Залог</label> <input id="dep" name="dep" type="text"><br>
            <label for="tank_empty_now">Сданная тара</label> <input id="tank_empty_now" name="tank_empty_now" type="text"><br>
            <label for="tank_bb">Тара к возврату</label> <input id="tank_bb" name="tank_b" type="text"><br>

            <label>Выбор оплаты</label>
            <select style="height: 22px; width: 110px;" onchange="changeCost();" class="pay_type_select">
               <option selected id="it_nal" name="selec_pr" value="0" checked>Наличный</option>
               <option id="it_bez" name="selec_pr" value="1">Безналичный</option>
            </select>
            <input id="add_order_sum" name="cash" type="text" style="width: 75px; margin-left: 10px;"> <br>

            <label for="on_floor">Подъем этаж</label> <input id="on_floor" name="on_floor" type="text" style="width: 200px;">


            </br>
            <label for="driver">Водитель</label> <select id="driver" name="driver" style="margin: 5px 0 20px 0; max-width: 204px; width: 204px;">
               <?php echo select_driver(); ?>
            </select>
            </br>
            <label for="storage">Склад: </label><select id="storage" class="storage" name="storage" style="margin: 5px 0 20px 4px; max-width: 204px; width: 204px;">

            </select>
            </br>
            <label for="status">Статус</label> <select id="status" name="status" style="margin: 5px 0 20px 0; max-width: 204px; width: 204px;">
               <option value="0" selected>В работе</option>
               <option value="1">Отмена</option>
               <option value="2">Доставлен</option>
               <option value="3">Перенос</option>
            </select> <br>

         </div>
         <input type="text" name="water_equip" id="water_equip" value="" style="display: none;"> <input type="text" name="cash_formula" id="cash_formula" value="" style="display: none;">
         <div style="width: 690px; text-align: right;">
            <input class="classic reset_button" id="submit" name="submit" type="submit" value="Отменить" style="width: auto;">
            <input class="classic search_button" id="submit" name="add_order" type="submit" value="Добавить" style="width: auto;">
         </div>

      </form>

      <div class="migrate_info" style="display: none;">
         <table id="order_info" style="text-align:center; border: 3px;">
            <caption>Информация о заказе</caption>
            <!-- Информация о необработанном заказе -->
            <tr>
               <td>Имя заказчика</td>
               <td>
                  <div id="info_name">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Регион</td>
               <td>
                  <div id="info_region">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Адрес</td>
               <td>
                  <div id="info_address">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Контакт</td>
               <td>
                  <div id="info_contact">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Дата</td>
               <td>
                  <div id="info_date">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Период</td>
               <td>
                  <div id="info_time">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Примечание</td>
               <td>
                  <div id="info_notice">пусто</div>
               </td>
            </tr>
            <tr>
               <td>Товары:</td>
               <td>
                  <div id="info_order">пусто</div>
               </td>
            </tr>
         </table>
      </div>
   </div>
   <div id="hidemap" style="width: 690px; margin-left: 8px;">
      <div id="map" style="width: 690px;"></div>
   </div>
   <div>
 <script>
   var myMap;
   var myCollection;
   var get = "<?php echo "get_".$_GET['once'] ?>";
   var count_order = 1;
   var units = '';

   /**
    * Конечные операции
   */
   $(document).ready(function () {
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
               errorPlacement: function(error, element) {
                   error.insertBefore(element);
               }
           }
      });
   });

 /**
    * Функция для добавления заказа
   */

   function swapClientNumber() {
      if (get=="get_once") {
          window.location = "/iwaterTest/admin/add_order/";
      } else {
          window.location = "/iwaterTest/admin/add_order?once=once";
      }
   }

   /**
    * Маска для указания времени доставки
   */
   function maskedTime() {
      /**
       * Входные параметры
       */
      var input_text = $('#time').val();
      var input_length = input_text.length;

      /**
       * Выходные параметры
       */
      var output_text = "";

      if (isNumeric(input_text[input_length - 1])) {
         output_text = input_text;
      } else {
         output_text = input_text.substr(0, input_length - 1);
      }

      var points = true;
      var spec_char = 0;
      var up_char = true;

      /**
       * Форматирование полей в периоды
       */
      for (var i = 0; i < output_text.length; i++) {
         if (spec_char == 1 && !points && output_text[i + 1] != " " && output_text[i + 1] != "-") {
            if (up_char) {
               output_text += "-";
               up_char = false;
            } else {
               output_text += " ";
               up_char = true;
            }

            spec_char = -1;
            points = true;
            i++;
         } else if (spec_char == 1 && points && output_text[i + 1] != ":") {
            output_text += ":";
            spec_char = -1;
            points = false;
            i++;

            /**
             * Чтение предыдущих спец. символов
             */
         } else if (output_text[i + 1] == " ") {
            spec_char = -1;
            i++;
            points = true;
            up_char = true;
         } else if (output_text[i + 1] == "-") {
            spec_char = -1;
            i++;
            points = true;
            up_char = false;
         } else if (output_text[i + 1] == ":") {
            spec_char = -1;
            i++;
            points = false;
         }

         spec_char++;
      }

      $('#time').val(output_text);
   }

   /**
    * Проверка на "тип"
   */
   function isNumeric(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);

      // Метод isNaN пытается преобразовать переданный параметр в число.
      // Если параметр не может быть преобразован, возвращает true, иначе возвращает false.
      // isNaN("12") // false
   }

   /**
    * Подсказка к полю с ценой
   */
   function helper() {
       if ($('.contact').val() == '') {
           $('.contact_help').css("opacity", 0);
       } else {
           $('.contact_help').css("opacity", 1);
       }
   }

   /**
    * Все заполненные товары преобразуются в serialize php массив и пишутся в скрытую строку
   */
   function updateWaterEquip() {
      var text = "a:" + count_order + ":{";
      var water_equip = [];
      for (var k = 0; k < count_order; k++) {
         div_cur = $('#div_units_' + k);

         id = div_cur.children('#wrapper');
         value = id.val();

         count_ord = div_cur.children('#count');
         selected = count_ord.val();

         text += "i:" + value + ";i:" + selected + ";";
         water_equip[value] = selected;
      }

      text += "}";
      $("#water_equip").val(text);
   }

   /**
    * Подсчёт суммы за заказ исходя из количеств и цен
   */
   function culcPrice() {
     full_cost = 0;
     formula = '';

     for (id = 0; id < count_order; id++) {
       full_cost += $('#div_units_' + id).children('#count').val() * $('#div_units_' + id).children('#price').val();
       formula += $('#div_units_' + id).children('#price').val() + '*' + $('#div_units_' + id).children('#count').val() + '+';
     }

     formula = formula.substring(0, formula.length -1);
     $('#cash_formula').val(formula);
     console.log($('#cash_formula').val());

     $('#add_order_sum').val(full_cost.toFixed(2));
   }

   /**
    * Запись списка товаров в select
   */
	function parseProductFromDB(unit_list) {
		for (var i = 0; i < unit_list.length; i++) {
			$('<option>', { id: i, value: unit_list[i]['id'], text: unit_list[i]['name']}).appendTo('#wrapper');
		}
	}

   /**
    * Загру\ координат для временного адреса
   */
   function check_point() {
      var address = $("#address");
     var region = $("#region");
     var myGeocoder = "";
     var address_string = 'Россия ' + region.val() + ' ' + address.val();
     $.ajax({
                       url: "/iwaterTest/backend.php",
                       type: "POST",
                       data: {
                           order_map: "",
                           address: address_string
                       },
                       success: function (data) {
                          myGeocoder = response(data);
                          console.log(response(data)); 
                       }
                   });

     console.log(address_string);
//                myCollection.removeAll();
     //var myGeocoder = ymaps.geocode(address_string);

      myGeocoder.then(
         function(res) {
            coords = res.geoObjects.get(0).geometry._coordinates;

            myPlacemark = new ymaps.Placemark(
               [coords[0], coords[1]], {
                  iconContent: 0,
                  hintContent: "Адресс №",
                  address_num: 1
               }, {
                  draggable: true
               });
            myMap.geoObjects.add(myPlacemark);
            $('#cords')[0].value = coords[0] + ',' + coords[1];
            $("label[for='cords']").show();
            $('#cords').show();

            myPlacemark.events.add("dragend", function(e) {
               cords = e.get('target').geometry.getCoordinates();
               var address_num = e.get('target').properties._data.address_num;
               $('#cords').val(cords);
            }, myPlacemark);

         },
         function(err) {
            //console.log('Ошибка');
         }
      );
   }

   /**
    * Загрузка данных при повторении заказа
   */
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

   /**
    * Заполнение данных при повторении заказа
   */
   function data_fill_order(data) {
      var last = data.getElementsByTagName('row')[0];

      $('#time').val(last.getElementsByTagName('cell')[1].childNodes[0].nodeValue);
      $('#time_d').val(last.getElementsByTagName('cell')[2].childNodes[0].nodeValue);
      $('#notice').val(last.getElementsByTagName('cell')[3].childNodes[0].nodeValue);

      var priceArray = [];
      var array = [];


      if (last.getElementsByTagName('cell')[7].childNodes[0].nodeValue != "") {
         $('.pay_type_select').val('0');
         array = last.getElementsByTagName('cell')[7].childNodes[0].nodeValue.split("+");
      } else {
         $('.pay_type_select').val('1');
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
      updateWaterEquip();

      $('#dep').val(last.getElementsByTagName('cell')[6].childNodes[0].nodeValue);
      $('#on_floor').val(last.getElementsByTagName('cell')[9].childNodes[0].nodeValue);
   }

   /**
    * Подсчёт суммы заказа
   */
   function changeCost() {
      var inputName = $('#add_order_sum').attr('name');
      var selectArea = $('.pay_type_select').val();

      if (selectArea == 1) {
         $('#add_order_sum').attr('name', 'cash_b');
      } else {
         $('#add_order_sum').attr('name', 'cash');
      }

      inputName = $('#add_order_sum').attr('name');
   }

   /**
    * Добавить товар в заказ
   */
   function addSelect() {
      $("#div_units_0").clone(true).attr('id', 'div_units_' + count_order).appendTo(".select_units").find('input:text').val('');
      count_order++;
   }

   /**
    * Удалить товар из заказа
   */
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
      }
   }

   /**
    * Если пользователь перешёл со страницы необработанных заявок, справа должно быть окошко с информацией из заявки
   */
   if (<?php if (isset($_GET['migrate_id'])) { echo $_GET['migrate_id']; } else { echo 0; } ?> > 0) {
      $.ajax({
        type: "POST",
        url: "/iwaterTest/backend.php",
        data: {
         migrate_ord: <?php if (isset($_GET['migrate_id'])) { echo $_GET['migrate_id']; } else { echo 0; } ?>
        },
        datatype: "json",
        success: function (data) {
           $('.migrate_info').css("display", "inline-table");
         cat = JSON.parse(JSON.stringify(data).toString());

         var dt = new Date(cat['date'] * 1000);

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
         }

         $('#info_order').html(water_list);
      }
      });
   }

   $(function () {

      // Подгрузка складов
      $.ajax({
         type: 'POST',
         url: '/iwaterTest/backend.php',
         data: {
            category_list: ""
         },
         response: "json",
         success: function (res) {
            var category = JSON.parse(res);

            for (var i = 0; i < category.length; i++) {
               $('<option>', { id: i, value: category[i]['id'], text: category[i]['name']}).appendTo('#storage');
               $('<option>', { id: i, value: category[i]['id'], text: category[i]['name']}).appendTo('#source');
            }
         }
      });

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
   </script>

    <style type="text/css">
    #add_order_form{

        border-radius: 8px;
        padding: 10px;
        width: 65% !important;
      }
    #add_order_form label {
      float: none;
      display: inline-block;
      width: 100px;
    }
    #add_order_form input {
      display: inline-block;
      width: 200px;
      margin: 5px 0 20px 0;
    }
    #add_order_form textarea {
      width: 196px;
    }
    .btn_link {
        border: none;
        background-color: #fff0;
        cursor: pointer;
    }
    .btn_link:focus {
        color: #015aaa;
    }
    .btn_link:hover {
        color: #015aaa;
    }
    .select_units {
    	display: inline-block;
    }

    #div_units_0 {
    	display: inline-block;
    }

    .content_add {
        background-color: #fff;
        border-radius: 9px;
        margin-top: 10px;
        overflow: hidden;
        padding: 20px;
    }

    .migrate_info {
       width: auto;
       height: auto;
       float: right;
       background-color: #fff;
       border-radius: 10px;
       padding: 10px 20px;
       margin: 81px 0 0 28px;
    }

    select {
    	height: 22px;
    }

    .main_form {
       display: flex;
    }

    .ui-autocomplete {
       width: 900px;
       border-radius: 5px;
       border: 1px solid #eff3f6 !important;
    }

    .ui-autocomplete li {
       background-color: #fff;
       /* border-bottom: 2px solid #eff3f6 !important; */
    	background-image: -webkit-linear-gradient(45deg, #74ccea 50%, transparent 50%);
       background-image: linear-gradient(45deg, #74ccea 50%, transparent 50%);
       background-position: 100%;
       background-size: 250%;
       transition: all 700ms linear 0ms;
    }

    .ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {
       background:rgba(0, 0, 0, 0);
       border: none;
    }

    .ui-autocomplete li:hover {
       background-position: 0;
       transition: background-position 700ms linear 0ms;
    }

    .contact_help{
        position: absolute;
        width: auto;
        margin-left: 100px;
        margin-top: -20px;
        border: 1px solid;
        border-radius: 1px 5px 5px 5px;
        padding: 3px;
        background-color: #d5d5d5;
        color: #898989;
        opacity: 0;
        text-align: center;
        transition: all 0.6s;
     }

     #add_order_form{
        width: 460px;
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

     /* КАСТОМНЫЙ ЧЕКБОКС */

     .checkbox-conteiner {
       min-width: 160px !important;
       display: block;
       cursor: pointer;
       float:left;
     }
     .checkbox {
       display: none !important;
     }
     .checkbox-text {
        border: none;
        line-height: 20px;
        background-color: #fff;
     }
     .checkbox-visual {
       position: relative;
       display: inline-block;
       vertical-align: top;
       margin-right: 12px;
       padding: 0px;
       width: 15px;
       height: 15px;
       border-radius: 12px;
       border: none;
       background-color: #9c9c9c;
     }
     .checkbox-visual:before {
       content: '';
       display: none;
       position: absolute;
       top: 50%;
       left: 50%;
       margin: -5px 0 0 -6px;
       height: 4px;
       width: 8px;
       border-width: 0 0 4px 4px;
       -webkit-transform: rotate(-45deg);
       -moz-transform: rotate(-45deg);
       -ms-transform: rotate(-45deg);
       -o-transform: rotate(-45deg);
       transform: rotate(-45deg);
     }
     .checkbox:checked ~ .checkbox-visual {
       background-color: : #015aaa;
     }
     .checkbox:checked ~ .checkbox-visual:before {
       display: block;
     }
     .checkbox:checked ~ .checkbox-visual {
        background-color: #015aaa;
     }
</style>
