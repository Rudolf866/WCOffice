<div class="main">
   <img src="../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>

<div class="name_title name_analytics">
                <div class="name_position" style="float: left;">Предиктивная аналитика</div>
            </div>
            <br>
            <div class="main_tab" id="tab_main">
              <div>
              <ul class="tab_analytics">
  <li><a href="#" class="tablinks" onclick="openAnalytics(event, 'list_delay_order1')">Просроченные заказы</a></li>
  <li><a href="#" class="tablinks" onclick="openAnalytics(event, 'list_last_order1')">Давно не заказывающие клиенты</a></li>
  <li><a href="#" class="tablinks" onclick="openAnalytics(event, 'list_rest_order1')">Остатки тары у клиентов</a></li>
  <li><a href="#" class="tablinks" onclick="openAnalytics(event, 'list_success_analytics1')">Успешность водителей</a></li>
  <li><a href="#" class="tablinks" onclick="openAnalytics(event, 'list_lost_client1')">Потерянные клиенты</a></li>
</ul>
</div>
<div id="list_delay_order1" class="tabcontent">
      <div class="list_lists_date">
            С
            <input type="text" class="list_lists_from" name="" value="" placeholder="Начальная дата">
            по
            <input type="text" class="list_lists_to" name="" value="" placeholder="Конечная дата">
            <input type="submit" class="lists_filter" name="lists_filter" value="Фильтр">
            <input id="order_excel" type="button" class="classic reset_button" value="Выгрузить в эксель" onclick="excelOut();">
         </div>
  <div class="delay_analytics">
            <!-- Статистика по просроченным заказам -->
            <!-- jQgrid таблица -->
            <table id="list_delay_order" style="width: 100%;"></table>
            <div id="pager_delay_order"></div>
            <script type="text/javascript">
               $("#list_delay_order").jqGrid({
                  mtype: "POST",
                  url:'/iwaterTest/backend.php?list_delay_order<?php if(isset($_GET['date'])) { echo '&date=' . $_GET['date']; } ?>',
                  datatype: "xml",
                  colNames:['№ заказа', 'Водитель', 'Время', 'Период', 'Доставили'],
                  colModel:[
                     {name:'id',  key : true, index:'id', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'driver', index:'driver', align:"center",  sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'time', index:'time', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'period', index:'period', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'come', index:'come', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                  ],
                  viewrecords: true,
                  caption: 'Просроченые заказы',
                  sortable: true,
                  shrinkToFit: true,
                  pager: "#pager_delay_order",
                  rowNum: 30,
                  rowList: [30, 50, 100],
                  sortname: 'id',
                  viewrecords: true,
                  sortorder: "desc",
                  height: 385
               });
               $("#list_delay_order").jqGrid('setGridWidth', 1010);
               $("#list_delay_order").jqGrid('setGridHeight', 490);
               $("#list_delay_order").jqGrid('navGrid', "#pager_delay_order", {edit: false, add: false, del: false, nav: true, search: false, refresh: false}, {}, {}, {}, {
                  multipleSearch: false,// Поиск по нескольким полям
                  multipleGroup: false, // Сложный поиск с подгруппами условий
                  showQuery: false
               });

               $("#list_delay_order").navButtonAdd('#pager_delay_order', {
                   buttonicon: "",
                   title: "Выбрать дату",
                   caption: "Выбрать дату",
                   position: "last",
                   onClickButton: selectDate
               });

               $("#list_delay_order").navButtonAdd('#pager_delay_order', {
                   buttonicon: "",
                   title: "Выгрузить",
                   caption: "Выгрузить",
                   position: "last",
                   onClickButton: excelOut
               });

               $("#list_lists_from").datepicker({
                 onSelect: function(dateText) {
                    window.location = "/iwaterTest/admin/analytics?date=" + dateText;
                 },
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

                $('.list_lists_from').datepicker({
                   showOn: 'button',
                   buttonText: 'Show date',
                   buttonImageOnly: true,
                   buttonImage: '/iwaterTest/css/image/calendar.png',
                });
                $('.list_lists_to').datepicker({
                   showOn: 'button',
                   buttonText: 'Show date',
                   buttonImageOnly: true,
                   buttonImage: '/iwaterTest/css/image/calendar.png',
                });
               });


               function excelOut() {
                  $('.loading').show();
                  $.ajax({
                     type: 'POST',
                     url: "/iwaterTest/backend.php",
                     data: {
                        createExcell_delay_order: "<?php if(isset($_GET['date'])) { echo '&date=' . $_GET['date']; } ?>"
                     },
                     success: function(req) {
                        $('.loading').hide();
                        location.href = '/iwaterTest/files/' + req;
                     }
                  });
               }

               function selectDate(event) {
                  // лoвим клик пo ссылки с id="go"
                    event.preventDefault(); // выключaем стaндaртную рoль элементa
                    $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                       function(){ // пoсле выпoлнения предъидущей aнимaции
                          $('#modal_datepicker')
                             .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                             .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                    });
               }
            </script>
          </div>
</div>

<div id="list_last_order1" class="tabcontent">
  <div class="last_analytics">
              <!-- Статистика по давно не заказывающим клиентам -->
             <table id="list_last_order" style="width: 100%;"></table>
            <div id="pager_last_order"></div>
            <script type="text/javascript">
              var state = 0;
               $("#list_last_order").jqGrid({
                  mtype: "POST",
                  url:'/iwaterTest/backend.php?list_last_order',
                  datatype: "xml",
                  colNames:['№ клиента', 'Имя клиента', 'Последний заказ', 'Расчетная дата'],
                  colModel:[
                     {name:'id',  key : true, index:'id', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'name', index:'name', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'last_order', index:'last_order', align:"center",sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'next_order', index:'next_order', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                  ],
                  viewrecords: true,
                  autowidth: false,
                  caption: 'Давно не заказывающие клиенты',
                  sortable: true,
                  shrinkToFit: true,
                  pager: "#pager_last_order",
                  rowNum: 30,
                  rowList: [30, 50, 100],
                  sortname: 'c.id',
                  viewrecords: true,
                  sortorder: "desc",
                  height: 360,
                  hiddengrid: false,
                  onHeaderClick: function(){
                    if (state) {$("#list_lost_client").jqGrid('setGridState','hidden'); state = 1;}
                 if (!state) { $("#list_lost_client").jqGrid('setGridState','visible'); state = 0;}
                }
               });
               $("#list_last_order").jqGrid('setGridWidth',1015);
               $("#list_last_order").jqGrid('setGridHeight',500);
               $("#list_last_order").jqGrid('navGrid', "#pager_last_order", {edit: false, add: false, del: false, nav: true, search: false, refresh: false}, {}, {}, {}, {
                  multipleSearch: false,// Поиск по нескольким полям
                  multipleGroup: false, // Сложный поиск с подгруппами условий
                  showQuery: false
               });

               $("#list_last_order").navButtonAdd('#pager_last_order', {
                   buttonicon: "none",
                   title: "Выгрузить",
                   caption: "Выгрузить",
                   position: "last",
                   onClickButton: excelOut_last
               });

               function excelOut_last() {
                  $('.loading').show();
                  $.ajax({
                     type: 'POST',
                     url: "/iwaterTest/backend.php",
                     data: {
                        createExcell_last_order: "",
                        table_type: "last"
                     },
                     success: function(req) {
                        $('.loading').hide();
                        location.href = '/iwaterTest/files/' + req;
                     }
                  });
               }

            </script>


</div>
</div>
<div id="list_rest_order1" class="tabcontent">
  <div class="rest_analytics">
            <!-- Статистика по остаткам тары у клиентов -->
            <!-- jQgrid таблица -->
            <table id="list_rest_order" style="width: 100%;"></table>
            <div id="pager_rest_order"></div>
            <script type="text/javascript">
               $("#list_rest_order").jqGrid({
                  mtype: "POST",
                  url:'/iwaterTest/backend.php?list_rest_order',
                  datatype: "xml",
                  colNames:['№ клиента', 'Имя', 'Количество тары', 'Последний заказ'],
                  colModel:[
                     {name:'id',  key : true, index:'id', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'name', index:'name', align:"center",  sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'tanks', index:'tanks', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'last_order', index:'last_order', align:"center",  width:240, sortable: false, editable: false}
                  ],
                  viewrecords: true,
                  autowidth: false,
                  caption: 'Остатки тары у клиентов',
                  sortable: true,
                  shrinkToFit: true,
                  pager: "#pager_rest_order",
                  rowNum: 30,
                  rowList: [30, 50, 100],
                  sortname: 'tanks',
                  viewrecords: true,
                  sortorder: "desc",
                  height: 250
               });
                $("#list_rest_order").jqGrid('setGridWidth',1015);
                $("#list_rest_order").jqGrid('setGridHeight',500);
               $("#list_rest_order").jqGrid('navGrid', "#pager_rest_order", {edit: false, add: false, del: false, nav: true, search: false, refresh: false}, {}, {}, {}, {
                  multipleSearch: false,// Поиск по нескольким полям
                  multipleGroup: false, // Сложный поиск с подгруппами условий
                  showQuery: false
               });

               $("#list_rest_order").navButtonAdd('#pager_rest_order', {
                   buttonicon: "",
                   title: "Выгрузить",
                   caption: "Выгрузить",
                   position: "last",
                   onClickButton: excelOut_r
               });
               function excelOut_r() {
                  $('.loading').show();
                  $.ajax({
                     type: 'POST',
                     url: "/iwaterTest/backend.php",
                     data: {
                        createExcell_rest_order: ""
                     },
                     success: function(req) {
                        $('.loading').hide();
                        location.href = '/iwaterTest/files/' + req;
                     }
                  });
               }
            </script>
          </div>
</div>
<div id="list_success_analytics1" class="tabcontent">
  <div class="success_analytics">
            <!-- Статистика по успешности водителей -->
            <!-- jQgrid таблица -->
            <table id="list_success_analytics" style="width: 100%;"></table>
            <div id="pager_success_analytics"></div>
            <div class="table_conteiner">
           <table id="list"></table>
        </div>
        <div id="pager"></div>
        <div class="custom_paginator" style="display: inline-block; float: right; padding: 3px 10px 0px 110px;"></div>
            <script type="text/javascript">
               $("#list_success_analytics").jqGrid({
                  mtype: "POST",
                  url:'/iwaterTest/backend.php?list_success_analytics',
                  datatype: "xml",
                  colNames:['№', 'Имя', 'Коэффициент успешность'],
                  colModel:[
                     {name:'id',  key : true, index:'id', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'name', index:'name', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'stat', index:'stat', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}}
                  ],
                  viewrecords: true,
                  autowidth: false,
                  caption: 'Успешность водителей',
                  sortable: true,
                  shrinkToFit: true,
                  pager: "#pager_success_analytics",
                  rowNum: 30,
                  rowList: [30, 50, 100],
                  sortname: 'stat',
                  viewrecords: true,
                  sortorder: "desc",
                  height: 250
               });
               $("#list_success_analytics").jqGrid('navGrid', "#pager_success_analytics", {edit: false, add: false, del: false, nav: true, search: false, refresh: false}, {}, {}, {}, {
                  multipleSearch: false,// Поиск по нескольким полям
                  multipleGroup: false, // Сложный поиск с подгруппами условий
                  showQuery: false
               });

               $("#list_success_analytics").navButtonAdd('#pager_success_analytics', {
                   buttonicon: "",
                   title: "Выгрузить",
                   caption: "Выгрузить",
                   position: "last",
                   onClickButton: excelOut_s
               });
               $("#list_success_analytics").jqGrid('setGridWidth',1015);
                $("#list_success_analytics").jqGrid('setGridHeight',500);
               var localPositionMargin = 0; // Из за анимации позиция не сразу проставляется конечной, из-за этого можно улететь за границу таблицы, этот фикс считает без задержки анимаций

                    // Метод для прокрутки таблицы стрелочками
                    function horizationScrollControl(side) {
                       var min = -1100;
                       var max = 0;
                       var tableBlock = $('#gbox_list');

                       if (side == 'left' && localPositionMargin < max) {
                          localPositionMargin += 275;
                          tableBlock.animate({
                            'margin-left': "+=275px" // уменьшение ширины границы элемента на два пикселя от текущего значения
                        }, '1', "linear");
                       } else if (side == 'right' && localPositionMargin > min) {
                          localPositionMargin -= 275;
                          tableBlock.animate({
                            'margin-left': "-=275px" // уменьшение ширины границы элемента на два пикселя от текущего значения
                        }, '1', "linear");
                       }
                    }

                    function createPager() {
                        // $("#editGrid").trigger("reloadGrid",[{page:10}]);
                        var currentPage = $("#list_success_analytics").getGridParam('#pager_success_analytics');
                        var start = currentPage < 3 ? 1 : currentPage - 2;
                        var html = '<input type="button" name="" value="<" onclick="goToPage(' + (currentPage - 1) + ');">'; // Строка с пагинотором

                        for (var i = start; i <= start + 5; i++) {
                           html += '<input type="button" name="" value="' + i + '" onclick="goToPage(' + i + ')">';
                        }

                        html += '<input type="button" name="" value=">" onclick="goToPage(' + (currentPage + 1) + ')">';
                        $('.custom_paginator').html(html);
                    }

                    function goToPage(page) {
                       console.log('go to ' + page);
                        $("#list_success_analytics").trigger("reloadGrid",[{page:page}]);
                    }
               function excelOut_s() {
                  $('.loading').show();
                  $.ajax({
                     type: 'POST',
                     url: "/iwaterTest/backend.php",
                     data: {
                        createExcell_success_analytics: ""
                     },
                     success: function(req) {
                        $('.loading').hide();
                        location.href = '/iwaterTest/files/' + req;
                     }
                  });
               }
            </script>
          </div>
</div>
<div id="list_lost_client1" class="tabcontent">
  <div id="lost">
    <table id="list_lost_client" style="width: 100%;" ></table>
            <div id="pager_lost_client"></div>
            <script type="text/javascript">
              var state = 1;
               $("#list_lost_client").jqGrid({
                  mtype: "POST",
                  url:'/iwaterTest/backend.php?list_lost_client',
                  datatype: "xml",
                  colNames:['№ клиента', 'Имя клиента', 'Последний заказ', 'Расчетная дата'],
                  colModel:[
                     {name:'id',  key : true, index:'id', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'name', index:'name', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'last_order', index:'last_order', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                     {name:'next_order', index:'next_order', align:"center", sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                  ],
                  viewrecords: true,
                  autowidth: false,
                  caption: 'Потерянные клиенты',
                  sortable: true,
                  shrinkToFit: true,
                  pager: "#pager_lost_client",
                  rowNum: 30,
                  rowList: [30, 50, 100],
                  sortname: 'c.id',
                  viewrecords: true,
                  sortorder: "desc",
                  height: 360,
                  hiddengrid: false,
                  onHeaderClick: function(){
                   if (state) {$("#list_last_order").jqGrid('setGridState','hidden'); state = 1;}
                   if (!state) {$("#list_last_order").jqGrid('setGridState','visible'); state = 0;}
                }
               });
               $("#list_lost_client").jqGrid('setGridWidth',1015);
               $("#list_lost_client").jqGrid('setGridHeight',500);
               $("#list_lost_client").jqGrid('navGrid', "#pager_lost_client", {edit: false, add: false, del: false, nav: true, search: false, refresh: false}, {}, {}, {}, {
                  multipleSearch: false,// Поиск по нескольким полям
                  multipleGroup: false, // Сложный поиск с подгруппами условий
                  showQuery: false
               });

               $("#list_lost_client").navButtonAdd('#pager_lost_client', {
                   buttonicon: "none",
                   title: "Выгрузить",
                   caption: "Выгрузить",
                   position: "last",
                   onClickButton: excelOut_lost
               });

               function excelOut_lost() {
                  $('.loading').show();
                  $.ajax({
                     type: 'POST',
                     url: "/iwaterTest/backend.php",
                     data: {
                        createExcell_last_order: "",
                        table_type: "lost"
                     },
                     success: function(req) {
                        $('.loading').hide();
                        location.href = '/iwaterTest/files/' + req;
                     }
                  });
               }

            </script>
</div>
</div>

            </div>
    </div>

   <script type="text/javascript">
function openAnalytics(evt, tabName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tabcontent.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the link that opened the tab
    document.getElementById(tabName).style.display = "block";
    // evt.currentTarget.className += " active"; // Пришлось закрыть, чтобы была подгрузка данных при старте страницы
}

$(document).ready(function(event) {
   openAnalytics(event, 'list_delay_order1');
   $('.tab_analytics li:first-child a').focus();
   $('.tab_analytics li:first-child a').addClass('active');
});
</script>
<!-- Временные стили, после стоит переснести в отдельный файл -->
<style media="screen">
.main_form{
  display: block;
}
.name_title{
  cursor: pointer;
}
.ui-datepicker-trigger {
    margin: 3px 0 -6px 0 !important;
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

/* Float the list items side by side */
ul.tab_analytics li {float: left;}

/* Style the links inside the list items */
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

/* Change background color of links on hover */
ul.tab_analytics li a:hover {background-color: #ddd;}

/* Create an active/current tablink class */
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

/*.tab-wrapper input{
    background-color: #e6e6e6;
    height: 35px;
    line-height: 35px;
    min-width: 50px;
    padding: 0px 20px;
    text-align: center;
    float: left;
    color: #878685;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border-right: 2px solid #c7c7c7;
}
.tab-wrapper input:checked + label{
  background-color: #000;
}
.tab-wrapper input:checked + label + .tab-item{display: block;}*/

.ui-datepicker-trigger {
   margin: 3px 0 6px 0;
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

/* Таблица jqgrid*/

  .ui-jqgrid .ui-jqgrid-pager,
    .ui-jqgrid .ui-jqgrid-caption {
        display: none !important;
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

    .reset_button:active{
        padding: 2px 30px;
    }

    .s-ico {
        display: none !important;
    }
 .ui-jqgrid .ui-jqgrid-bdiv {
        /* height: 400px !important; */
        /* width: 1020px !important; */
    }

    .ui-jqgrid .ui-jqgrid-view,
    .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all {
        /* width: 1020px !important; */
    }

    .ui-state-default, .ui-widget-content .ui-state-default {
        /* width: auto !important; */
    }
#date_pick {
   height: 200px;
}

.ui-datepicker-inline {
   height: 200px;
}


   /*.main_form {
      width: 100%;
      height: 100%;
      display: flex;
      flex-flow: row wrap;
   }
   .big_area {
      height: 60%;
      width: :100%;
      display: inline-flex;
      min-height: 400px;
   }
   .small_area {
      height: 40%;
      width: 100%;
      display: inline-flex;
      margin-top: 30px;
      min-width: 1350px;
   }
   .rest_analytics {
      height: 100%;
      width: 50%;
   }
   .delay_analytics {
      height: 100%;
      width: 50%;
   }
   .last_analytics {
      height: 100%;
      width: 50%;
      margin-left: 100px;
   }
   .success_analytics {
      height: 100%;
      width: 50%;
   }*/
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
</style>

<!-- Испольняемый код -->
<script type="text/javascript">

</script>
