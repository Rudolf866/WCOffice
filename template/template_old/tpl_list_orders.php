<?php
   $access_level = check_perms('list_orders');
?>

<!-- RANGE ПО КОТОРОМУ ПЛАВАЮТ ЗНАЧЕНИЯ ПРАВОГО MARGIN ОТ 0 ДО -594 -->
<style type="text/css">
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
    .ui-jqgrid .inline-edit-cell{
      padding: 0px 0px 0px 0px;
    }


    .ui-jqgrid .ui-jqgrid-view,
    .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all {
        /* width: 1020px !important; */
    }

    .ui-state-default, .ui-widget-content .ui-state-default {
        /* width: auto !important; */
    }
    .table_conteiner {
      max-height: 600px;
      overflow-x: scroll;
      overflow-y: scroll;
   }
   .table_scroll_button {
      width: 30px;
      height: 30px;
      background-color: #015aaa;
      color: #fff;
      border-radius: 15px;
      padding: 1px 0px 0px 2px;
      font-size: 18px;
   }

   /* СТИЛИ СКРОЛЛ ПАНЕЛИ */
   .table_conteiner::-webkit-scrollbar-track   {
   	border-radius: 10px;
   	background-color: #F5F5F5;
   }

   .table_conteiner::-webkit-scrollbar   {
   	width: 9px;
      height: 9px;
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
</style>
<div class="main">
    <img src="/iwaterTest/css/image/loading-gallery.gif" id="loading" class="loading" alt="" style="width: 141px; display: none;z-index: 9999"/>
    <div class="clients_list_table">
        <div class="name_title">
            <div class="name_position">Список заказов</div>
            <div class="add_position"><a href="http://iwatercrm.ru/iwaterTest/admin/add_order/"><span class="img_add"></span>Добавить заказ</a></div>
        </div>
        <div class="search">

            <form>
                <label for="from" style="line-height: 30px; margin-right: 30px;">Искать по</label>
                <select class="edit_role" name="role" style="height: 23px; width: 150px; padding-left: 5px;">
				   <option value="mobile">Мобильный</option>  
                   <option value="client_order">id клиента</option>
                   <option value="name">Клиент</option>
                   <option value="driver">Водитель</option>
                   <option value="status">Статус</option>
                   <option value="equip">Оборудование</option>
                   
               </select>
               <label style="line-height: 30px; margin: 0 30px;">Данные</label><input class="data" id="set_filter" name="" type="text">
                <input class="search_button" type="button" value="Поиск" onclick="searchOrder();">
                <input class="reset_button" type="button" value="Сброс" onclick="window.location.reload();">
                <input id="order_excel" type="button" class="classic reset_button" value="Выгрузить в эксель" onclick="load_excel();">
            </form>
        </div>
        <div class="inline-block">
             <input name="multi_hidden" class="multi_hidden" type="hidden" value="multi">
        </div>
        <div class="inline-block form_status" style="display: none;">
            <div class="inline-block status_list">
                      <select id="status" name="status">
                        <option value="0" selected>В работе</option>
                        <option value="1">Отмена</option>
                        <option value="2">Доставлен</option>
                        <option value="3">Перенос</option>
                    </select>
                <input type="button" value="Изменить" onclick="change_statuses(this)">
            </div>
        </div>
        <div class="table_conteiner">
           <table id="list"></table>
        </div>
        <div id="pager"></div>
        <input type="button" class="classic multi search_button"  value="Мультивыделение" placeholder="Дата путевого (d/m/Y)">
        <div class="custom_paginator" style="display: inline-block; float: right; padding: 3px 10px 0px 110px;"></div>
        <a href="/iwaterTest/admin/logs?logs=order" style="color: #000; float: right; margin: 16px;">Журнал операций</a>
        <input id="all_order_excel" type="button" class="classic btn_link" value="Выгрузить базу данных" onclick="all_load_excel();" style="float: right; margin-top: 16px;">



        <div>
            <div id="modal_form"><!-- Сaмo oкнo -->
                <span id="modal_close">X</span> <!-- Кнoпкa зaкрыть -->
                <div class="title_date">Дата новой доставки</div>
                <form id="form_datepicker" method="post" action="/iwaterTest/backend.php">
                    <input id="date_client_id" name="id" type="hidden">
                    <p class="datepicker_p"><label for="datepicker">Дата: </label><input name="date" type="text"
                                                                                         id="datepicker"></p>
                    <p class="datepicker_p"><label for="reason">Причина: </label> <select id="reason" name="reason">
                        <option value="Не успели">Не успели</option>
                        <option value="Поломка авто">Поломка авто</option>
                            <option value="Ошибочный ввод" selected>Ошибочный ввод</option>
                            <option value="Перенос по просьбе клиента">Перенос по просьбе клиента</option>
                            <option value="Прочее">Прочее</option>
                    </select>

<!--                    <p class="datepicker_p"><label for="time">Время: </label><input id="time" name="time" type="text">-->
                    </p>
                    <div id="datepicker_submit">
                        <input id="submit" name="submit" type="submit" value="Сохранить изменения">
                    </div>
                    <input name="change_date" type="hidden">
                </form>
            </div>
            <div id="overlay"></div><!-- Пoдлoжкa -->
            <div>
                <?php
                    $getDate="";
                if (isset($_GET['list_order_upd'])) {
                    $getDate ="&list_order_upd=";
                    if (isset($_GET['from'])) {
                        $getDate .="&from=".$_GET['from'];
                    }
                    if (isset($_GET['to'])) {
                        $getDate .="&to=".$_GET['to'];
                    }
                }
                ?>

                <?php if (isset($_GET['client_order'])) {
                    $get = "order=order&client_order=".$_GET['client_order'];
                }elseif (isset($_GET['mobile'])) {
					$get = "order=order&mobile=" . $_GET['mobile'];					// Поиск по телефону клиента
				}elseif (isset($_GET['no_date_order'])) {
                    $get = "order=order&no_date_order=".$_GET['no_date_order'];
                } else {
                    $get = "order=order";
                }; ?>
                <script type="text/javascript">
                    var listWater = [
                    {name: "edit", index: "edit", width: 40, align: "center", search: false, formatter: editOrder <?php if ($access_level < 2) { echo ', hidden: true'; } ?>},
                    {name: "delete", index: "delete", width: 20, align: "center", search: false, formatter: deleteOrder <?php if ($access_level < 3) { echo ', hidden: true'; } ?>},
                    {name: "client_id", index: "o.client_id", width: 60, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                    {name: "name", index: "o.name", width: 180, align: "center", editable: false, sorttype: 'string', searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                    {name: "order_id", index: "o.id", width: 50, align: "center", sorttype: 'integer', search: true, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                    {name: "address", index: "o.address", width: 210, align: "center", sorttype: 'string', editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
					{name: "contact", index: "o.contact", width: 210, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                    {name: "date", index: "o.date", width: 70, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                    {name: "time", index: "o.time", width: 80, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},];
                    //{name: "driver", index: "u.name", width: 80, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},];

                    var listName = ["", "", "id клиента","Клиент", "id", "Адрес", "Контактные данные","Дата", "Время"];
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
                        var currentPage = $("#list").getGridParam('page');
                        var start = currentPage < 3 ? 1 : currentPage - 2;
                        var selectCurrentPage = '';
                        var html = '<input type="button" name="" value="<" onclick="goToPage(' + (currentPage - 1) + ');">'; // Строка с пагинотором

                        for (var i = start; i <= start + 5; i++) {
                           selectCurrentPage = i == currentPage ? ' style="background-color: #74ccea; color: #fff;" ' : '';
                           html += '<input type="button" name="" value="' + i + '" onclick="goToPage(' + i + ')" ' + selectCurrentPage + ' >';
                        }

                        html += '<input type="button" name="" value=">" onclick="goToPage(' + (currentPage + 1) + ')">';
                        $('.custom_paginator').html(html);
                    }

                    function goToPage(page) {
                       console.log('go to ' + page);
                        $("#list").trigger("reloadGrid",[{page:page}]);
                    }

                    $(function () {
                        var events, originalReloadGrid, $grid = $("#list"), multiselect = false,
                            enableMultiselect = function (isEnable) {
                                $(this).jqGrid('setGridParam', {multiselect: (isEnable ? true : false)});
                            };
                        var lastsel = 0;

                        $.ajax({
                            type: 'POST',
                            data: {
                                water_list: ""
                            },
                            url: "/iwaterTest/backend.php",
                            success: function(result) {
                                var currentWater = JSON.parse(result);
                                var var_a = 0;

                                console.log(currentWater)

                                for (var m = 0; m < currentWater.length; m++) {
                                    listName.push(currentWater[m]['name']);
                                    listWater.push(currentWater[m]);
                                    var_a++;
                                }

                                listName.push("Тара", "Оборудование","Переносы","Примечание", "Причина переноса", "Водитель", "Статус");


                                listWater.push({name: "tank_empty_now", index: "o.tank_empty_now", width: 80, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                listWater.push({name: "equip", index: "o.equip", width: 310, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                listWater.push({name: "history", index: "o.history", width: 80, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                listWater.push({name: "notice", index: "o.notice", width: 190, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                listWater.push({name: "reason", index: "o.reason", width: 140, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                //listWater.push({name: "mobile", index: "mobile", width: 80, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                //listWater.push({name: "checked", index: "o.checked", width: 110, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                listWater.push({name: "driver", index: "u.name", width: 80, align: "center", editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});
                                listWater.push({name: 'status', index: "o.status",  width: 100,align: "center",  edittype:'select',formatter:'select', editoptions:{value:{0:'В работе',1:'Отмена',2:"Доставлен",3:"Перенос"}}, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}});

                        $("#list").jqGrid({
                            url: "/iwaterTest/backend.php?<?echo $get.$getDate?>",
                            datatype: "xml",
                            mtype: "POST",
                            xmlReader: {
                                root:"rows",
                                total:"total",
                                records:"rows>records",
                                repeatitems:true
                            },
                            colNames: listName,
                            colModel: listWater,
                            pager: "#pager",
                            rowNum: 30,
                            rowList: [30, 50, 100],
                            sortname: 'o_id',
                            viewrecords: true,
                            sortorder: "desc",
                            onPaging: function () {
                                enableMultiselect.call(this, true);
                            },
                            onSortCol: function () {
                                enableMultiselect.call(this, true);
                            },
                            loadComplete: function () {
                               createPager();
//                                if(typeof $("#list td:nth-child(2)")[1] != "undefined") {
//                                    if ($("#list td:nth-child(2)")[1].innerText != "") {
//
////                                        $("#list td:nth-child(1)").show();
////                                        $("#list tr:nth-child(1) td:nth-child(1)").hide();
////                                        $("#list tr:nth-child(1)").append('<td style="width:0px">New Column</td>');
//                                        // Если вы задумались о том, зачем этот код, то я поведую вам страшную историю, как jqgrid при изменениях работы с мультивыделения
//                                        // на одиночное выставлял первую строку в таблицы в display:none без причины при поиске. Так вот здесь стоит костыль,
//                                        // вдруг оказывается client id, то я вручную отображаю скрытый зачем-то нулевой столбец
//                                    }
//                                }
                                if (!multiselect) {
                                    $(this).jqGrid('hideCol', 'cb');
                                } else {
                                    $(this).jqGrid('showCol', 'cb');
                                }
//                                enableMultiselect.call(this, multiselect);

                            },
                            onSelectRow: function (id) {
                                var multi_flag = $(".multi_hidden").val()=="multi";
                                if(multi_flag == true) {
                                    var div_date = $("#change_date");
                                    div_date.detach();
                                    var tr = $("#" + id);
                                    var td = tr.find("td")[2];
                                    var top = td.offsetTop + 17;
                                    var left = td.offsetLeft + 400;
                                    var html = '<div id="change_date" type="text" role="textbox" class="editable inline-edit-cell ui-widget-content ui-corner-all" style="border-width: 2px;    border-color: #cdcdcd; z-index: 9999; position: absolute; left:' + left + 'px; top:' + top + 'px;"><div><a id="modal_link" href="#">Перенести клиента</a></div></div>';
                                    tr.append(html);
                                    //width: 115px;height: 17px;
                                    // var div_date = $("#edit_order");
                                    // div_date.detach();
                                    // var tr = $("#" + id);
                                    // var td = tr.find("td")[2];
                                    // var top = td.offsetTop;
                                    // var left = 0;
                                    // var html = '<a href="/iwaterTest/admin/edit_orders?id=' + id + '"><div id="edit_order" type="text" role="textbox" class="editable inline-edit-cell ui-widget-content ui-corner-all" style="    background: url(../../css/image/edit.png) 0 0 no-repeat;background-size: contain; z-index: 9999; position: absolute;width: 18px;height: 13px; left:' + left + 'px; top:' + top + 'px;"></div></a>';
                                    // // tr.append(html);
                                    //



                                    $("#date_client_id")[0].value = id;
                                    $("#datepicker")[0].value = tr.find("td")[7].innerHTML;
//                                $("#time")[0].value = tr.find("td")[4].innerHTML;
                                    $('a#modal_link').click(function (event) { // лoвим клик пo ссылки с id="go"
                                        event.preventDefault(); // выключaем стaндaртную рoль элементa
                                        $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                                            function () { // пoсле выпoлнения предъидущей aнимaции
                                                $('#modal_form')
                                                    .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                                                    .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                                            });
                                    });
                                    /!* Зaкрытие мoдaльнoгo oкнa, тут делaем тo же сaмoе нo в oбрaтнoм пoрядке *!/
                                    $('#modal_close, #overlay').click(function () { // лoвим клик пo крестику или пoдлoжке
                                        $('#modal_form')
                                            .animate({opacity: 0, top: '45%'}, 200,  // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
                                                function () { // пoсле aнимaции
                                                    $(this).css('display', 'none'); // делaем ему display: none;
                                                    $('#overlay').fadeOut(400); // скрывaем пoдлoжку
                                                }
                                            );
                                    });
                                    if (id && id !== lastsel && lastsel != 0) {
                                        jQuery('#list').jqGrid('saveRow', lastsel);
                                        jQuery('#list').jqGrid('editRow', id, true);
                                        lastsel = id;
                                    } else {
                                        if (lastsel == 0) {
                                            jQuery('#list').jqGrid('editRow', id, true);
                                            lastsel = id;
                                        }
                                    }
                                }


                            },
                            onSortCol: function (rowid, iRow, iCol, e) {
                               // $(".ui-search-toolbar").show();
                            },
                            editurl: "/iwaterTest/backend.php",
                            gridview: true,
                            autoencode: false,
                            caption: "Заказы",
                            loadonce: false,
                            sortable: true,
                            editable: false,
                            multiselect: true
                        });

                        events = $grid.data("events"); // read all events bound to
// Verify that one reloadGrid event handler is set. It should be set
                        if (events && events.reloadGrid && events.reloadGrid.length === 1) {
                            originalReloadGrid = events.reloadGrid[0].handler; // save old
                            $grid.unbind('reloadGrid');
                            $grid.bind('reloadGrid', function (e, opts) {
                                enableMultiselect.call(this, true);
                                originalReloadGrid.call(this, e, opts);
                            });
                        }
                        $(".multi").button().click(function () {
                            var $this = $(this);


                            multiselect = $('.multi_hidden').val()=='multi';
                            multiselect ? $('.multi_hidden').val('one') : $('.multi_hidden').val('multi');
                            multiselect ? $(".form_status").show() : $(".form_status").hide();
                            $this.button("option", "label", multiselect ?
                                "Редактирование таблицы" :
                                "Мультивыделение");
                            enableMultiselect.call($grid[0], true);
                            $grid.trigger("reloadGrid");
                        });
                        $("#list").jqGrid('navGrid', "#pager", {edit: false, add: false, del: true}, {}, {}, {},
                        {
                            multipleSearch: true,// Поиск по нескольким полям
                            multipleGroup: true, // Сложный поиск с подгруппами условий
                            showQuery: true
                        });

                        $("#list").jqGrid('filterToolbar', {searchOperators: true});
                        $('#list').jqGrid({ sortname: 'o_id', sortorder: "desc"});
                        $("#list").jqGrid('setGridHeight', 'auto');
                        // $(".ui-search-toolbar").hide();
                     }
                  });

                    });

                    $(document).ready(function () {
                        $("#datepicker").datepicker({
                                changeMonth: true,
                                changeYear: true,
                                showButtonPanel: true,
                                beforeShow: function (input, inst) {
                                    var rect = input.getBoundingClientRect();
                                    setTimeout(function () {
                                        inst.dpDiv.css({top: rect.top + 25, left: rect.left - 95});
                                    }, 0);
                                }
                            },
                            $.datepicker.regional["ru"]);
                        $("#datepicker").datepicker("option", "dateFormat", "dd/mm/yy");

                        $("#no_date_order").click(function(){
                            if($(this).is( ":checked" )){
                                window.location= "/iwaterTest/admin/list_orders?no_date_order=1";
                            }else{
                                window.location= "/iwaterTest/admin/list_orders";

                            }
                        });
                    });

                    $( function() {
                        var dateFormat = "mm/dd/yy",
                            from = $( "#from" )
                                .datepicker({
                                    defaultDate: "+1w",
                                    changeMonth: true,
                                    numberOfMonths: 1
                                })
                                .on( "change", function() {
                                    to.datepicker( "option", "minDate", getDate( this ) );
                                }),
                            to = $( "#to" ).datepicker({
                                defaultDate: "+1w",
                                changeMonth: true,
                                numberOfMonths: 1
                            })
                                .on( "change", function() {
                                    from.datepicker( "option", "maxDate", getDate( this ) );
                                });

                        function getDate( element ) {
                            var date;
                            try {
                                date = $.datepicker.parseDate( dateFormat, element.value );
                            } catch( error ) {
                                date = null;
                            }

                            return date;
                        }
                        $("#from, #to").datepicker("option", "dateFormat", "dd/mm/yy");
                        $("#from").val("<?php echo $_GET['from'] ?>");
                        $("#to").val("<?php echo $_GET['to'] ?>");
                    } );

                    function load_excel(){
                        var from = $("#from").val();
                        var to =  $("#to").val();
                        if(from == "" || to == ""){
                            $("#error_excel").show();
                            return 0;
                        }
                        $("#error_excel").hide();
                        $('#loading').show();
                        $('#order_excel').hide();
                        $('#all_order_excel').hide();
                        $.ajax({
                            type: "POST",
                            data: {
                                from: from,
                                to: to,
                                order_excel_file: ""
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (file_name) {
//                                location.href = '/iwaterTest/files'+'.xlsx';
                                from = from.split("/");
                                to = to.split("/");
                                location.href = '/iwaterTest/files/order_from_'+from[0]+from[1]+from[2]+'_to_'+to[0]+to[1]+to[2]+'.xlsx';
                                $('#loading').hide();
                                $('#order_excel').show();
                                $('#all_order_excel').show();
                            }
                        });
                    }

                    function editOrder(options) {
                       return '<a href="/iwaterTest/admin/edit_orders?id=' + options + '"><img src="/iwaterTest/css/image/edit.png" style="cursor: pointer;" alt="Редактирование" title="Редактировать заказ"></a>';
                    }

                    function deleteOrder(options) {
                       return '<a onclick="deleteOrderNumber(' + options + ')"><img src="/iwaterTest/css/image/delete.png" style="cursor: pointer;" alt="Удаление" title="Удалить заказ"></a>';
                    }

                    function deleteOrderNumber(options) {
                       if (confirm('Вы уверены что хотите удалить заказ №' + options + '?')) {
                          $.ajax({
                              type: "POST",
                              data: {
                                 oper: "del",
                                 id: options
                              },
                              url: "/iwaterTest/backend.php",
                              success: function (file_name) {
                                 alert('Заказ был успешно удалён');
                                 $('#list').trigger( 'reloadGrid' );
                              }
                          });
                       }
                    }

                    function all_load_excel(){
                        $('#loading').show();
                        $('#order_excel').hide();
                        $('#all_order_excel').hide();
                        $.ajax({
                            type: "POST",
                            data: {
                                from: "",
                                to: "",
                                order_excel_file: "all"
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (file_name) {
                                location.href = '/iwaterTest/files/all_order.xlsx';
                                $('#loading').hide();
                                $('#order_excel').show();
                                $('#all_order_excel').show();
                            }
                        });
                    }

                    function searchOrder() {
                      var url = "/iwaterTest/backend.php?<?echo $get.$getDate?>" + "&" + $(".edit_role option:selected").val() + "=" + $("#set_filter").val().replace(/[^\d]/g, '');
                      $("#list").jqGrid('setGridParam', { url: url });
                      $("#list").trigger("reloadGrid");
                    }

                    function change_statuses(){
                        var array_id;
                        var status = $("#status").val();
                        array_id = jQuery("#list").jqGrid('getGridParam','selarrrow');

                        $.ajax({
                            type: "POST",
                            data: {
                                statuses_order_list: JSON.stringify(array_id),
                                status: status
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (file_name) {
                                location.href = '/iwaterTest/admin/list_orders';
                            }
                        });
                    }
                </script>
