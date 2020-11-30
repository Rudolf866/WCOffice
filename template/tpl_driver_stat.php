<div class="main">
   <img src="../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>
    <div class="main_form">
         <div class="name_title name_driver">
                <div class="name_position">Статистика водителей</div>
            </div>
            <br>
            <div class="tab-container">
    <div class="tab-wrapper" style="display: flex; padding: 20px 20px 20px 7px;">
      <div class="select_driver"></div>
             <div class="list_lists_date123" style="display: inline-flex; padding: 3px 10px 0 0;">
            <p style="margin: 8px 0 0px 0;">Дата</p>
            </div>
            <input id="date_list" name="date" type="text"  value="<?php if (isset($_GET['date'])) echo $_GET['date']?>" placeholder="Дата выборки" style="margin-right: 10px; display: inline-block; margin-left: 33px; max-width: 160px;">
            <input type="submit" class="lists_filter" name="lists_filter" value="Поиск" onclick="window.location = '/iwaterTest/admin/driver_stat?date=' + $('#date_list').val() + '&driver=' + $('#driver').val();" style="margin: 2px 5px; display: inline-block; padding: 0px 13px; width: 140px;">
            <input type="submit" class="lists_filter" name="lists_filter" value="Сброс" onclick="window.location = '/iwaterTest/admin/driver_stat';" style="margin: 2px 0px; display: inline-block; padding: 0px 13px; width: 140px; background-color: #fff; color: #0157a4;">
         </div>
      <div class="table_driver">
      </div>
    </div>
  </div>
  <div class="date_picker" id="datepicker" style="height: 0px; max-width: 350px;">
     <div id="date_pick"></div>
  </div>


<script>

$('#date_list').datepicker({
   showOn: 'button',
   buttonText: 'Show date',
   buttonImageOnly: true,
   buttonImage: '/iwaterTest/css/image/calendar.png'
});

$("#modal_datepicker").datepicker({
   onSelect: function(dateText) {
     window.location = "/iwaterTest/admin/driver_stat?date=" + dateText;
   }
});

$(document).ready(function() { // вся мaгия пoсле зaгрузки стрaницы
   /* Зaкрытие мoдaльнoгo oкнa, тут делaем тo же сaмoе нo в oбрaтнoм пoрядке */
   $('#modal_datepicker_close, #overlay').click( function(){ // лoвим клик пo крестику или пoдлoжке
      $('#modal_datepicker')
         .animate({opacity: 0, top: '45%'}, 200,  // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
            function(){ // пoсле aнимaции
               $(this).css('display', 'none'); // делaем ему display: none;
               $('#overlay').fadeOut(400); // скрывaем пoдлoжку
            }
         );
   });


      $('.name_driver').click(function(){
     if ($('.tab-container').css('display') == 'none') {
        $('.tab-container').slideDown();
        $('.date_picker').css('height', 'auto');
     } else {
        $('.tab-container').slideUp();
        $('.date_picker').css('height', '0px');
      }
   });

   loadDriversInfo();
});

function selectDate(event) {
   $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
      function(){ // пoсле выпoлнения предъидущей aнимaции
        $('#modal_datepicker')
           .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
           .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
   });
}

// Получение таблицы со статистикой для водителей
function loadDriversInfo() {
    $.ajax({
      type: 'POST',
      url: "/iwaterTest/backend.php",
      data: {
         today_drivers: '',
         date: '<?php if (isset($_GET['date'])) { echo $_GET['date']; } ?>'
      },
      success: function(res) {
         var result = JSON.parse(res);
         var opt_driver = "";
         for (var i = 1; i <= result.length; i++) {
            var selected = (i == <?php if (isset($_GET['driver'])) { echo $_GET['driver']; } else { echo 0; } ?>) ? 'selected' : '';
            old = $('.select_driver').html();
            opt_driver += '<option value=' + i + ' ' + selected + '>' + result[i - 1]['name'] + '</option>';
            old_table = $('.table_driver').html();

            $('.table_driver').html(old_table + '<div class="tab-item" id="tab-content' + i + '" style="display:none; padding: 0px;width: 100%;"><table style="width: 100%; text-align: center; border-collapse:collapse;"><tr><th>Клиент<th>Адрес</th></th><th>Отмеченное время</th><th>Возврат тары</th><th>Расстояние до заказчика</th><th>Комментарий</th><th style="min-width: 75px;">Статус</th><tr>' + result[i - 1]['text'] + '</table></div>');
         }

         $(".tab-container").css("height", $(".tab-container").height() + $("#tab-content1").height());
         $('.select_driver').html(old + '<select class="classic" id="driver" name="driver  onchange="getval(this);" nth="1" style="float: left;margin-right: 30px; padding-left: 9px; width: 160px; height: 25px;">' + opt_driver + '</select>');
         $("#tab-content" + $('.classic').val()).css("display", "block");
         $(".classic").change(function() {
            $(".tab-container").css("height", "0");
            $(".tab-container").css("height", $(".tab-container").height() + $("#tab-content" + this.value).height());
            $(".tab-item").css("display", "none");

            $("#tab-content" + this.value).css("display", "block");

         });
      }
   });

   if ($('.tab-container').css('display') == 'block') { $('.date_picker').css('height', 'auto'); }
}
</script>
<style media="screen">
.main_form{
  display: block;
}
.name_title{
   margin-left: 20px;
  cursor: pointer;
}
/* СТИЛИ СКРОЛЛ ПАНЕЛИ */
   .table_conteiner::-webkit-scrollbar-track   {
    border-radius: 10px;
    background-color: #F5F5F5;
   }

   .table_conteiner::-webkit-scrollbar   {
    width: 12px;
    background-color: #F5F5F5;
   }

   .table_conteiner::-webkit-scrollbar-thumb   {
    border-radius: 10px;
    background-color: #65b6e9;
   }
   .status_list {
      background-color: #fff;
      border-radius: 4px;
      border: 0;
      padding: 15px 30px;
      margin: 0 0 8px -3px;
   }
   .status_list input[type=button] {
      background: #015aaa;
      border: 0;
      color: #fff;
   }
   #modal_link {
      background-color: #fff;
      width: 100px;
      height: 40px;
      position: absolute;
      text-align: center;
      border-radius: 6px;
      border: 2px solid #e3e9f1;
      color: #015aaa;
      text-decoration: none;
      font-size: 15px;
   }
   .custom_paginator input {
      width: 26px;
      height: 27px;
      margin-left: 5px;
      background-color: #fff;
   }

#gview_list_rest_order {
   margin-left: -6px;
}
#list_success_analytics{
  width: 100%;
}
/*таблица водителей */
th{
    color: #586579;
    padding-bottom: 20px;
}
/* Style the list */
.main_tab{
     background-color: white;
    border-radius: 10px;
    height: 590px;
    margin-top: 20px;
}

ul.tab_analytics {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    background-color: #eff3f6;
}

ul.tab_analytics li {float: left;}

ul.tab_analytics li a {
   display: inline-block;
   color: black;
   text-align: center;
   margin-right: 5px;
   border-radius: 10px 10px 0px 0px;
   text-decoration: none;
   transition: 0.3s;
   background-color: #e3e9f1;
   font-size: 14px;
   padding: 5px 19px;
}

ul.tab_analytics > li:last-of-type a {
   margin: 0px;
   padding-right: 23.9968px;
}

ul.tab_analytics li a:hover {background-color: #ddd;}

ul.tab_analytics li a:focus, .active {background-color: #fff;}

.ui-jqgrid-btable .ui-common-table{
width: 100%;
}
.ui-jqgrid-htable .ui-common-table {
  width: 100%;
}

.tab-container{position: relative;}
.tab-wrapper{
    width: 100%;
    overflow-x: hidden;
    font-family: sans-serif;
}
.tab-wrapper .tab-item,
.tab-wrapper input{display: block;}
.tab-wrapper .tab-item{
    background-color: #eff3f6;
    width: calc(100% - 40px);
    max-width: 100%;
    padding: 20px;
    float: left;
    position: absolute;
    left: 0px;
    top: 35px;
  }

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border-top: none;
}
   .datepicker_icon {
      width: 25px;
      height: 25px;
      display: inline-block;
      background-image: url(/iwaterTest/css/image/calendar.png);
   }

#date_pick {
   height: 200px;
}

   #modal_datepicker {
    width: 200px;
    height: 200px; /* Рaзмеры дoлжны быть фиксирoвaны */
    border-radius: 5px;
    border: 3px #000 solid;
    background: #fff;
    position: fixed; /* чтoбы oкнo былo в видимoй зoне в любoм месте */
    top: 45%; /* oтступaем сверху 45%, oстaльные 5% пoдвинет скрипт */
    left: 50%; /* пoлoвинa экрaнa слевa */
    margin-top: -150px;
    margin-left: -150px; /* тут вся мaгия центрoвки css, oтступaем влевo и вверх минус пoлoвину ширины и высoты сooтветственнo =) */
    display: none; /* в oбычнoм сoстoянии oкнa не дoлжнo быть */
    opacity: 0; /* пoлнoстью прoзрaчнo для aнимирoвaния */
    z-index: 1000; /* oкнo дoлжнo быть нaибoлее бoльшем слoе */
    padding: 0px 0px;
   }
   /* Кнoпкa зaкрыть для тех ктo в тaнке) */
   #modal_datepicker #modal_datepicker_close {
    width: 21px;
    height: 21px;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    display: block;
   }

      /* Пoдлoжкa */
   #overlay {
    z-index:999; /* пoдлoжкa дoлжнa быть выше слoев элементoв сaйтa, нo ниже слoя мoдaльнoгo oкнa */
    position:fixed; /* всегдa перекрывaет весь сaйт */
    background-color:#000; /* чернaя */
    opacity:0.8; /* нo немнoгo прoзрaчнa */
    -moz-opacity:0.8; /* фикс прозрачности для старых браузеров */
    filter:alpha(opacity=80);
    width:100%;
    height:100%; /* рaзмерoм вo весь экрaн */
    top:0; /* сверху и слевa 0, oбязaтельные свoйствa! */
    left:0;
    cursor:pointer;
    display:none; /* в oбычнoм сoстoянии её нет) */
   }

   .ui-datepicker-trigger {
      position: relative;
      right: 207px;
      width: 25px;
      height: 25px;
      display: inline-block;
      margin: 4px 4px 0px 4px;
   }
</style>

<!-- Испольняемый код -->
<script type="text/javascript">

</script>
